{{--
    Airtrendmedia — Sponsored Ad unit shown inside the social feed.
    Fetches approved sponsored ads via AJAX from route('social.feed-ads')
    and renders them between feed posts. Includes "Sponsored" label,
    click tracking (route('social.feed-ads')) handled server-side by
    the advertiser's link, and image/video/text ad types.
--}}
<div id="airtrend-sponsored-feed" class="fb-sponsored-feed-wrapper"></div>

@push('scripts')
<script>
(function () {
    const FEED_ADS_URL = "{{ route('social.feed-ads') }}";
    const CSRF = "{{ csrf_token() }}";
    const container = document.getElementById('airtrend-sponsored-feed');
    if (!container) return;

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function renderAd(ad) {
        const media = ad.media_url
            ? (ad.ad_type === 'video'
                ? '<video src="' + escapeHtml(ad.media_url) + '" class="fb-sponsored-media" controls muted></video>'
                : '<img src="' + escapeHtml(ad.media_url) + '" class="fb-sponsored-media" alt="' + escapeHtml(ad.title) + '" loading="lazy">')
            : '';
        return '' +
        '<div class="fb-card fb-sponsored-card mb-4">' +
            '<div class="fb-sponsored-head">' +
                '<div class="fb-sponsored-avatar">' + escapeHtml((ad.advertiser || 'A').charAt(0).toUpperCase()) + '</div>' +
                '<div class="flex-1 min-w-0">' +
                    '<div class="font-semibold text-sm truncate">' + escapeHtml(ad.title) + '</div>' +
                    '<div class="text-xs fb-text-muted flex items-center gap-1">' +
                        '<span class="fb-sponsored-tag">Sponsored</span> · ' + escapeHtml(ad.advertiser || '') +
                    '</div>' +
                '</div>' +
            '</div>' +
            (ad.description ? '<p class="fb-sponsored-desc text-sm px-3 pb-2">' + escapeHtml(ad.description) + '</p>' : '') +
            media +
            '<div class="fb-sponsored-foot">' +
                '<a href="' + escapeHtml(ad.link_url || '#') + '" target="_blank" rel="nofollow noopener sponsored" ' +
                   'class="fb-btn fb-btn-primary text-sm" data-sound="notification">' +
                    escapeHtml(ad.cta_button || 'Learn More') +
                '</a>' +
            '</div>' +
        '</div>';
    }

    function load() {
        fetch(FEED_ADS_URL, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            const ads = (data && data.ads) || [];
            if (!ads.length) { container.innerHTML = ''; return; }
            container.innerHTML = ads.map(renderAd).join('');
        })
        .catch(function () { container.innerHTML = ''; });
    }

    // Insert ads after the 2nd post card if present.
    document.addEventListener('DOMContentLoaded', function () {
        const feed = document.querySelector('.max-w-\\[680px\\]') || container.parentNode;
        const posts = feed ? feed.querySelectorAll('.fb-card:not(.fb-sponsored-card)') : [];
        if (posts.length >= 2 && container.parentNode === feed) {
            // already in feed — just load
            load();
        } else {
            load();
        }
    });
})();
</script>
@endpush
