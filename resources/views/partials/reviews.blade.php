@php
/** Reusable review/rating partial.
 * Variables expected:
 * @var string $reviewType   (gig|listing|task|profile)
 * @var \Illuminate\Database\Eloquent\Model $reviewTarget  (the model being reviewed)
 * @var \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection $reviews
 * @var \App\Models\Review|null $myReview  (the current user's existing review, optional)
 */
$targetId   = $reviewTarget->id;
$ratingAvg  = method_exists($reviewTarget, 'rating_avg') ? $reviewTarget->rating_avg : ($reviews->avg('rating') ?? 0);
$ratingCount= method_exists($reviewTarget, 'rating_count') ? $reviewTarget->rating_count : $reviews->count();
@endphp

<section class="reviews-block card mt-8 p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <x-icon name="star" class="w-5 h-5 text-amber-500" /> Ratings &amp; Reviews
        </h3>
        <div class="text-right">
            <div class="text-2xl font-bold text-amber-500">{{ number_format((float)$ratingAvg, 1) }}<span class="text-sm text-slate-400">/5</span></div>
            <div class="text-xs text-slate-400">{{ number_format((int)$ratingCount) }} review(s)</div>
        </div>
    </div>

    {{-- Star summary bar --}}
    @php
        $dist = [5=>0,4=>0,3=>0,2=>0,1=>0];
        foreach($reviews as $r){ if(isset($dist[$r->rating])) $dist[$r->rating]++; }
    @endphp
    <div class="space-y-1 mb-5 max-w-md">
        @foreach([5,4,3,2,1] as $star)
            @php $pct = $ratingCount ? round($dist[$star]/$ratingCount*100) : 0; @endphp
            <div class="flex items-center gap-2 text-xs">
                <span class="w-6 text-slate-500">{{$star}}★</span>
                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-400" style="width:{{$pct}}%"></div>
                </div>
                <span class="w-6 text-right text-slate-400">{{$dist[$star]}}</span>
            </div>
        @endforeach
    </div>

    {{-- Review form (auth users only, not owner) --}}
    @auth('web')
        @php $ownerId = ($reviewType==='profile') ? $reviewTarget->id : ($reviewTarget->user_id ?? null); @endphp
        @if((int)$ownerId !== (int)auth('web')->id())
        <form action="{{ route('reviews.store') }}" method="POST" class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
            @csrf
            <input type="hidden" name="type" value="{{ $reviewType }}">
            <input type="hidden" name="id" value="{{ $targetId }}">
            <h4 class="font-semibold text-slate-700 mb-2">{{ $myReview ? 'Update your review' : 'Write a review' }}</h4>
            <div class="flex items-center gap-1 mb-3" id="star-input-wrap">
                @for($i=1;$i<=5;$i++)
                    <button type="button" data-val="{{$i}}" class="star-btn text-2xl text-slate-300 hover:text-amber-400 transition" onclick="selectStar({{$i}})">★</button>
                @endfor
                <input type="hidden" name="rating" id="rating-val" value="{{ $myReview->rating ?? 5 }}" required>
                <span id="rating-label" class="ml-2 text-sm text-slate-500"></span>
            </div>
            <textarea name="body" rows="3" class="form-input w-full" placeholder="Share your experience... (optional)">{{ $myReview->body ?? '' }}</textarea>
            <div class="flex items-center justify-between mt-3">
                @if($myReview)
                    <span class="text-xs text-slate-400">You already reviewed this — submitting updates it.</span>
                @endif
                <button type="submit" class="btn btn-primary ml-auto">{{ $myReview ? 'Update Review' : 'Post Review' }}</button>
            </div>
        </form>
        @else
            <p class="text-sm text-slate-400 mb-6">You cannot review your own item.</p>
        @endif
    @endauth

    {{-- Review list --}}
    @if($reviews->isNotEmpty())
        <div class="space-y-4">
            @foreach($reviews as $review)
                <div class="border-b border-slate-100 pb-4 last:border-0">
                    <div class="flex items-center gap-3 mb-1">
                        <img src="{{ $review->user?->avatarUrl() ?? 'https://ui-avatars.com/api/?name=?' }}" class="w-9 h-9 rounded-full object-cover" alt="">
                        <div>
                            <div class="font-semibold text-slate-700 text-sm">{{ $review->user?->username ?? 'Anonymous' }}</div>
                            <div class="text-amber-500 text-sm">
                                @for($i=1;$i<=5;$i++)
                                    @if($i<=$review->rating)★@else☆@endif
                                @endfor
                            </div>
                        </div>
                        <span class="ml-auto text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    @if($review->body)<p class="text-sm text-slate-600 mt-1">{{ $review->body }}</p>@endif
                </div>
            @endforeach
        </div>
        @if(method_exists($reviews,'links')) {{ $reviews->links() }} @endif
    @else
        <p class="text-sm text-slate-400 text-center py-6">No reviews yet. Be the first to review!</p>
    @endif
</section>

<script>
function selectStar(v){
    document.getElementById('rating-val').value = v;
    const labels={1:'Poor',2:'Fair',3:'Good',4:'Very Good',5:'Excellent'};
    document.getElementById('rating-label').textContent=labels[v]||'';
    document.querySelectorAll('#star-input-wrap .star-btn').forEach(function(b){
        b.style.color = (parseInt(b.dataset.val) <= v) ? '#f59e0b' : '#cbd5e1';
    });
}
document.addEventListener('DOMContentLoaded',function(){ selectStar(document.getElementById('rating-val').value); });
</script>
