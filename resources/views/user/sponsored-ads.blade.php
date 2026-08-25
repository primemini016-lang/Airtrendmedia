@extends('layouts.user')

@section('title', 'Sponsored Ads')
@section('heading', 'Sponsored Ads Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="card bg-gradient-to-r from-purple-600 to-blue-700 text-white">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                    <x-icon name="ads" class="w-6 h-6" />
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold">Sponsored Advertising</h2>
                    <p class="text-purple-100 text-sm mt-1">Promote your business in the Airtrendmedia feed. Pay only ${{ number_format($defaultCpc, 2) }} per click. {{ $autoApprove ? 'Ads are auto-approved.' : 'Ads are reviewed before going live.' }}</p>
                </div>
                @if(auth('web')->user()->isAdvertiser())
                    <button onclick="document.getElementById('new-ad-form').classList.toggle('hidden')" class="btn bg-white text-purple-700 hover:bg-purple-50">+ New Ad</button>
                @endif
            </div>
        </div>
    </div>

    @if(!auth('web')->user()->isAdvertiser())
        <div class="card border-amber-300">
            <div class="card-body">
                <p class="text-amber-600 font-semibold">You need an Advertiser account to create sponsored ads. <a href="{{ route('user.account-type') }}" class="underline">Switch account type →</a></p>
            </div>
        </div>
    @endif

    {{-- New ad form (hidden by default) --}}
    <div id="new-ad-form" class="card hidden">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-4">Create Sponsored Ad</h3>
            <form action="{{ route('user.sponsored-ads.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Ad Title *</label>
                        <input type="text" name="title" required class="form-input" maxlength="120" placeholder="Your ad headline">
                    </div>
                    <div>
                        <label class="form-label">Ad Type *</label>
                        <select name="ad_type" required class="form-input" onchange="toggleMedia(this)">
                            <option value="text">Text Only</option>
                            <option value="image">Image Ad</option>
                            <option value="video">Video Ad</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Description *</label>
                    <textarea name="description" required class="form-input" rows="3" maxlength="500" placeholder="Describe your product or service..."></textarea>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Link URL *</label>
                        <input type="url" name="link_url" required class="form-input" placeholder="https://your-site.com">
                    </div>
                    <div>
                        <label class="form-label">CTA Button Text</label>
                        <input type="text" name="cta_button" class="form-input" maxlength="30" placeholder="Learn More" value="Learn More">
                    </div>
                </div>
                <div id="media-field" class="hidden">
                    <label class="form-label">Media (Image or Video)</label>
                    <input type="file" name="media" accept="image/*,video/*" class="form-input text-sm">
                </div>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Budget (USD) *</label>
                        <input type="number" name="budget" required class="form-input" min="1" step="0.01" placeholder="10.00">
                    </div>
                    <div>
                        <label class="form-label">Cost Per Click (USD)</label>
                        <input type="number" name="cost_per_click" class="form-input" min="0.01" step="0.01" value="{{ number_format($defaultCpc, 2) }}">
                        <p class="text-xs text-slate-400 mt-1">Default: ${{ number_format($defaultCpc, 2) }}</p>
                    </div>
                    <div>
                        <label class="form-label">Start Date (optional)</label>
                        <input type="date" name="starts_at" class="form-input">
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">Create Ad</button>
                    <button type="button" onclick="document.getElementById('new-ad-form').classList.add('hidden')" class="btn btn-ghost">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ads list --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-4">Your Ads</h3>
            @if($ads->isEmpty())
                <p class="text-center text-slate-400 py-8">No ads yet. Create your first sponsored ad to reach thousands of users.</p>
            @else
                <div class="space-y-3">
                    @foreach($ads as $ad)
                        <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="badge @if($ad->status==='active') badge-success @elseif($ad->status==='pending_review') badge-warning @elseif($ad->status==='rejected') badge-danger @else badge-default @endif">{{ ucfirst(str_replace('_',' ',$ad->status)) }}</span>
                                        <span class="text-xs text-slate-400 uppercase">{{ $ad->ad_type }}</span>
                                    </div>
                                    <h4 class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $ad->title }}</h4>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2">{{ $ad->description }}</p>
                                    @if($ad->status === 'rejected' && $ad->rejection_reason)
                                        <p class="text-xs text-red-500 mt-1">Reason: {{ $ad->rejection_reason }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('user.sponsored-ads.stats', $ad) }}" class="btn btn-ghost text-sm flex-shrink-0">Stats →</a>
                            </div>
                            <div class="grid grid-cols-4 gap-2 mt-3 text-center text-sm">
                                <div><p class="text-xs text-slate-400">Impressions</p><p class="font-bold text-slate-700 dark:text-slate-200">{{ number_format($ad->impressions) }}</p></div>
                                <div><p class="text-xs text-slate-400">Clicks</p><p class="font-bold text-slate-700 dark:text-slate-200">{{ number_format($ad->clicks) }}</p></div>
                                <div><p class="text-xs text-slate-400">Spent</p><p class="font-bold text-green-600">${{ number_format($ad->amount_spent, 2) }}</p></div>
                                <div><p class="text-xs text-slate-400">Budget</p><p class="font-bold text-slate-700 dark:text-slate-200">${{ number_format($ad->budget, 2) }}</p></div>
                            </div>
                            <div class="mt-3">
                                <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                    @php $pct = $ad->budget > 0 ? min(100, (float)$ad->amount_spent / (float)$ad->budget * 100) : 0; @endphp
                                    <div class="h-full bg-blue-600" style="width: {{ $pct }}%"></div>
                                </div>
                                <p class="text-xs text-slate-400 mt-1 text-right">{{ number_format($pct, 0) }}% budget used</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{ $ads->links() }}
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleMedia(select) {
    const field = document.getElementById('media-field');
    if (select.value === 'image' || select.value === 'video') {
        field.classList.remove('hidden');
    } else {
        field.classList.add('hidden');
    }
}
</script>
@endpush
@endsection
