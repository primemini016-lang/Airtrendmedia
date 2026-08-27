<?php
/**
 * HTTP smoke test with plain-text exception handler to bypass Termwind segfault.
 * Authenticates by writing the session data directly then sending the cookie.
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

// Swap exception handler to reveal real errors (bypasses Termwind segfault)
$app->instance(ExceptionHandler::class, new class implements ExceptionHandler {
    public function report(\Throwable $e): void {}
    public function shouldReport(\Throwable $e): bool { return false; }
    public function render($request, \Throwable $e): Response {
        $msg = $e->getMessage();
        $file = basename($e->getFile());
        $line = $e->getLine();
        $prev = $e->getPrevious();
        $prevInfo = $prev ? " | PREV: " . get_class($prev) . ": " . $prev->getMessage() . " @" . basename($prev->getFile()) . ":" . $prev->getLine() : "";
        return new Response("ERR:" . get_class($e) . ": " . $msg . " @" . $file . ":" . $line . $prevInfo, 500);
    }
    public function renderForConsole($output, \Throwable $e): void {}
    public function shouldRenderExceptionWhenOutage(\Throwable $e, $request, bool $isReportable): bool { return false; }
});

$kernel = $app->make(Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pass = 0; $fail = 0;

/**
 * Make a request with an auth session cookie pre-set.
 * We create a session, log in the user, save the session, get the cookie,
 * then send subsequent requests with that cookie.
 */
function createAuthSession($app, $guard, $user): array {
    // Get a fresh session store
    $session = $app->make('session.store');
    $session->setId(\Str::random(40));
    $session->start();
    
    // Login the user
    Auth::guard($guard)->login($user);
    
    // The auth middleware key depends on the guard
    $authKey = 'login_web_' . sha1($guard === 'web' ? 'web' : 'admin');
    if ($guard === 'admin') {
        $authKey = 'login_admin_' . sha1('admin');
    }
    $session->put($authKey, $user->id);
    $session->save();
    
    return [
        'airtrendmedia_session' => $session->getId(),
    ];
}

function testRoute($kernel, $method, $uri, $name, $cookies = []) {
    $server = ['REMOTE_ADDR' => '127.0.0.1', 'SERVER_NAME' => 'localhost'];
    if (!empty($cookies)) {
        $cookieStr = '';
        foreach ($cookies as $k => $v) {
            $cookieStr .= "$k=$v; ";
        }
        $server['HTTP_COOKIE'] = rtrim($cookieStr, '; ');
    }
    $request = Request::create('http://localhost' . $uri, $method, [], $cookies, [], $server);
    try {
        $response = $kernel->handle($request);
        $code = $response->getStatusCode();
        $body = $response->getContent();
        $status = ($code >= 200 && $code < 400) ? 'PASS' : 'FAIL';
        $extra = '';
        if ($code >= 400) {
            $extra = ' | ' . substr($body, 0, 250);
        }
        echo sprintf("%-4s %-45s %3d %s%s\n", $method, $name, $code, $status, $extra);
        return $code;
    } catch (\Throwable $e) {
        echo sprintf("%-4s %-45s ERR %s: %s @%s:%d\n", $method, $name, get_class($e), $e->getMessage(), basename($e->getFile()), $e->getLine());
        return 500;
    }
}

// === PUBLIC ROUTES ===
echo "=== PUBLIC ROUTES ===\n";
$blogSlug = \App\Models\BlogPost::first()->slug;
$publicRoutes = [
    ['GET', '/', 'Homepage'],
    ['GET', '/gigs', 'Gigs list'],
    ['GET', '/gigs/1', 'Gig show (with reviews)'],
    ['GET', '/gigs/5', 'Gig show #5'],
    ['GET', '/gigs/10', 'Gig show #10'],
    ['GET', '/marketplace', 'Marketplace list'],
    ['GET', '/marketplace/1', 'Marketplace show (reviews+comments)'],
    ['GET', '/marketplace/5', 'Marketplace show #5'],
    ['GET', '/blog', 'Blog list'],
    ['GET', '/blog/' . $blogSlug, 'Blog show (slug: ' . substr($blogSlug, 0, 20) . '...)'],
    ['GET', '/ptc', 'PTC browse'],
    ['GET', '/ptc/1', 'PTC show'],
    ['GET', '/ptc/2', 'PTC show #2'],
    ['GET', '/login', 'Login page'],
    ['GET', '/register', 'Register page'],
    ['GET', '/faqs', 'FAQs'],
    ['GET', '/contact', 'Contact'],
    ['GET', '/terms', 'Terms'],
];
foreach ($publicRoutes as $r) {
    $code = testRoute($kernel, $r[0], $r[1], $r[2]);
    if ($code >= 200 && $code < 400) $pass++; else $fail++;
}

