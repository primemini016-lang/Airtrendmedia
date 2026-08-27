@extends('layouts.app')
@section('title', $ad->title . ' — PTC Ad')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    <a href="{{ route('ptc.index') }}" class="text-blue-600 hover:underline text-sm mb-4 inline-block">← Back to PTC Ads</a>

    <div class="card overflow-hidden" x-data="ptcViewer({
        adId: {{ $ad->id }},
        duration: {{ $ad->duration_seconds }},
        reward: {{ (float) $ad->reward_per_view }},
        csrf: '{{ csrf_token() }}',
        startUrl: '{{ route('ptc.start', $ad) }}',
        confirmUrl: '{{ route('ptc.confirm', $ad) }}',
        alreadyViewed: {{ $alreadyViewed ? 'true' : 'false' }}
    })" x-init="init()">
        <!-- Ad Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-5">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h1 class="text-xl font-bold">{{ $ad->title }}</h1>
                    <p class="text-blue-100 text-sm">by {{ $ad->user?->username ?? 'Advertiser' }} · {{ $ad->duration_seconds }}s · Reward ${{ number_format((float)$ad->reward_per_view, 4) }}</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold" x-text="timerDisplay">00:10</div>
                    <div class="text-xs text-blue-100" x-show="!finished && !alreadyViewed">Time remaining</div>
                </div>
            </div>
        </div>

        <!-- Ad Body -->
        <div class="p-6">
            @if($ad->image)
                <img src="{{ $ad->imageUrl() }}" class="w-full max-h-80 object-cover rounded-xl mb-4" alt="{{ $ad->title }}">
            @endif

            @if($ad->description)
                <div class="prose max-w-none text-slate-700 mb-4">{!! nl2br(e($ad->description)) !!}</div>
            @endif

            @if($ad->url)
                <div class="bg-slate-50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-slate-400 mb-1">Visit the advertiser:</p>
                    <a href="{{ $ad->url }}" target="_blank" rel="nofollow noopener sponsored" class="text-blue-600 hover:underline break-all">{{ $ad->url }}</a>
                </div>
            @endif

            <!-- Progress bar -->
            <div class="mb-4" x-show="!finished && !alreadyViewed">
                <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-full transition-all duration-1000 ease-linear" :style="`width: ${progressPercent}%`"></div>
                </div>
            </div>

            <!-- Status / Confirm button -->
            <div class="text-center py-4">
                <template x-if="alreadyViewed">
                    <div class="text-green-600 font-semibold text-lg">✓ You already completed this ad today. Come back tomorrow!</div>
                </template>

                <template x-if="!alreadyViewed && !finished">
                    <div class="text-slate-500">⏳ Please wait for the timer to finish…</div>
                </template>

                <template x-if="!alreadyViewed && finished && !confirmed">
                    <button @click="confirmExecution()"
                        class="inline-flex items-center gap-2 px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-bold text-lg rounded-xl shadow-lg transition transform hover:scale-105">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        Confirm Execution & Claim $<span x-text="reward.toFixed(4)"></span>
                    </button>
                </template>

                <template x-if="confirmed">
                    <div class="bg-green-50 border border-green-200 rounded-xl p-5">
                        <div class="text-2xl mb-2">🎉</div>
                        <div class="text-green-700 font-bold text-lg" x-text="message"></div>
                        <div class="text-sm text-green-600 mt-1">New balance: $<span x-text="newBalance.toFixed(2)"></span></div>
                        <a href="{{ route('ptc.index') }}" class="btn btn-primary mt-4">Watch More Ads</a>
                    </div>
                </template>

                <template x-if="error">
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-600" x-text="error"></div>
                </template>
            </div>
        </div>
    </div>

    @guest
    <div class="card mt-6 p-5 text-center bg-blue-50 border-blue-200">
        <p class="text-slate-700 font-semibold">Want to earn from watching ads?</p>
        <p class="text-slate-500 text-sm mb-3">Sign up free and get paid instantly for every ad you watch.</p>
        <a href="{{ route('register') }}" class="btn btn-primary">Create Free Account</a>
    </div>
    @endguest
</div>

@push('scripts')
<script>
function ptcViewer(data) {
    return {
        ...data,
        elapsed: 0,
        timerDisplay: '00:' + String(data.duration).padStart(2,'0'),
        progressPercent: 0,
        finished: false,
        confirmed: false,
        alreadyViewed: data.alreadyViewed,
        viewId: null,
        newBalance: 0,
        message: '',
        error: '',
        timerInterval: null,

        init() {
            if (this.alreadyViewed) return;
            this.startSession();
        },

        startSession() {
            fetch(this.startUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrf,
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(res => {
                if (res.error) { this.error = res.error; return; }
                this.viewId = res.view_id;
                this.startTimer();
            }).catch(() => { this.error = 'Could not start the ad session. Please refresh.'; });
        },

        startTimer() {
            const startTime = Date.now();
            this.timerInterval = setInterval(() => {
                this.elapsed = Math.floor((Date.now() - startTime) / 1000);
                const remaining = Math.max(0, this.duration - this.elapsed);
                const mins = Math.floor(remaining / 60);
                const secs = remaining % 60;
                this.timerDisplay = (mins > 0 ? String(mins).padStart(2,'0') + ':' : '') + String(secs).padStart(2,'0');
                this.progressPercent = Math.min(100, (this.elapsed / this.duration) * 100);
                if (this.elapsed >= this.duration) {
                    clearInterval(this.timerInterval);
                    this.finished = true;
                    this.playFinishSound();
                }
            }, 250);
        },

        confirmExecution() {
            this.error = '';
            fetch(this.confirmUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    view_id: this.viewId,
                    watched_seconds: this.elapsed
                })
            }).then(r => r.json()).then(res => {
                if (res.error) { this.error = res.error; return; }
                this.confirmed = true;
                this.newBalance = res.new_balance;
                this.message = res.message;
                this.playRewardSound();
            }).catch(() => { this.error = 'Could not confirm execution. Please try again.'; });
        },

        playFinishSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const o = ctx.createOscillator(); const g = ctx.createGain();
                o.connect(g); g.connect(ctx.destination);
                o.frequency.value = 880; o.type = 'sine';
                g.gain.setValueAtTime(0.001, ctx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.3, ctx.currentTime + 0.05);
                g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                o.start(); o.stop(ctx.currentTime + 0.4);
            } catch(e) {}
        },

        playRewardSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                [523.25, 659.25, 783.99, 1046.50].forEach((freq, i) => {
                    const o = ctx.createOscillator(); const g = ctx.createGain();
                    o.connect(g); g.connect(ctx.destination);
                    o.frequency.value = freq; o.type = 'sine';
                    const t = ctx.currentTime + i * 0.12;
                    g.gain.setValueAtTime(0.001, t);
                    g.gain.exponentialRampToValueAtTime(0.25, t + 0.03);
                    g.gain.exponentialRampToValueAtTime(0.001, t + 0.3);
                    o.start(t); o.stop(t + 0.3);
                });
            } catch(e) {}
        }
    };
}
</script>
@endpush
@endsection
