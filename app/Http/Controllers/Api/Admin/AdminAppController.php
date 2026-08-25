<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin app settings management (site info, commissions, activation fee,
 * affiliate reward, theme colors, verification, etc.).
 */
class AdminAppController extends Controller
{
    use ApiResponse;

    public function show(): JsonResponse
    {
        return $this->ok('App settings.', app(SettingService::class)->all());
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'              => 'sometimes|string|max:120',
            'logotext'          => 'sometimes|string|max:60',
            'logo'              => 'sometimes|nullable|string|max:255',
            'favicon'           => 'sometimes|nullable|string|max:255',
            'url'               => 'sometimes|nullable|string|max:255',
            'default_currency_id' => 'sometimes|nullable|exists:currencies,id',
            'need_verification' => 'sometimes|boolean',
            'saas'              => 'sometimes|boolean',
            'manual_payment'    => 'sometimes|boolean',
            'withdraw_com'      => 'sometimes|numeric|min:0|max:100',
            'task_com'          => 'sometimes|numeric|min:0|max:100',
            'activation_fee'    => 'sometimes|numeric|min:0',
            'affiliate_reward'  => 'sometimes|numeric|min:0',
            'affiliate_enabled' => 'sometimes|boolean',
            'ann_status'        => 'sometimes|boolean',
            'ann_text'          => 'sometimes|nullable|string|max:5000',
            'booking_limit'     => 'sometimes|integer|min:1',
            'ss_limit'          => 'sometimes|integer|min:1',
            'address'           => 'sometimes|nullable|string|max:255',
            'contact_email'     => 'sometimes|nullable|email|max:191',
            'phone'             => 'sometimes|nullable|string|max:40',
            'footer_text'       => 'sometimes|nullable|string|max:5000',
            'primary_color'     => 'sometimes|string|max:9',
            'accent_color'      => 'sometimes|string|max:9',
        ]);

        $settings = AppSetting::find(1);
        $settings->update($validated);
        app(SettingService::class)->flush();

        return $this->ok('Settings updated.', $settings->fresh());
    }
}
