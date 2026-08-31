<?php
/**
 * Airtrendmedia deployment smoke test.
 *
 * Run after installation from the project root:
 *   php smoke_test.php
 *
 * It intentionally performs GET-only checks and does not create payments,
 * delete content, or modify user balances. It uses the first available
 * user/admin account in the database and reports the exact route that fails.
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

$app->instance(ExceptionHandler::class, new class implements ExceptionHandler {
    public function report(\Throwable $e): void {}
    public function shouldReport(\Throwable $e): bool { return false; }
    public function render($request, \Throwable $e): Response {
        return new Response(
            'ERR:'.get_class($e).': '.$e->getMessage().' @'.basename($e->getFile()).':'.$e->getLine(),
            500
        );
    }
    public function renderForConsole($output, \Throwable $e): void {}
    public function shouldRenderExceptionWhenOutage($request, \Throwable $e): bool { return false; }
});

$kernel = $app->make(Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pass = 0; $fail = 0;

function createAuthSession($app, string $guard, $model): array {
    if (!$model) return [];
    $session = $app->make('session.store');
    $session->setId(\Illuminate\Support\Str::random(40));
    $session->start();
    Auth::guard($guard)->login($model);
    $key = $guard === 'admin'
        ? 'login_admin_'.sha1('admin')
        : 'login_web_'.sha1('web');
    $session->put($key, $model->id);
    $session->save();
    return [$session->getName() => $session->getId()];
}

function testRoute($kernel, string $method, string $uri, string $name, array $cookies = []): bool {
    $server = [
        'REMOTE_ADDR' => '127.0.0.1',
        'SERVER_NAME' => 'localhost',
        'HTTP_ACCEPT' => 'text/html,application/json',
    ];
    $request = Request::create('http://localhost'.$uri, $method, [], $cookies, [], $server);
    try {
        $response = $kernel->handle($request);
        $code = $response->getStatusCode();
        $ok = $code >= 200 && $code < 400;
        echo sprintf("%-5s %-50s %3d %s", $method, $name, $code, $ok ? 'PASS' : 'FAIL');
        if (!$ok) echo ' | '.substr(trim(strip_tags($response->getContent())), 0, 220);
        echo PHP_EOL;
        return $ok;
    } catch (\Throwable $e) {
        echo sprintf("%-5s %-50s ERR FAIL | %s @%s:%d\n",
            $method, $name, $e->getMessage(), basename($e->getFile()), $e->getLine());
        return false;
    }
}

function runChecks($kernel, array $routes, array $cookies = []): array {
    $p = 0; $f = 0;
    foreach ($routes as $route) {
        if (testRoute($kernel, 'GET', $route[0], $route[1], $cookies)) $p++; else $f++;
    }
    return [$p, $f];
}

echo "=== PUBLIC ROUTES ===\n";
$public = [
    ['/', 'Homepage'],
    ['/browse', 'Browse'],
    ['/gigs', 'Gigs'],
    ['/marketplace', 'Marketplace'],
    ['/blog', 'Blog'],
    ['/affiliate', 'Affiliate'],
    ['/login', 'Login'],
    ['/register', 'Register'],
    ['/faqs', 'FAQs'],
    ['/contact', 'Contact'],
    ['/terms', 'Terms'],
];
[$p,$f] = runChecks($kernel, $public); $pass += $p; $fail += $f;

$user = \App\Models\User::where('banned', false)->orderBy('id')->first();
if ($user) {
    echo "\n=== AUTHENTICATED USER ROUTES ===\n";
    $cookies = createAuthSession($app, 'web', $user);
    $routes = [
        ['/dashboard', 'User dashboard'],
        ['/profile', 'My profile'],
        ['/messages', 'Support messages'],
        ['/messenger', 'Messenger'],
        ['/notifications', 'Notifications'],
        ['/wallet', 'Wallet'],
        ['/transactions', 'Transactions'],
        ['/tasks', 'Task marketplace'],
        ['/my-tasks', 'My tasks'],
        ['/my-gigs', 'My gigs'],
        ['/my-gigs/create', 'Create gig'],
        ['/my-listings/create', 'Create marketplace listing'],
        ['/my-blog', 'My blog'],
        ['/my-blog/create', 'Create blog'],
        ['/affiliate-dashboard', 'Affiliate dashboard'],
        ['/verification', 'Blue verification'],
        ['/account-type', 'Account type'],
        ['/kyc', 'KYC'],
        ['/activate', 'Activation'],
    ];
    [$p,$f] = runChecks($kernel, $routes, $cookies); $pass += $p; $fail += $f;
} else {
    echo "\nNo user exists yet; authenticated checks skipped.\n";
}

$admin = \App\Models\Admin::orderBy('id')->first();
if ($admin) {
    echo "\n=== ADMIN GOD-MODE ROUTES ===\n";
    $cookies = createAuthSession($app, 'admin', $admin);
    $routes = [
        ['/admin', 'Admin dashboard'],
        ['/admin/users', 'Users'],
        ['/admin/categories', 'Task categories'],
        ['/admin/verification', 'Verification requests'],
        ['/admin/kyc', 'KYC'],
        ['/admin/tasks', 'Task moderation'],
        ['/admin/gigs', 'Gig moderation'],
        ['/admin/marketplace', 'Marketplace moderation'],
        ['/admin/blog', 'Blog management'],
        ['/admin/messages', 'Support/messages'],
        ['/admin/notifications', 'Notifications'],
        ['/admin/deposits', 'Deposits'],
        ['/admin/withdrawals', 'Withdrawals'],
        ['/admin/transactions', 'Transactions'],
        ['/admin/payment-keys', 'Payment keys'],
        ['/admin/methods', 'Payment methods'],
        ['/admin/settings', 'Settings'],
        ['/admin/appearance', 'Appearance'],
        ['/admin/system-update', 'System update'],
    ];
    [$p,$f] = runChecks($kernel, $routes, $cookies); $pass += $p; $fail += $f;
} else {
    echo "\nNo admin exists yet; admin checks skipped.\n";
}

echo "\n=== SUMMARY ===\n";
echo "PASS: $pass | FAIL: $fail | TOTAL: ".($pass+$fail).PHP_EOL;
exit($fail ? 1 : 0);
