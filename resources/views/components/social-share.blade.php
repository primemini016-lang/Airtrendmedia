@props(['url' => null, 'title' => 'Share'])
@php($shareUrl = $url ?: url()->current())
<div class="flex items-center gap-2" data-share-url="{{ e($shareUrl) }}">
    <button type="button" class="atm-social-btn copy" data-share-copy data-share-value="{{ e($shareUrl) }}" aria-label="Copy link" title="Copy link"><x-icon name="link" /></button>
    <button type="button" class="atm-social-btn native" data-share-native data-share-title="{{ e($title) }}" data-share-value="{{ e($shareUrl) }}" aria-label="Share" title="Share"><x-icon name="share" /></button>
</div>
@push('scripts')
<script>
document.addEventListener('click',function(e){
 const copy=e.target.closest('[data-share-copy]'); if(copy){ const url=copy.dataset.shareValue; const done=()=>{copy.dataset.done='1'; setTimeout(()=>copy.dataset.done='0',1200)}; if(navigator.clipboard?.writeText) navigator.clipboard.writeText(url).then(done).catch(()=>fallback(url,done)); else fallback(url,done); }
 const native=e.target.closest('[data-share-native]'); if(native && navigator.share){ navigator.share({title:native.dataset.shareTitle||document.title,url:native.dataset.shareValue}).catch(()=>{}); }
});
function fallback(text,done){const t=document.createElement('textarea');t.value=text;t.setAttribute('readonly','');t.style.position='fixed';t.style.opacity='0';document.body.appendChild(t);t.select();try{document.execCommand('copy');done()}finally{t.remove()}}
</script>
@endpush
