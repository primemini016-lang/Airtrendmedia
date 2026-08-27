@extends('layouts.user')

@section('title', 'Account Type')
@section('heading', 'Account Type')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="card">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                    <x-icon name="settings" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">Choose Your Account Type</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Switch between being a Freelancer (perform micro-jobs), an Advertiser (run sponsored ads), or both. You can change this anytime.</p>
                </div>
            </div>
        </div>
    </div>

    @if(!$enabled)
        <div class="card border-amber-300">
            <div class="card-body">
                <p class="text-amber-600 font-semibold flex items-center gap-2"><x-icon name="complaints" class="w-5 h-5" /> Account type switching is currently disabled by the administrator.</p>
            </div>
        </div>
    @endif

    <form action="{{ route('user.account-type.switch') }}" method="POST">
        @csrf
        <div class="grid sm:grid-cols-3 gap-4">
            {{-- Freelancer --}}
            <label class="card cursor-pointer hover:border-blue-400 transition @if($user->account_type === 'freelancer' || $user->account_type === 'both') border-blue-500 ring-2 ring-blue-200 @endif">
                <div class="card-body text-center">
                    <input type="radio" name="account_type" value="freelancer" class="hidden" @if($user->account_type === 'freelancer') checked @endif>
                    <div class="w-14 h-14 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center mx-auto mb-3">
                        <x-icon name="briefcase" class="w-7 h-7 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100">Freelancer</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perform micro-jobs, complete social media tasks, and earn money.</p>
                    <ul class="text-xs text-left text-slate-500 mt-3 space-y-1">
                        <li class="flex items-start gap-2"><x-icon name="check" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" /> Browse & complete tasks</li>
                        <li class="flex items-start gap-2"><x-icon name="check" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" /> Submit task proofs</li>
                        <li class="flex items-start gap-2"><x-icon name="check" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" /> Withdraw earnings</li>
                    </ul>
                </div>
            </label>

            {{-- Advertiser --}}
            <label class="card cursor-pointer hover:border-blue-400 transition @if($user->account_type === 'advertiser' || $user->account_type === 'both') border-blue-500 ring-2 ring-blue-200 @endif">
                <div class="card-body text-center">
                    <input type="radio" name="account_type" value="advertiser" class="hidden" @if($user->account_type === 'advertiser') checked @endif>
                    <div class="w-14 h-14 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center mx-auto mb-3">
                        <x-icon name="ads" class="w-7 h-7 text-purple-600 dark:text-purple-400" />
                    </div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100">Advertiser</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Run sponsored ads in the feed. Pay per click and reach thousands.</p>
                    <ul class="text-xs text-left text-slate-500 mt-3 space-y-1">
                        <li class="flex items-start gap-2"><x-icon name="check" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" /> Create sponsored ads</li>
                        <li class="flex items-start gap-2"><x-icon name="check" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" /> Track clicks & impressions</li>
                        <li class="flex items-start gap-2"><x-icon name="check" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" /> Set your own budget</li>
                    </ul>
                </div>
            </label>

            {{-- Both --}}
            <label class="card cursor-pointer hover:border-blue-400 transition @if($user->account_type === 'both') border-blue-500 ring-2 ring-blue-200 @endif>
                <div class="card-body text-center">
                    <input type="radio" name="account_type" value="both" class="hidden" @if($user->account_type === 'both') checked @endif>
                    <div class="w-14 h-14 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mx-auto mb-3">
                        <x-icon name="star" class="w-7 h-7 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100">Both</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Get the best of both worlds — earn as a freelancer and advertise.</p>
                    <ul class="text-xs text-left text-slate-500 mt-3 space-y-1">
                        <li class="flex items-start gap-2"><x-icon name="check" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" /> All freelancer features</li>
                        <li class="flex items-start gap-2"><x-icon name="check" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" /> All advertiser features</li>
                        <li class="flex items-start gap-2"><x-icon name="check" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" /> Maximum flexibility</li>
                    </ul>
                </div>
            </label>
        </div>

        @if($enabled)
            <div class="mt-6 text-center">
                <button type="submit" class="btn btn-primary px-8">Save Account Type</button>
            </div>
        @endif
    </form>

    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-2">Current Status</h3>
            <div class="flex flex-wrap gap-2">
                @if($user->isFreelancer())<span class="badge badge-success">Freelancer</span>@endif
                @if($user->isAdvertiser())<span class="badge badge-success">Advertiser</span>@endif
                @if($user->isKycVerified())<span class="badge badge-success">KYC Verified</span>@else<span class="badge badge-warning">KYC Pending</span>@endif
                @if($user->isBlueVerified())<span class="badge" style="background:#1877F2;color:white" class="inline-flex items-center gap-1"><x-icon name="check" class="w-3 h-3" /> Blue Badge</span>@endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Update radio selection visual
document.querySelectorAll('label.card input[type=radio]').forEach(input => {
    input.addEventListener('change', () => {
        document.querySelectorAll('label.card').forEach(l => l.classList.remove('border-blue-500','ring-2','ring-blue-200'));
        input.closest('label.card').classList.add('border-blue-500','ring-2','ring-blue-200');
    });
});
</script>
@endpush
@endsection
