<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PushDeviceToken;
use App\Models\PushNotificationsLog;
use App\Services\PushNotificationService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function __construct(
        private SettingService $settings,
        private PushNotificationService $push,
    ) {}

    /**
     * User: register a device token (called from PWA / mobile app via JS).
     */
    public function registerToken(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'token'     => ['required', 'string', 'max:255'],
            'provider'  => ['nullable', 'string', 'in:firebase,onesignal'],
            'platform'  => ['nullable', 'string', 'in:web,android,ios,pwa'],
            'device_id' => ['nullable', 'string', 'max:100'],
        ]);

        $this->push->registerToken(
            $user,
            $validated['token'],
            $validated['provider'] ?? 'firebase',
            $validated['device_id'] ?? null
        );

        // Update platform if provided
        if (isset($validated['platform'])) {
            PushDeviceToken::where('user_id', $user->id)
                ->where('token', $validated['token'])
                ->update(['platform' => $validated['platform']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * User: unregister a device token (on logout).
     */
    public function unregisterToken(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        PushDeviceToken::where('user_id', $user->id)
            ->where('token', $validated['token'])
            ->delete();

        return response()->json(['success' => true]);
    }

    /* ===== Admin Push Settings ===== */

    /**
     * Admin: push notification settings page (Firebase + OneSignal).
     */
    public function adminSettings()
    {
        $s = $this->settings->all();
        $recentLogs = PushNotificationsLog::latest()->limit(20)->get();
        $totalTokens = PushDeviceToken::where('is_active', true)->count();

        return view('admin.push-settings', compact('s', 'recentLogs', 'totalTokens'));
    }

    /**
     * Admin: save push notification settings.
     */
    public function adminSettingsSave(Request $request)
    {
        $validated = $request->validate([
            'push_provider'              => ['nullable', 'in:firebase,onesignal,none'],
            'push_enabled'               => ['nullable', 'boolean'],
            'firebase_server_key'        => ['nullable', 'string'],
            'firebase_project_id'        => ['nullable', 'string'],
            'firebase_api_key'           => ['nullable', 'string'],
            'firebase_auth_domain'       => ['nullable', 'string'],
            'firebase_storage_bucket'    => ['nullable', 'string'],
            'firebase_messaging_sender_id'=> ['nullable', 'string'],
            'firebase_app_id'            => ['nullable', 'string'],
            'firebase_measurement_id'    => ['nullable', 'string'],
            'firebase_service_account_json'=> ['nullable', 'string'],
            'onesignal_app_id'           => ['nullable', 'string'],
            'onesignal_rest_api_key'     => ['nullable', 'string'],
        ]);

        $s = $this->settings->all();
        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $s->$key = $value;
            }
        }
        // Handle booleans that may not be in the request
        $s->push_enabled = $request->boolean('push_enabled');
        if (!$request->has('push_provider')) {
            // keep existing
        } else {
            $s->push_provider = $validated['push_provider'] ?? 'none';
        }
        $s->save();
        $this->settings->flush();

        return back()->with('success', 'Push notification settings saved successfully.');
    }

    /**
     * Admin: send a test push notification to a specific user or all.
     */
    public function adminTestPush(Request $request)
    {
        $validated = $request->validate([
            'target_user_id' => ['nullable', 'exists:users,id'],
        ]);

        if ($validated['target_user_id'] ?? null) {
            $user = \App\Models\User::find($validated['target_user_id']);
            $result = $this->push->notify($user, 'Airtrendmedia Test', 'This is a test push notification from the admin panel.', route('user.dashboard'));
        } else {
            $result = $this->push->broadcast([
                'title' => 'Airtrendmedia Test Broadcast',
                'body'  => 'This is a test broadcast notification.',
                'link'  => route('home'),
            ]);
        }

        if ($result['success']) {
            return back()->with('success', 'Test notification sent successfully.');
        }

        return back()->with('error', 'Failed to send: ' . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Admin: view push notification logs.
     */
    public function adminLogs()
    {
        $logs = PushNotificationsLog::latest()->paginate(20);
        return view('admin.push-logs', compact('logs'));
    }

    /**
     * Admin: broadcast a custom notification to all users (Phoenix-style with images + slides).
     */
    public function adminBroadcast(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:191'],
            'body'        => ['required', 'string', 'max:1000'],
            'image_url'   => ['nullable', 'url'],
            'link'        => ['nullable', 'url'],
            'icon'        => ['nullable', 'string'],
            'slides'      => ['nullable', 'array'],
            'slides.*.image' => ['required_with:slides', 'url'],
            'slides.*.caption' => ['nullable', 'string'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'body'  => $validated['body'],
            'image' => $validated['image_url'] ?? null,
            'link'  => $validated['link'] ?? null,
            'icon'  => $validated['icon'] ?? null,
            'type'  => 'broadcast',
        ];

        if (!empty($validated['slides'])) {
            $payload['slides'] = $validated['slides'];
        }

        $result = $this->push->broadcast($payload);

        if ($result['success']) {
            return back()->with('success', 'Broadcast notification sent to all devices.');
        }

        return back()->with('error', 'Broadcast failed: ' . ($result['error'] ?? 'Unknown error'));
    }
}
