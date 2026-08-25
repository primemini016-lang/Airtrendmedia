<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Country;
use App\Models\Faq;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;

class HelperController extends Controller
{
    use ApiResponse;

    public function siteInfo(SettingService $settings): JsonResponse
    {
        $s = $settings->all();
        $currency = $settings->defaultCurrency();
        return response()->json([
            'name'            => $s->name,
            'logotext'        => $s->logotext,
            'logo'            => $s->logo ? asset($s->logo) : null,
            'favicon'         => $s->favicon ? asset($s->favicon) : null,
            'currency'        => ['code' => $currency->code, 'symbol' => $currency->symbol],
            'primary_color'   => $s->primary_color,
            'accent_color'    => $s->accent_color,
            'address'         => $s->address,
            'contact_email'   => $s->contact_email,
            'phone'           => $s->phone,
            'footer_text'     => $s->footer_text,
            'activation_fee'  => (float) $s->activation_fee,
            'affiliate_reward'=> (float) $s->affiliate_reward,
            'saas'            => (bool) $s->saas,
            'ann_status'      => (bool) $s->ann_status,
            'ann_text'        => $s->ann_text,
        ]);
    }

    public function countries(): JsonResponse
    {
        return response()->json(Country::orderBy('name')->get(['id', 'name', 'code', 'phone_code']));
    }

    public function faqs(): JsonResponse
    {
        return response()->json(Faq::orderBy('position')->get(['id', 'question', 'answer']));
    }

    public function contactDetails(SettingService $settings): JsonResponse
    {
        $s = $settings->all();
        return response()->json([
            'address' => $s->address,
            'email'   => $s->contact_email,
            'phone'   => $s->phone,
        ]);
    }

    public function paystackKey(): JsonResponse
    {
        return response()->json([
            'public_key' => (string) config('services.paystack.public_key'),
            'configured' => ! empty(config('services.paystack.public_key')),
        ]);
    }
}
