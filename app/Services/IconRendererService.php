<?php

namespace App\Services;

/**
 * Renders inline SVG icons keyed by name.
 * Used by the @categoryIcon Blade directive.
 */
class IconRendererService
{
    /**
     * SVG path definitions keyed by icon name.
     */
    protected array $paths = [
        'facebook'      => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
        'facebook-like' => '<path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11h6a2 2 0 0 1 2 2.5l-1.5 6A2 2 0 0 1 17.5 21H7z"/>',
        'facebook-page' => '<path d="M14 9V5a3 3 0 0 0-6 0v4H4v12h16V9h-6z"/><path d="M14 9h6M9 9h5"/>',
        'twitter'       => '<path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>',
        'twitter-retweet'=> '<path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>',
        'instagram'     => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>',
        'instagram-followers' => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="11" r="3"/><path d="M8 17a4 4 0 0 1 8 0"/>',
        'youtube'       => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>',
        'youtube-subscribers' => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>',
        'youtube-watch' => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/><path d="M12 7v5l3 2"/>',
        'tiktok'        => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/>',
        'linkedin'      => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
        'linkedin-connect' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/><path d="M18 2v6M15 5h6"/>',
        'telegram'      => '<path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/>',
        'telegram-join' => '<path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/><circle cx="18" cy="18" r="3"/><path d="M20.5 18h-5"/>',
        'whatsapp'      => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>',
        'whatsapp-message' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/><path d="M8 11.5l2 2 4-4"/>',
        'pinterest'     => '<circle cx="12" cy="12" r="10"/><path d="M9 21l3-9M9 12c0-2 1.5-4 4-4s4 2 4 4-1.5 4-4 4"/>',
        'snapchat'      => '<path d="M12 2c3 0 5 2 5 5 0 2-1 4-1 6 1 1 2 1 3 1-1 1-2 2-4 2 0 1 1 3 2 4-2 0-4-1-5-3-1 2-3 3-5 3 1-1 2-3 2-4-2 0-3-1-4-2 1 0 2 0 3-1 0-2-1-4-1-6 0-3 2-5 5-5z"/>',
        'reddit'        => '<circle cx="12" cy="12" r="10"/><circle cx="9" cy="13" r="1"/><circle cx="15" cy="13" r="1"/><path d="M9 16c1 1 2 1 3 1s2 0 3-1"/><circle cx="18" cy="8" r="1.5"/>',
        'discord'       => '<path d="M19 5a16 16 0 0 0-4-1l-.5 1A14 14 0 0 0 12 5a14 14 0 0 0-2.5 0L9 4a16 16 0 0 0-4 1C3 8 2 11 2 14a16 16 0 0 0 5 2l1-2c-1 0-2 0-3-1 1 0 2-1 3-1 2 2 4 2 6 2s4 0 6-2c1 0 2 1 3 1-1 1-2 1-3 1l1 2a16 16 0 0 0 5-2c0-3-1-6-3-9z"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/>',
        'twitch'        => '<path d="M3 21h4V9h8v4M11 17h4M3 3h18v12l-4 4M19 17v-4"/>',
        'spotify'       => '<circle cx="12" cy="12" r="10"/><path d="M7 14c3-1 7-1 10 1M7.5 11c3.5-1 8-1 11 1.5M8 8c3-1 6.5-1 9 1"/>',
        'soundcloud'    => '<path d="M3 14v4M5 12v6M7 11v7M9 10v8M11 9v9M13 8v10M15 7v11"/><rect x="16" y="9" width="6" height="9" rx="1"/>',
        'website'       => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
        'website-visit' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/><path d="M12 8v8M9 11l3-3 3 3"/>',
        'google'        => '<circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/>',
        'app-install'   => '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01M12 6v6M9 9l3-3 3 3"/>',
        'app-review'    => '<rect x="5" y="2" width="14" height="20" rx="2"/><polygon points="12 8 13 11 16 11 13.5 13 14.5 16 12 14 9.5 16 10.5 13 8 11 11 11 12 8"/>',
        'survey'        => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/>',
        'review'        => '<polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9 12 2"/>',
        'signup'        => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>',
        'subscribe'     => '<path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
        'comment'       => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'share'         => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/>',
        'follow'        => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>',
        'like'          => '<path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11h6a2 2 0 0 1 2 2.5l-1.5 6A2 2 0 0 1 17.5 21H7z"/>',
        'view'          => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'vote'          => '<path d="M9 12l2 2 4-4"/><rect x="3" y="3" width="18" height="18" rx="2"/>',
        'writing'       => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>',
        'design'        => '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.9 0 1.6-.7 1.6-1.7 0-.4-.2-.8-.4-1.1-.3-.3-.4-.7-.4-1.1a1.6 1.6 0 0 1 1.7-1.7h2c3 0 5.5-2.5 5.5-5.6C22 6 17.5 2 12 2z"/>',
        'marketing'     => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'music'         => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
        'video'         => '<polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/>',
        'tech'          => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
        'briefcase'     => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'star'          => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'heart'         => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
        'globe'         => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',

        // ── Airtrendmedia exact social-media interaction icons ──
        // Facebook variants
        'facebook-follow'  => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/><path d="M19 14v6M22 17h-6"/>',
        'facebook-react'   => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/>',
        'facebook-share'   => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/>',
        'facebook-group'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'facebook-view'    => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'facebook-star'    => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/><polygon points="20 14 21 17 24 17 21.5 19 22.5 22 20 20 17.5 22 18.5 19 16 17 19 17 20 14"/>',
        // Instagram variants
        'instagram-follow' => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/><path d="M19 20v-2M22 21h-6"/>',
        'instagram-like'   => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/><path d="M12 15l-2-2c-1-1-1-3 0-4s3-1 4 0l-2 2"/>',
        'instagram-comment'=> '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/><path d="M8 14h8"/>',
        'instagram-story'  => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="11" r="3"/><path d="M8 17a4 4 0 0 1 8 0"/><circle cx="12" cy="12" r="9" stroke-dasharray="2 2"/>',
        'instagram-reel'   => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/><polygon points="10 13 14 11 10 9 10 13"/>',
        'instagram-save'   => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/><path d="M8 18l4-3 4 3V8H8z"/>',
        // YouTube variants
        'youtube-sub'      => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/><path d="M19 14v6M22 17h-6"/>',
        'youtube-like'     => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/>',
        'youtube-view'     => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>',
        'youtube-comment'  => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/><path d="M8 12h8"/>',
        // Twitter/X variants
        'twitter-follow'   => '<path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/><path d="M19 14v6M22 17h-6"/>',
        'twitter-like'     => '<path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/>',
        'twitter-comment'  => '<path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/><path d="M8 12h8"/>',
        // TikTok variants
        'tiktok-follow'    => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/><path d="M19 14v6M22 17h-6"/>',
        'tiktok-like'      => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/>',
        'tiktok-view'      => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'tiktok-comment'   => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/><path d="M8 12h8"/>',
        'tiktok-share'     => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/>',
        // LinkedIn variants
        'linkedin-follow'  => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/><path d="M19 20v-2M22 21h-6"/>',
        'linkedin-like'    => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/>',
        // Telegram variants
        'telegram-view'    => '<path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>',
        // WhatsApp variants
        'whatsapp-join'    => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/><path d="M18 18v6M21 21h-6"/>',
        // Pinterest variants
        'pinterest-follow' => '<circle cx="12" cy="12" r="10"/><path d="M9 21l3-9M9 12c0-2 1.5-4 4-4s4 2 4 4-1.5 4-4 4"/><path d="M19 14v6M22 17h-6"/>',
        'pinterest-pin'    => '<circle cx="12" cy="12" r="10"/><path d="M9 21l3-9M9 12c0-2 1.5-4 4-4s4 2 4 4-1.5 4-4 4"/><path d="M12 8v8M9 11l3-3 3 3"/>',
        // Spotify variants
        'spotify-play'     => '<circle cx="12" cy="12" r="10"/><path d="M7 14c3-1 7-1 10 1M7.5 11c3.5-1 8-1 11 1.5M8 8c3-1 6.5-1 9 1"/><polygon points="10 14 16 12 10 10 10 14"/>',
        'spotify-follow'   => '<circle cx="12" cy="12" r="10"/><path d="M7 14c3-1 7-1 10 1M7.5 11c3.5-1 8-1 11 1.5M8 8c3-1 6.5-1 9 1"/><path d="M19 20v-2M22 21h-6"/>',
        // SoundCloud variants
        'soundcloud-play'  => '<path d="M3 14v4M5 12v6M7 11v7M9 10v8M11 9v9M13 8v10M15 7v11"/><rect x="16" y="9" width="6" height="9" rx="1"/><polygon points="9 14 14 12 9 10 9 14"/>',
        'soundcloud-follow'=> '<path d="M3 14v4M5 12v6M7 11v7M9 10v8M11 9v9M13 8v10M15 7v11"/><rect x="16" y="9" width="6" height="9" rx="1"/><path d="M19 20v-2M22 21h-6"/>',
        // Twitch variants
        'twitch-follow'    => '<path d="M3 21h4V9h8v4M11 17h4M3 3h18v12l-4 4M19 17v-4"/><path d="M19 14v6M22 17h-6"/>',
        'twitch-view'      => '<path d="M3 21h4V9h8v4M11 17h4M3 3h18v12l-4 4M19 17v-4"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>',
        // Discord variants
        'discord-join'     => '<path d="M19 5a16 16 0 0 0-4-1l-.5 1A14 14 0 0 0 12 5a14 14 0 0 0-2.5 0L9 4a16 16 0 0 0-4 1C3 8 2 11 2 14a16 16 0 0 0 5 2l1-2c-1 0-2 0-3-1 1 0 2-1 3-1 2 2 4 2 6 2s4 0 6-2c1 0 2 1 3 1-1 1-2 1-3 1l1 2a16 16 0 0 0 5-2c0-3-1-6-3-9z"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><path d="M18 18v6M21 21h-6"/>',
        // Reddit variants
        'reddit-upvote'    => '<circle cx="12" cy="12" r="10"/><circle cx="9" cy="13" r="1"/><circle cx="15" cy="13" r="1"/><path d="M9 16c1 1 2 1 3 1s2 0 3-1"/><circle cx="18" cy="8" r="1.5"/><path d="M12 7l-4 5h8z"/>',
        'reddit-sub'       => '<circle cx="12" cy="12" r="10"/><circle cx="9" cy="13" r="1"/><circle cx="15" cy="13" r="1"/><path d="M9 16c1 1 2 1 3 1s2 0 3-1"/><circle cx="18" cy="8" r="1.5"/><path d="M19 14v6M22 17h-6"/>',
        // Quora
        'quora-upvote'     => '<circle cx="12" cy="12" r="10"/><path d="M9 16c1 1 2 1 3 1s2 0 3-1"/><path d="M12 7l-4 5h8z"/><circle cx="18" cy="8" r="1.5"/>',
        // Medium
        'medium-clap'      => '<circle cx="12" cy="12" r="10"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/><path d="M8 8c3-1 6.5-1 9 1"/>',
        // VK
        'vk-follow'        => '<circle cx="12" cy="12" r="10"/><path d="M7 9c2 4 5 6 9 6M7 15c2-2 5-2 9-2"/><path d="M19 14v6M22 17h-6"/>',
        'vk-like'          => '<circle cx="12" cy="12" r="10"/><path d="M7 9c2 4 5 6 9 6M7 15c2-2 5-2 9-2"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/>',
        // Generic extra icons used by seeder
        'social'           => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
        'social-media'     => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
        'edit'             => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>',
        'palette'          => '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.9 0 1.6-.7 1.6-1.7 0-.4-.2-.8-.4-1.1-.3-.3-.4-.7-.4-1.1a1.6 1.6 0 0 1 1.7-1.7h2c3 0 5.5-2.5 5.5-5.6C22 6 17.5 2 12 2z"/>',
        'code'             => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        'trending'         => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'clipboard'        => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/>',
        'phone'            => '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/>',
        'photo'            => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
        'image'            => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
        'bug'              => '<rect x="8" y="6" width="8" height="13" rx="4"/><path d="M8 10H4M16 10h4M8 14H3M16 14h5M8 18H5M16 18h3M9 6a3 3 0 0 1 6 0"/>',
        'table'            => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/>',
        'link'             => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
        'folder'           => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
        'mail'             => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/>',
        'search'           => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
        'chat'             => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'user'             => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'mic'              => '<rect x="9" y="2" width="6" height="11" rx="3"/><path d="M19 10v1a7 7 0 0 1-14 0v-1M12 18v4M8 22h8"/>',

        'default'       => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    ];

    /**
     * Render an inline SVG for the given icon key.
     *
     * @param  object|string|null  $icon
     * @return string
     */
    public function render($icon): string
    {
        $key = $icon;
        if (is_object($key) && isset($key->icon)) {
            $key = $key->icon;
        }
        $key = is_string($key) ? strtolower(trim($key)) : 'briefcase';

        $path = $this->paths[$key] ?? ($this->paths['briefcase'] ?? $this->paths['default']);

        return '<svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">'
            . $path
            . '</svg>';
    }
}
