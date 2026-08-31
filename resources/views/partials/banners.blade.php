@php
/**
 * Header banner, footer banner, popup banner and PWA install popup.
 * All settings are admin-managed via /admin/banner-settings.
 * Reads from SiteSetting (key/value table) with safe defaults.
 */
$hbEnabled   = (bool) \App\Models\SiteSetting::get('header_banner_enabled', 0);
$hbText      = \App\Models\SiteSetting::get('header_banner_text', '');
$hbBg        = \App\Models\SiteSetting::get('header_banner_bg_color', '#1877F2');
$hbFg        = \App\Models\SiteSetting::get('header_banner_text_color', '#ffffff');
$hbLink      = \App\Models\SiteSetting::get('header_banner_link', '');

$fbEnabled   = (bool) \App\Models\SiteSetting::get('footer_banner_enabled', 0);
$fbText      = \App\Models\SiteSetting::get('footer_banner_text', '');
$fbBg        = \App\Models\SiteSetting::get('footer_banner_bg_color', '#1877F2');
$fbFg        = \App\Models\SiteSetting::get('footer_banner_text_color', '#ffffff');
$fbLink      = \App\Models\SiteSetting::get('footer_banner_link', '');

$popEnabled  = (bool) \App\Models\SiteSetting::get('popup_banner_enabled', 0);
$popTitle    = \App\Models\SiteSetting::get('popup_banner_title', '');
$popDesc     = \App\Models\SiteSetting::get('popup_banner_description', '');
$popImage    = \App\Models\SiteSetting::get('popup_banner_image', '');
$popLink     = \App\Models\SiteSetting::get('popup_banner_link', '');
$popLinkTxt  = \App\Models\SiteSetting::get('popup_banner_link_text', 'Learn More');
$popBg       = \App\Models\SiteSetting::get('popup_banner_bg_color', '#ffffff');
$popDelay    = (int) \App\Models\SiteSetting::get('popup_banner_delay_seconds', 3);
$popReShow   = (int) \App\Models\SiteSetting::get('popup_banner_show_again_hours', 24);

$pwaEnabled  = (bool) \App\Models\SiteSetting::get('pwa_popup_enabled', 0);
$pwaTitle    = \App\Models\SiteSetting::get('pwa_popup_title', 'Install App');
$pwaDesc     = \App\Models\SiteSetting::get('pwa_popup_description', 'Install Airtrendmedia on your home screen for a faster experience.');
$pwaInstBtn  = \App\Models\SiteSetting::get('pwa_popup_install_btn_text', 'Install Now');
$pwaDismBtn  = \App\Models\SiteSetting::get('pwa_popup_dismiss_btn_text', 'Not Now');
@endphp

<!-- HEADER BANNER -->
@if($hbEnabled && $hbText)
<div id="site-header-banner" style="background:{{ $hbBg }};color:{{ $hbFg }};" class="w-full text-center text-sm font-medium py-2 px-4 relative">
    @if($hbLink)<a href="{{ $hbLink }}" style="color:{{ $hbFg }};">{!! $hbText !!}</a>@else{!! $hbText !!}@endif
    <button type="button" onclick="document.getElementById('site-header-banner').remove()" style="color:{{ $hbFg }};" class="absolute right-2 top-1/2 -translate-y-1/2 opacity-70 hover:opacity-100 text-lg leading-none">&times;</button>
</div>
@endif

<!-- FOOTER BANNER -->
@if($fbEnabled && $fbText)
<div id="site-footer-banner" style="background:{{ $fbBg }};color:{{ $fbFg }};" class="w-full text-center text-sm font-medium py-3 px-4">
    @if($fbLink)<a href="{{ $fbLink }}" style="color:{{ $fbFg }};">{!! $fbText !!}</a>@else{!! $fbText !!}@endif
</div>
@endif

