<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserIpLog;
use App\Models\AntiCheatFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AntiCheatService
{
    /**
     * Check & log IP on registration. Prevents one-account-per-IP when enforced.
     * Returns ['ok' => bool, 'reason' => ?string]
     */
    public function checkRegistration(Request $request, string $email): array
    {
        $ip = $request->ip();
        $enforceOnePerIp = (bool) app(SettingService::class)->get('anti_cheat_one_account_per_ip', true);

        if ($enforceOnePerIp && $ip !== '127.0.0.1') {
            $existing = UserIpLog::where('ip_address', $ip)
                ->where('event', 'register')
                ->whereDate('created_at', today())
                ->exists();

            if ($existing) {
                return ['ok' => false, 'reason' => 'Multiple registrations from the same IP address are not allowed.'];
            }
        }

        return ['ok' => true, 'reason' => null];
    }

    /**
     * Log the user's IP for a given event.
     */
    public function logIp(User $user, string $event, Request $request): void
    {
        UserIpLog::create([
            'user_id'    => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event'      => $event,
        ]);

        if ($event === 'register') {
            $user->update(['registration_ip' => $request->ip()]);
        }
    }

    /**
     * Prevent duplicate views on content (post/story/ad/video).
     * Uses cache as a fast dedup layer keyed by user+content.
     */
    public function canView(User $user, string $contentType, int $contentId): bool
    {
        $key = "view:{$contentType}:{$contentId}:{$user->id}";
        if (Cache::has($key)) {
            return false; // already viewed recently
        }
        Cache::put($key, true, now()->addHours(6));
        return true;
    }

    /**
     * Detect suspicious engagement patterns and flag the user.
     * e.g. too many actions in a short time window.
     */
    public function detectSuspiciousActivity(User $user, string $actionType): void
    {
        $windowMinutes = 5;
        $threshold = (int) app(SettingService::class)->get('anti_cheat_action_threshold', 100);

        $cacheKey = "actions:{$user->id}:{$actionType}";
        $count = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $count, now()->addMinutes($windowMinutes));

        if ($count > $threshold) {
            $this->flag($user, 'rapid_' . $actionType, 'medium', "{$count} {$actionType} actions in {$windowMinutes} minutes");
        }
    }

    /**
     * Flag a user for a specific cheating violation.
     */
    public function flag(User $user, string $type, string $severity = 'high', string $description = ''): AntiCheatFlag
    {
        return AntiCheatFlag::create([
            'user_id'    => $user->id,
            'type'       => $type,
            'severity'   => $severity,
            'description'=> $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