// === AUTHENTICATED ROUTES ===
echo "\n=== AUTHENTICATED ROUTES (as sarah_k - recommendable user) ===\n";
$user = \App\Models\User::where('username', 'sarah_k')->first();
$userCookies = createAuthSession($app, 'web', $user);

$authRoutes = [
    ['GET', '/dashboard', 'User Dashboard'],
    ['GET', '/profile', 'My profile (recommendable + reviews)'],
    ['GET', '/my-blog', 'My blog'],
    ['GET', '/my-blog/create', 'Create blog'],
    ['GET', '/my-ptc', 'My PTC ads'],
    ['GET', '/my-ptc/history', 'PTC earnings history'],
    ['GET', '/my-ptc/create', 'Create PTC ad'],
    ['GET', '/create-task', 'Create task'],
    ['GET', '/messages', 'Messenger'],
    ['GET', '/wallet', 'Wallet'],
    ['GET', '/tasks', 'Browse tasks'],
    ['GET', '/tasks/1', 'Task detail'],
    ['GET', '/my-tasks', 'My tasks'],
    ['GET', '/my-gigs', 'My gigs'],
    ['GET', '/my-gigs/create', 'Create gig'],
    ['GET', '/notifications', 'Notifications'],
    ['GET', '/transactions', 'Transactions'],
];
foreach ($authRoutes as $r) {
    $code = testRoute($kernel, $r[0], $r[1], $r[2], $userCookies);
    if ($code >= 200 && $code < 400) $pass++; else $fail++;
}

// === ADMIN ROUTES ===
echo "\n=== ADMIN ROUTES (as admin) ===\n";
$admin = \App\Models\Admin::where('username', 'admin')->first();
if (!$admin) $admin = \App\Models\Admin::first();
echo "(Admin: " . $admin->username . ")\n";
$adminCookies = createAuthSession($app, 'admin', $admin);

$adminRoutes = [
    ['GET', '/admin', 'Admin dashboard'],
    ['GET', '/admin/ptc', 'Admin PTC index'],
    ['GET', '/admin/ptc/settings', 'Admin PTC settings'],
    ['GET', '/admin/ptc/1', 'Admin PTC show'],
    ['GET', '/admin/blog', 'Admin blog'],
    ['GET', '/admin/marketplace', 'Admin marketplace'],
    ['GET', '/admin/gigs', 'Admin gigs'],
    ['GET', '/admin/tasks', 'Admin tasks'],
    ['GET', '/admin/users', 'Admin users'],
    ['GET', '/admin/settings', 'Admin settings'],
    ['GET', '/admin/banner-settings', 'Admin banner settings'],
    ['GET', '/admin/system-update', 'Admin system update (God mode)'],
    ['GET', '/admin/deposits', 'Admin deposits'],
    ['GET', '/admin/withdrawals', 'Admin withdrawals'],
    ['GET', '/admin/transactions', 'Admin transactions'],
];
foreach ($adminRoutes as $r) {
    $code = testRoute($kernel, $r[0], $r[1], $r[2], $adminCookies);
    if ($code >= 200 && $code < 400) $pass++; else $fail++;
}

echo "\n=== SUMMARY ===\n";
echo "PASS: $pass | FAIL: $fail | Total: " . ($pass + $fail) . "\n";
if ($fail > 0) {
    echo "*** $fail FAILURES - review above ***\n";
} else {
    echo "*** ALL PASSED ***\n";
}