<!-- POPUP BANNER -->
@if($popEnabled && ($popTitle || $popDesc))
<div id="site-popup-banner" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden relative" style="background:{{ $popBg }};">
        <button type="button" onclick="closePopupBanner()" class="absolute top-3 right-3 text-slate-500 hover:text-slate-800 dark:hover:text-white text-2xl leading-none z-10">&times;</button>
        @if($popImage && Storage::disk('public')->exists($popImage))
            <img src="{{ storage_asset($popImage) }}" alt="{{ $popTitle }}" class="w-full h-40 object-cover">
        @endif
        <div class="p-6">
            @if($popTitle)<h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-2">{{ $popTitle }}</h3>@endif
            @if($popDesc)<p class="text-sm text-slate-600 dark:text-slate-300 mb-4">{!! $popDesc !!}</p>@endif
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closePopupBanner()" class="px-4 py-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 text-sm">{{ $pwaDismBtn }}</button>
                @if($popLink)<a href="{{ $popLink }}" onclick="closePopupBanner()" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">{{ $popLinkTxt }}</a>@endif
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var key='popup_banner_last_shown';
    var delay={{ $popDelay }}*1000;
    var reShow={{ $popReShow }}*3600*1000;
    var last=parseInt(localStorage.getItem(key)||'0',10);
    if(Date.now()-last < reShow) return;
    setTimeout(function(){
        var el=document.getElementById('site-popup-banner');
        if(!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
        localStorage.setItem(key, Date.now().toString());
    }, delay);
    window.closePopupBanner=function(){
        var el=document.getElementById('site-popup-banner');
        if(el){ el.classList.add('hidden'); el.classList.remove('flex'); }
    };
    document.addEventListener('keydown',function(e){ if(e.key==='Escape') closePopupBanner(); });
})();
</script>
@endif

<!-- PWA INSTALL POPUP -->
@if($pwaEnabled)
<div id="pwa-install-popup" class="fixed bottom-4 right-4 z-[55] hidden bg-white dark:bg-slate-800 rounded-xl shadow-2xl p-4 max-w-xs border border-slate-200 dark:border-slate-700">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white text-xl flex-shrink-0">📱</div>
        <div class="flex-1">
            <h4 class="font-semibold text-slate-800 dark:text-slate-100 text-sm">{{ $pwaTitle }}</h4>
            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">{{ $pwaDesc }}</p>
            <div class="flex gap-2 mt-3">
                <button type="button" id="pwa-install-btn" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium">{{ $pwaInstBtn }}</button>
                <button type="button" onclick="dismissPwaPopup()" class="px-3 py-1.5 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 text-xs">{{ $pwaDismBtn }}</button>
            </div>
        </div>
        <button type="button" onclick="dismissPwaPopup()" class="text-slate-400 hover:text-slate-700 text-lg leading-none">&times;</button>
    </div>
</div>
<script>
(function(){
    var key='pwa_popup_dismissed';
    var last=parseInt(localStorage.getItem(key)||'0',10);
    var reShow={{ $popReShow }}*3600*1000;
    if(Date.now()-last < reShow) return;
    var deferredPrompt=null;
    window.addEventListener('beforeinstallprompt',function(e){
        e.preventDefault();
        deferredPrompt=e;
        setTimeout(function(){
            var el=document.getElementById('pwa-install-popup');
            if(el){ el.classList.remove('hidden'); }
        }, {{ $popDelay }}*1000);
    });
    window.addEventListener('appinstalled',function(){ dismissPwaPopup(); });
    document.addEventListener('DOMContentLoaded',function(){
        var btn=document.getElementById('pwa-install-btn');
        if(btn){
            btn.addEventListener('click',function(){
                if(deferredPrompt){
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then(function(){ deferredPrompt=null; dismissPwaPopup(); });
                } else {
                    alert('To install: tap your browser menu and select "Add to Home Screen".');
                    dismissPwaPopup();
                }
            });
        }
    });
    window.dismissPwaPopup=function(){
        var el=document.getElementById('pwa-install-popup');
        if(el) el.classList.add('hidden');
        localStorage.setItem(key, Date.now().toString());
    };
})();
</script>
@endif
