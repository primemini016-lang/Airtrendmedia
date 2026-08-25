<?php

namespace App\Services;

use App\Models\PushDeviceToken;
use App\Models\PushNotificationsLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private SettingService $settings;

    public function __construct()
    {
        $this->settings = app(SettingService::class);
    }

    /**
     * Register/update a device token for a user.
     */
    public function registerToken(User $user, string $token, string $provider = 'firebase', ?string $deviceId = null): PushDeviceToken
    {
        return PushDeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token'   => $token,
            ],
            [
                'provider'  => $provider,
                'device_id' => $deviceId,
                'is_active' => true,
            ]
        );
    }

    /**
     * Send a push notification to a single user (all their active tokens).
     * Supports Phoenix-style notifications with images and slides.
     */
    public function sendToUser(User $user, array $payload): array
    {
        $tokens = $user->pushTokens()->where('is_active', true)->pluck('token')->toArray();

        if (empty($tokens)) {
            return ['success' => false, 'error' => 'No active device tokens'];
        }

        return $this->send($tokens, $payload, $user->id);
    }

    /**
     * Broadcast a notification to all users (admin broadcast).
     */
    public function broadcast(array $payload): array
    {
        $tokens = PushDeviceToken::where('is_active', true)->pluck('token')->toArray();

        if (empty($tokens)) {
            return ['success' => false, 'error' => 'No active device tokens'];
        }

        return $this->send($tokens, $payload, null);
    }

    /**
     * Core send method. Routes to Firebase FCM or OneSignal based on admin config.
     * Payload supports: title, body, image, link, slides (array of {image, caption}),
     * data (custom key-values), icon, badge, sound.
     */
    protected function send(array $tokens, array $payload, ?int $userId = null): array
    {
        $provider = $this->settings->get('push_provider', 'firebase');
        $title = $payload['title'] ?? 'Airtrendmedia';
        $body = $payload['body'] ?? '';
        $image = $payload['image'] ?? null;
        $link = $payload['link'] ?? null;
        $icon = $payload['icon'] ?? null;
        $sound = $payload['sound'] ?? 'default';
        $slides = $payload['slides'] ?? [];
        $data = $payload['data'] ?? [];

        // Phoenix-style slides go in the data payload for native rendering
        $dataPayload = array_merge($data, [
            'link'   => $link,
            'slides' => json_encode($slides),
            'type'   => $payload['type'] ?? 'notification',
        ]);

        $logData = [
            'title'      => $title,
            'body'       => $body,
            'provider'   => $provider,
            'recipients' => count($tokens),
            'user_id'    => $userId,
            'data'       => json_encode($dataPayload),
            'status'     => 'sent',
        ];

        try {
            if ($provider === 'firebase') {
                $result = $this->sendViaFirebase($tokens, $title, $body, $image, $icon, $sound, $dataPayload);
            } elseif ($provider === 'onesignal') {
                $result = $this->sendViaOneSignal($tokens, $title, $body, $image, $icon, $sound, $dataPayload);
            } else {
                $logData['status'] = 'failed';
                $logData['error']  = "Unknown provider: {$provider}";
                PushNotificationsLog::create($logData);
                return ['success' => false, 'error' => "Unknown provider: {$provider}"];
            }

            $logData['provider_response'] = json_encode($result);
            PushNotificationsLog::create($logData);

            return ['success' => true, 'result' => $result];
        } catch (\Exception $e) {
            $logData['status'] = 'failed';
            $logData['error']  = $e->getMessage();
            PushNotificationsLog::create($logData);
            Log::error('Push notification failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send via Firebase Cloud Messaging HTTP v1 API.
     * Requires server key / service account JSON configured by admin.
     */
    protected function sendViaFirebase(array $tokens, string $title, string $body, ?string $image, ?string $icon, string $sound, array $data): array
    {
        $serverKey = $this->settings->get('firebase_server_key');

        if (!$serverKey) {
            throw new \RuntimeException('Firebase server key not configured. Set it in admin → Push Settings.');
        }

        $message = [
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => $data,
            'android' => [
                'notification' => [
                    'icon' => $icon ?? 'ic_notification',
                    'sound' => $sound,
                    'image' => $image,
                    'click_action' => $data['link'] ?? null,
                ],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => $sound,
                        'badge' => 1,
                    ],
                ],
                'fcm_options' => [
                    'image' => $image,
                ],
            ],
        ];

        // FCM legacy HTTP API (works with server key)
        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'registration_ids' => $tokens,
            'notification'     => $message['notification'],
            'data'             => $message['data'],
        ]);

        return $response->json();
    }

    /**
     * Send via OneSignal REST API.
     */
    protected function sendViaOneSignal(array $tokens, string $title, string $body, ?string $image, ?string $icon, string $sound, array $data): array
    {
        $appId = $this->settings->get('onesignal_app_id');
        $restKey = $this->settings->get('onesignal_rest_api_key');

        if (!$appId || !$restKey) {
            throw new \RuntimeException('OneSignal credentials not configured. Set them in admin → Push Settings.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $restKey,
            'Content-Type'  => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id'            => $appId,
            'include_player_ids'=> $tokens,
            'headings'          => ['en' => $title],
            'contents'          => ['en' => $body],
            'big_picture'       => $image,
            'large_icon'        => $icon,
            'data'              => $data,
        ]);

        return $response->json();
    }

    /**
     * Convenience: send a simple notification.
     */
    public function notify(User $user, string $title, string $body, ?string $link = null, ?string $image = null): array
    {
        return $this->sendToUser($user, [
            'title' => $title,
            'body'  => $body,
            'link'  => $link,
            'image' => $image,
        ]);
    }
}
