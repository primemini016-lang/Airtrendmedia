<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KycSubmission;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    public function __construct(
        private SettingService $settings,
    ) {}

    /**
     * Show the KYC submission / status page.
     */
    public function index()
    {
        $user = auth('web')->user();
        $submission = $user->kycSubmission;
        $autoApprove = (bool) $this->settings->get('kyc_auto_approval', false);
        $enabled = (bool) $this->settings->get('kyc_enabled', true);

        return view('user.kyc', compact('submission', 'autoApprove', 'enabled'));
    }

    /**
     * Store a new KYC submission (uploads docs + selfie).
     */
    public function submit(Request $request)
    {
        $user = auth('web')->user();

        if (!(bool) $this->settings->get('kyc_enabled', true)) {
            return back()->with('error', 'KYC verification is currently disabled.');
        }

        // Prevent re-submission if already approved
        if ($user->kyc_status === 'approved') {
            return back()->with('error', 'Your account is already KYC verified.');
        }

        // Prevent duplicate pending submissions
        if ($user->kyc_status === 'pending') {
            return back()->with('error', 'You already have a KYC submission under review.');
        }

        $validated = $request->validate([
            'full_name'     => ['required', 'string', 'max:120'],
            'id_type'       => ['required', 'string', 'in:national_id,passport,drivers_license,voters_card'],
            'id_number'     => ['required', 'string', 'max:80'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'country'       => ['required', 'string', 'max:80'],
            'address'       => ['required', 'string', 'max:255'],
            'document_front'=> ['required', 'image', 'max:5120'],
            'document_back' => ['nullable', 'image', 'max:5120'],
            'selfie'        => ['required', 'image', 'max:5120'],
        ]);

        // Store uploads
        $validated['document_front'] = $request->file('document_front')->store('kyc', 'public');
        $validated['document_back']  = $request->file('document_back')?->store('kyc', 'public');
        $validated['selfie']         = $request->file('selfie')->store('kyc', 'public');
        $validated['user_id']        = $user->id;
        $validated['status']         = 'pending';

        $submission = KycSubmission::create($validated);

        $autoApprove = (bool) $this->settings->get('kyc_auto_approval', false);
        if ($autoApprove) {
            $this->approveInternal($submission, null);
            return redirect()->route('user.kyc')->with('success', 'KYC submitted and automatically approved! You can now withdraw.');
        }

        $user->update(['kyc_status' => 'pending']);

        return redirect()->route('user.kyc')->with('success', 'KYC documents submitted successfully. We will review them shortly.');
    }

    /**
     * Internal helper to approve a submission.
     */
    public function approveInternal(KycSubmission $submission, $reviewerId = null): void
    {
        $submission->update([
            'status'         => 'approved',
            'reviewed_by'    => $reviewerId,
            'reviewed_at'    => now(),
        ]);
        $submission->user->update([
            'kyc_status'      => 'approved',
            'kyc_approved_at' => now(),
        ]);
    }

    /**
     * Admin: list all KYC submissions.
     */
    public function adminIndex(Request $request)
    {
        $query = KycSubmission::with('user:id,username,name,email,image');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $submissions = $query->latest()->paginate(20);
        $autoApprove = (bool) $this->settings->get('kyc_auto_approval', false);

        return view('admin.kyc', compact('submissions', 'autoApprove'));
    }

    /**
     * Admin: view a single submission with documents.
     */
    public function adminShow(KycSubmission $kyc)
    {
        $kyc->load('user');
        return view('admin.kyc-show', compact('kyc'));
    }

    /**
     * Admin: approve a submission.
     */
    public function adminApprove(Request $request, KycSubmission $kyc)
    {
        $admin = auth('admin')->user();
        $this->approveInternal($kyc, $admin?->id);

        return back()->with('success', 'KYC submission approved. User can now withdraw funds.');
    }

    /**
     * Admin: reject a submission with a reason.
     */
    public function adminReject(Request $request, KycSubmission $kyc)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $admin = auth('admin')->user();
        $kyc->update([
            'status'           => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by'      => $admin?->id,
            'reviewed_at'      => now(),
        ]);
        $kyc->user->update(['kyc_status' => 'rejected']);

        return back()->with('success', 'KYC submission rejected.');
    }

    /**
     * Admin: toggle auto-approval setting.
     */
    public function adminToggleAuto(Request $request)
    {
        $s = $this->settings->all();
        $s->update(['kyc_auto_approval' => $request->boolean('enabled')]);
        $this->settings->flush();

        return back()->with('success', 'KYC auto-approval setting updated.');
    }
}
