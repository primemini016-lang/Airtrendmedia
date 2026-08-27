<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;

class AccountTypeController extends Controller
{
    public function __construct(
        private SettingService $settings,
    ) {}

    /**
     * Show account-type switching page.
     */
    public function index()
    {
        $user = auth('web')->user();
        $enabled = (bool) $this->settings->get('account_type_switch_enabled', true);

        return view('user.account-type', compact('user', 'enabled'));
    }

    /**
     * Switch account type: freelancer / advertiser / both.
     */
    public function switch(Request $request)
    {
        $user = auth('web')->user();

        if (!(bool) $this->settings->get('account_type_switch_enabled', true)) {
            return back()->with('error', 'Account type switching is currently disabled.');
        }

        $validated = $request->validate([
            'account_type' => ['required', 'in:freelancer,advertiser,both'],
        ]);

        $user->update(['account_type' => $validated['account_type']]);

        $labels = [
            'freelancer' => 'Freelancer (Task Performer)',
            'advertiser' => 'Advertiser',
            'both'       => 'Both (Freelancer + Advertiser)',
        ];

        return redirect()->route('user.account-type')->with('success', "Account type switched to: {$labels[$validated['account_type']]}");
    }
}
