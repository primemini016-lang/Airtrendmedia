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
        'social_platform' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 0 20M7 5.5c1.4 1 3.2 1.5 5 1.5s3.6-.5 5-1.5M7 18.5c1.4-1 3.2-1.5 5-1.5s3.6.5 5 1.5"/>',
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

        // ── Additional world social-media platforms ──
        // Snapchat
        'snapchat-follow'  => '<path d="M12 2c3 0 5 2 5 5 0 2-1 4-1 6 1 1 2 1 3 1-1 1-2 2-4 2 0 1 1 3 2 4-2 0-4-1-5-3-1 2-3 3-5 3 1-1 2-3 2-4-2 0-3-1-4-2 1 0 2 0 3-1 0-2-1-4-1-6 0-3 2-5 5-5z"/><path d="M19 14v6M22 17h-6"/>',
        'snapchat-view'    => '<path d="M12 2c3 0 5 2 5 5 0 2-1 4-1 6 1 1 2 1 3 1-1 1-2 2-4 2 0 1 1 3 2 4-2 0-4-1-5-3-1 2-3 3-5 3 1-1 2-3 2-4-2 0-3-1-4-2 1 0 2 0 3-1 0-2-1-4-1-6 0-3 2-5 5-5z"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        // X (Twitter rebrand)
        'x-follow'         => '<path d="M18.9 2H22l-7.3 8.3L23 22h-6.8l-5.3-7-6.1 7H2l7.8-9L1.5 2h7l4.8 6.4L18.9 2z"/><path d="M19 14v6M22 17h-6"/>',
        'x-like'           => '<path d="M18.9 2H22l-7.3 8.3L23 22h-6.8l-5.3-7-6.1 7H2l7.8-9L1.5 2h7l4.8 6.4L18.9 2z"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/>',
        'x-retweet'        => '<path d="M18.9 2H22l-7.3 8.3L23 22h-6.8l-5.3-7-6.1 7H2l7.8-9L1.5 2h7l4.8 6.4L18.9 2z"/><path d="M17 1l4 4-4 4M3 11V9a4 4 0 0 1 4-4h14M7 23l-4-4 4-4M21 13v2a4 4 0 0 1-4 4H3"/>',
        // Threads
        'threads-follow'   => '<path d="M16.5 11c-.1-2.4-1.3-3.8-3.5-3.8-2.4 0-3.7 1.6-3.7 3.2 0 .8.3 1.5.9 2M8.5 6.5C9.6 5.3 11.3 4.7 13 4.7c3.5 0 5.7 2.2 5.9 5.6M5.5 12.5c0 1.2 0 2.4.1 3.4"/><path d="M19 20v-2M22 21h-6"/>',
        'threads-like'     => '<path d="M16.5 11c-.1-2.4-1.3-3.8-3.5-3.8-2.4 0-3.7 1.6-3.7 3.2 0 .8.3 1.5.9 2M8.5 6.5C9.6 5.3 11.3 4.7 13 4.7c3.5 0 5.7 2.2 5.9 5.6M5.5 12.5c0 1.2 0 2.4.1 3.4"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/>',
        // Tumblr
        'tumblr-follow'    => '<path d="M9 3v6h4v3H9v6c0 1 .5 2 2 2h2v3h-3c-3 0-4-2-4-4v-7H4v-3h2V3z"/><path d="M19 14v6M22 17h-6"/>',
        'tumblr-reblog'    => '<path d="M9 3v6h4v3H9v6c0 1 .5 2 2 2h2v3h-3c-3 0-4-2-4-4v-7H4v-3h2V3z"/><path d="M17 1l4 4-4 4M3 11V9a4 4 0 0 1 4-4h14"/>',
        // Vimeo
        'vimeo-follow'     => '<path d="M22 7c-.3 3-2.5 7-6.5 12-4.3 5.3-7.8 8-10.5 8-1.7 0-3.1-2.3-4.2-6.8L1 11C.2 6.5-.7 4.2-1.7 4.2"/><path d="M2 8c1-1.5 2.5-3 4-3 1 0 2 1.5 2.5 4l2 7c.5 1.8 1.2 2.7 2 2.7"/><path d="M19 20v-2M22 21h-6"/>',
        'vimeo-view'       => '<path d="M22 7c-.3 3-2.5 7-6.5 12-4.3 5.3-7.8 8-10.5 8-1.7 0-3.1-2.3-4.2-6.8L1 11C.2 6.5-.7 4.2-1.7 4.2"/><path d="M2 8c1-1.5 2.5-3 4-3 1 0 2 1.5 2.5 4l2 7c.5 1.8 1.2 2.7 2 2.7"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>',
        // Dailymotion
        'dailymotion-follow' => '<circle cx="12" cy="12" r="10"/><path d="M14 7v10"/><circle cx="14" cy="7" r="1.5"/><path d="M19 20v-2M22 21h-6"/>',
        'dailymotion-view' => '<circle cx="12" cy="12" r="10"/><path d="M14 7v10"/><circle cx="14" cy="7" r="1.5"/><path d="M10 11c0-1.5 1-2.5 2.5-2.5S15 9.5 15 11s-1 2.5-2.5 2.5S10 12.5 10 11z"/>',
        // Mixcloud
        'mixcloud-follow'  => '<path d="M2 14v4M5 12v6M8 10v8M11 8v10"/><rect x="13" y="6" width="9" height="12" rx="1"/><path d="M19 20v-2M22 21h-6"/>',
        'mixcloud-play'    => '<path d="M2 14v4M5 12v6M8 10v8M11 8v10"/><rect x="13" y="6" width="9" height="12" rx="1"/><polygon points="9 14 14 12 9 10 9 14"/>',
        // Patreon
        'patreon-follow'   => '<circle cx="14" cy="12" r="8"/><rect x="3" y="3" width="4" height="18"/><path d="M19 20v-2M22 21h-6"/>',
        'patreon-pledge'   => '<circle cx="14" cy="12" r="8"/><rect x="3" y="3" width="4" height="18"/><path d="M12 7l1.5 4.5L18 13l-4.5 1.5L12 19l-1.5-4.5L6 13l4.5-1.5z"/>',
        // Kick (streaming)
        'kick-follow'      => '<path d="M3 3h6v6h3V6h3v3h3v6h-3v3h-3v-3H9v6H3z"/><path d="M19 20v-2M22 21h-6"/>',
        'kick-view'        => '<path d="M3 3h6v6h3V6h3v3h3v6h-3v3h-3v-3H9v6H3z"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>',
        // Rumble
        'rumble-follow'    => '<circle cx="12" cy="12" r="9"/><polygon points="10 8 16 12 10 16 10 8"/><path d="M19 20v-2M22 21h-6"/>',
        'rumble-view'      => '<circle cx="12" cy="12" r="9"/><polygon points="10 8 16 12 10 16 10 8"/>',
        // Clubhouse
        'clubhouse-follow' => '<circle cx="12" cy="12" r="9"/><circle cx="9" cy="10" r="1.5"/><circle cx="15" cy="10" r="1.5"/><circle cx="9" cy="15" r="1.5"/><circle cx="15" cy="15" r="1.5"/><path d="M19 20v-2M22 21h-6"/>',
        // Signal
        'signal-follow'    => '<circle cx="12" cy="12" r="9"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2 2M16.4 16.4l2 2M18.4 5.6l-2 2M7.6 16.4l-2 2"/><path d="M19 20v-2M22 21h-6"/>',
        // Viber
        'viber-follow'     => '<path d="M5 4h6l3 4-3 3v6H5z"/><path d="M19 20v-2M22 21h-6"/>',
        'viber-message'    => '<path d="M5 4h6l3 4-3 3v6H5z"/><path d="M8 11.5l2 2 4-4"/>',
        // Line
        'line-follow'      => '<rect x="3" y="6" width="18" height="12" rx="3"/><path d="M7 12h4M9 10v4"/><circle cx="15" cy="11" r="1"/><circle cx="17.5" cy="13" r="1"/><path d="M19 20v-2M22 21h-6"/>',
        'line-message'     => '<rect x="3" y="6" width="18" height="12" rx="3"/><path d="M7 12h4M9 10v4"/><circle cx="15" cy="11" r="1"/><circle cx="17.5" cy="13" r="1"/>',
        // Skype
        'skype-follow'     => '<circle cx="12" cy="12" r="9"/><path d="M8 9c0-1.5 1.5-2.5 3.5-2.5S15 7.5 15 9c0 2.5-5 1.5-5 4 0 1 1 1.5 2.5 1.5S15 14 15 15"/><path d="M19 20v-2M22 21h-6"/>',
        // Truth Social
        'truth-follow'     => '<circle cx="12" cy="12" r="9"/><path d="M8 8h8M8 12h8M8 16h5"/><path d="M19 20v-2M22 21h-6"/>',
        'truth-like'       => '<circle cx="12" cy="12" r="9"/><path d="M8 8h8M8 12h8M8 16h5"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/>',
        // Mastodon
        'mastodon-follow'  => '<path d="M20 8c0-4-3-6-8-6S4 4 4 8c0 5 2 9 8 9 1.5 0 2.8-.3 3.8-.8"/><path d="M4 11c0 3 1 5 4 6M20 11c0 3-1 5-4 6"/><path d="M8 8v6M12 8v6M16 8v6"/><path d="M19 20v-2M22 21h-6"/>',
        'mastodon-boost'   => '<path d="M20 8c0-4-3-6-8-6S4 4 4 8c0 5 2 9 8 9"/><path d="M4 11c0 3 1 5 4 6M20 11c0 3-1 5-4 6"/><path d="M8 8v6M12 8v6M16 8v6"/><path d="M17 1l4 4-4 4M3 11V9a4 4 0 0 1 4-4h14"/>',
        // Weibo
        'weibo-follow'     => '<circle cx="10" cy="14" r="6"/><path d="M16 6c3 0 5 2 5 5"/><path d="M19 20v-2M22 21h-6"/>',
        'weibo-repost'     => '<circle cx="10" cy="14" r="6"/><path d="M16 6c3 0 5 2 5 5"/><path d="M17 1l4 4-4 4M3 11V9a4 4 0 0 1 4-4h14"/>',
        // WeChat
        'wechat-follow'    => '<path d="M9 4C5 4 2 6.5 2 10c0 2 1 3.8 2.5 5L4 17l3-1.5c.7.2 1.3.3 2 .3"/><path d="M15 9c3.5 0 6.5 2.5 6.5 5.5 0 1.5-.7 2.8-1.8 3.8L20 21l-2.3-1.2c-.7.2-1.4.3-2.2.3-3.5 0-6.5-2.5-6.5-5.5"/><path d="M19 20v-2M22 21h-6"/>',
        // Likee
        'likee-follow'     => '<path d="M3 12c0-5 4-9 9-9s9 4 9 9-4 9-9 9"/><path d="M12 7v10M8 10l4-3 4 3"/><path d="M19 20v-2M22 21h-6"/>',
        'likee-view'       => '<path d="M3 12c0-5 4-9 9-9s9 4 9 9-4 9-9 9"/><path d="M12 7v10M8 10l4-3 4 3"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>',
        // ShareChat
        'sharechat-follow' => '<circle cx="12" cy="12" r="9"/><path d="M8 8h8v8H8z"/><circle cx="12" cy="12" r="2"/><path d="M19 20v-2M22 21h-6"/>',
        // Kuaishou
        'kuaishou-follow'  => '<path d="M5 4l4 6-4 10M11 4l4 6-4 10M17 4v16"/><path d="M19 20v-2M22 21h-6"/>',
        'kuaishou-view'    => '<path d="M5 4l4 6-4 10M11 4l4 6-4 10M17 4v16"/><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>',
        // OnlyFans
        'onlyfans-follow'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v10M7 12h10"/><path d="M19 20v-2M22 21h-6"/>',
        'onlyfans-subscribe' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v10M7 12h10"/><path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
        // Trovo
        'trovo-follow'     => '<rect x="4" y="3" width="16" height="18" rx="3"/><path d="M9 9v6l5-3z"/><path d="M19 20v-2M22 21h-6"/>',
        'trovo-view'       => '<rect x="4" y="3" width="16" height="18" rx="3"/><path d="M9 9v6l5-3z"/>',
        // Xing
        'xing-follow'      => '<path d="M5 4l4 7-5 9h4l5-9-4-7z"/><path d="M13 4l5 8-5 8"/><path d="M19 20v-2M22 21h-6"/>',
        // Meetup
        'meetup-follow'    => '<circle cx="8" cy="10" r="3"/><circle cx="15" cy="13" r="4"/><circle cx="6" cy="16" r="2"/><path d="M19 20v-2M22 21h-6"/>',
        // Goodreads
        'goodreads-follow' => '<path d="M6 3h12v18H6z"/><path d="M9 7h6M9 11h6M9 15h4"/><path d="M19 20v-2M22 21h-6"/>',
        'goodreads-review' => '<path d="M6 3h12v18H6z"/><path d="M9 7h6M9 11h6M9 15h4"/><polygon points="12 10 13 12.5 15.5 12.5 13.5 14 14.5 16.5 12 15 9.5 16.5 10.5 14 8.5 12.5 11 12.5 12 10"/>',
        // Untappd
        'untappd-follow'   => '<path d="M8 3v6l-3 8h6l-3-8V3M16 3v6l-3 8h6l-3-8V3"/><path d="M19 20v-2M22 21h-6"/>',
        // Substack
        'substack-follow'  => '<rect x="3" y="3" width="18" height="4"/><rect x="3" y="9" width="18" height="3"/><path d="M3 14h18v7H3z"/><path d="M19 20v-2M22 21h-6"/>',
        'substack-subscribe' => '<rect x="3" y="3" width="18" height="4"/><rect x="3" y="9" width="18" height="3"/><path d="M3 14h18v7H3z"/><path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
        // Behance
        'behance-follow'   => '<path d="M3 6h6c2 0 3 1 3 3s-1 3-3 3c2 0 3 1 3 3s-1 3-3 3H3z"/><path d="M14 7h6"/><path d="M19 20v-2M22 21h-6"/>',
        'behance-like'     => '<path d="M3 6h6c2 0 3 1 3 3s-1 3-3 3c2 0 3 1 3 3s-1 3-3 3H3z"/><path d="M14 7h6"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/>',
        // Dribbble
        'dribbble-follow'  => '<circle cx="12" cy="12" r="10"/><path d="M5 5c5 5 12 7 17 6M2 12c6-1 12 0 17 5M8 2c4 5 6 12 5 20"/><path d="M19 20v-2M22 21h-6"/>',
        'dribbble-like'    => '<circle cx="12" cy="12" r="10"/><path d="M5 5c5 5 12 7 17 6M2 12c6-1 12 0 17 5M8 2c4 5 6 12 5 20"/><path d="M7 22V11M2 11v11M2 11h5l4-7a2 2 0 0 1 4 .5V11"/>',
        // Flickr
        'flickr-follow'    => '<circle cx="7" cy="12" r="4" fill="currentColor" stroke="none"/><circle cx="17" cy="12" r="4"/><path d="M19 20v-2M22 21h-6"/>',
        // GitHub
        'github-follow'    => '<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.2 4.2 0 0 0-.1-3.2s-1.1-.3-3.5 1.3a12 12 0 0 0-6.2 0C6.5 2.8 5.4 3.1 5.4 3.1a4.2 4.2 0 0 0-.1 3.2A4.6 4.6 0 0 0 4 9.5c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V21"/><path d="M19 20v-2M22 21h-6"/>',
        'github-star'      => '<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.2 4.2 0 0 0-.1-3.2s-1.1-.3-3.5 1.3a12 12 0 0 0-6.2 0C6.5 2.8 5.4 3.1 5.4 3.1a4.2 4.2 0 0 0-.1 3.2A4.6 4.6 0 0 0 4 9.5c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V21"/><polygon points="20 14 21 17 24 17 21.5 19 22.5 22 20 20 17.5 22 18.5 19 16 17 19 17 20 14"/>',
        // Google Reviews
        'google-review'    => '<path d="M12 2l3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/>',
        // Trustpilot
        'trustpilot-review'=> '<path d="M12 2l3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/><path d="M9 11l6 0"/>',
        // App Store / Play Store downloads
        'google-play-install' => '<path d="M3 3l9 9-9 9zM3 3l13 8M3 21l13-8"/>',
        'appstore-install' => '<path d="M12 3v12M7 8l5-5 5 5"/><rect x="3" y="14" width="18" height="7" rx="2"/>',
        // Quora followers (extra)
        'quora-follow'     => '<circle cx="12" cy="12" r="10"/><path d="M9 16c1 1 2 1 3 1s2 0 3-1"/><path d="M12 7l-4 5h8z"/><circle cx="18" cy="8" r="1.5"/><path d="M19 14v6M22 17h-6"/>',
        // Medium followers
        'medium-follow'    => '<path d="M2 6h6l4 9 4-9h6"/><path d="M2 18h6"/><path d="M19 20v-2M22 21h-6"/>',
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
     * Brand colors keyed by icon/platform name (hex, no #).
     * Used by render3D() to give each social-media icon its real brand color
     * with a 3D gradient + drop-shadow effect.
     */
    protected array $brandColors = [
        'facebook'      => '1877F2', 'facebook-like' => '1877F2', 'facebook-page' => '1877F2',
        'facebook-follow' => '1877F2', 'facebook-react' => '1877F2', 'facebook-share' => '1877F2',
        'facebook-group' => '1877F2', 'facebook-view' => '1877F2', 'facebook-star' => '1877F2',
        'twitter'       => '1DA1F2', 'twitter-retweet' => '1DA1F2', 'twitter-follow' => '1DA1F2',
        'twitter-like'  => '1DA1F2', 'twitter-comment' => '1DA1F2',
        'x-follow'      => '000000', 'x-like' => '000000', 'x-retweet' => '000000',
        'instagram'     => 'E4405F', 'instagram-followers' => 'E4405F', 'instagram-follow' => 'E4405F',
        'instagram-like' => 'E4405F', 'instagram-comment' => 'E4405F', 'instagram-story' => 'E4405F',
        'instagram-reel' => 'E4405F', 'instagram-save' => 'E4405F',
        'youtube'       => 'FF0000', 'youtube-subscribers' => 'FF0000', 'youtube-watch' => 'FF0000',
        'youtube-sub'   => 'FF0000', 'youtube-like' => 'FF0000', 'youtube-view' => 'FF0000', 'youtube-comment' => 'FF0000',
        'tiktok'        => '000000', 'tiktok-follow' => '000000', 'tiktok-like' => '000000',
        'tiktok-view'   => '000000', 'tiktok-comment' => '000000', 'tiktok-share' => '000000',
        'linkedin'      => '0A66C2', 'linkedin-connect' => '0A66C2', 'linkedin-follow' => '0A66C2', 'linkedin-like' => '0A66C2',
        'telegram'      => '26A5E4', 'telegram-join' => '26A5E4', 'telegram-view' => '26A5E4',
        'whatsapp'      => '25D366', 'whatsapp-message' => '25D366', 'whatsapp-join' => '25D366',
        'pinterest'     => 'E60023', 'pinterest-follow' => 'E60023', 'pinterest-pin' => 'E60023',
        'snapchat'      => 'FFFC00', 'snapchat-follow' => 'FFFC00', 'snapchat-view' => 'FFFC00',
        'reddit'        => 'FF4500', 'reddit-upvote' => 'FF4500', 'reddit-sub' => 'FF4500',
        'discord'       => '5865F2', 'discord-join' => '5865F2',
        'twitch'        => '9146FF', 'twitch-follow' => '9146FF', 'twitch-view' => '9146FF',
        'spotify'       => '1DB954', 'spotify-play' => '1DB954', 'spotify-follow' => '1DB954',
        'soundcloud'    => 'FF5500', 'soundcloud-play' => 'FF5500', 'soundcloud-follow' => 'FF5500',
        'social_platform' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 0 20M7 5.5c1.4 1 3.2 1.5 5 1.5s3.6-.5 5-1.5M7 18.5c1.4-1 3.2-1.5 5-1.5s3.6.5 5 1.5"/>',
        'website'       => '3B82F6', 'website-visit' => '3B82F6',
        'google'        => '4285F4', 'google-review' => '4285F4', 'google-play-install' => '00C300',
        'app-install'   => '0D96F6', 'app-review' => '0D96F6', 'appstore-install' => '0D96F6',
        'survey'        => '8B5CF6', 'review' => 'F59E0B', 'signup' => '10B981', 'subscribe' => 'EF4444',
        'comment'       => '6366F1', 'share' => '14B8A6', 'follow' => 'EC4899', 'like' => 'F43F5E',
        'view'          => '3B82F6', 'vote' => '8B5CF6', 'writing' => '6366F1', 'design' => 'A855F7',
        'marketing'     => 'F97316', 'music' => '9333EA', 'video' => 'EF4444', 'tech' => '06B6D4',
        'briefcase'     => '475569', 'star' => 'F59E0B', 'heart' => 'EF4444', 'globe' => '3B82F6',
        'quora'         => 'B92B27', 'quora-upvote' => 'B92B27', 'quora-follow' => 'B92B27',
        'medium'        => '000000', 'medium-clap' => '000000', 'medium-follow' => '000000',
        'vk'            => '0077FF', 'vk-follow' => '0077FF', 'vk-like' => '0077FF',
        'threads'       => '000000', 'threads-follow' => '000000', 'threads-like' => '000000',
        'tumblr'        => '36465D', 'tumblr-follow' => '36465D', 'tumblr-reblog' => '36465D',
        'vimeo'         => '1AB7EA', 'vimeo-follow' => '1AB7EA', 'vimeo-view' => '1AB7EA',
        'dailymotion'   => '0066DC', 'dailymotion-follow' => '0066DC', 'dailymotion-view' => '0066DC',
        'mixcloud'      => '5000FF', 'mixcloud-follow' => '5000FF', 'mixcloud-play' => '5000FF',
        'patreon'       => 'FF424D', 'patreon-follow' => 'FF424D', 'patreon-pledge' => 'FF424D',
        'kick'          => '53FC18', 'kick-follow' => '53FC18', 'kick-view' => '53FC18',
        'rumble'        => '85C742', 'rumble-follow' => '85C742', 'rumble-view' => '85C742',
        'clubhouse'     => '651FFF', 'clubhouse-follow' => '651FFF',
        'signal'        => '3A76F0', 'signal-follow' => '3A76F0',
        'viber'         => '7360F2', 'viber-follow' => '7360F2', 'viber-message' => '7360F2',
        'line'          => '06C755', 'line-follow' => '06C755', 'line-message' => '06C755',
        'skype'         => '00AFF0', 'skype-follow' => '00AFF0',
        'truth'         => 'E8112D', 'truth-follow' => 'E8112D', 'truth-like' => 'E8112D',
        'mastodon'      => '6364FF', 'mastodon-follow' => '6364FF', 'mastodon-boost' => '6364FF',
        'weibo'         => 'E6162D', 'weibo-follow' => 'E6162D', 'weibo-repost' => 'E6162D',
        'wechat'        => '07C160', 'wechat-follow' => '07C160',
        'likee'         => 'FFDF00', 'likee-follow' => 'FFDF00', 'likee-view' => 'FFDF00',
        'sharechat'     => 'FE5722', 'sharechat-follow' => 'FE5722',
        'kuaishou'      => 'FF4906', 'kuaishou-follow' => 'FF4906', 'kuaishou-view' => 'FF4906',
        'onlyfans'      => '00AFF0', 'onlyfans-follow' => '00AFF0', 'onlyfans-subscribe' => '00AFF0',
        'trovo'         => '21BCF4', 'trovo-follow' => '21BCF4', 'trovo-view' => '21BCF4',
        'xing'          => '006567', 'xing-follow' => '006567',
        'meetup'        => 'ED1C40', 'meetup-follow' => 'ED1C40',
        'goodreads'     => '372213', 'goodreads-follow' => '372213', 'goodreads-review' => '372213',
        'untappd'       => 'FFC000', 'untappd-follow' => 'FFC000',
        'substack'      => 'FF6719', 'substack-follow' => 'FF6719', 'substack-subscribe' => 'FF6719',
        'behance'       => '1769FF', 'behance-follow' => '1769FF', 'behance-like' => '1769FF',
        'dribbble'      => 'EA4C89', 'dribbble-follow' => 'EA4C89', 'dribbble-like' => 'EA4C89',
        'flickr'        => '0063DC', 'flickr-follow' => '0063DC',
        'github'        => '181717', 'github-follow' => '181717', 'github-star' => '181717',
        'trustpilot'    => '00B67A', 'trustpilot-review' => '00B67A',
        'social'        => '3B82F6', 'social-media' => '3B82F6',
        'edit'          => '64748B', 'palette' => 'A855F7', 'code' => '1E293B', 'trending' => '10B981',
        'clipboard'     => '6366F1', 'phone' => '06B6D4', 'photo' => '14B8A6', 'image' => '14B8A6',
        'bug'           => 'EF4444', 'table' => '64748B', 'link' => '3B82F6', 'folder' => 'F59E0B',
        'mail'          => 'EF4444', 'search' => '64748B', 'chat' => '6366F1', 'user' => '10B981',
        'mic'           => '9333EA',
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

    /**
     * Render a 3D, brand-colored circular badge icon.
     * Produces a glossy 3D sphere/badge with the platform's real brand color,
     * a radial-gradient highlight, inner shadow and drop shadow for depth.
     *
     * @param  object|string|null  $icon
     * @param  int                  $size   pixel size (default 48)
     * @return string
     */
    public function render3D($icon, int $size = 48): string
    {
        $key = $icon;
        if (is_object($key) && isset($key->icon)) {
            $key = $key->icon;
        }
        // Support passing a color override via the model (e.g. $cat->color).
        $overrideColor = null;
        if (is_object($icon) && isset($icon->color) && $icon->color) {
            $overrideColor = ltrim($icon->color, '#');
        }
        $key = is_string($key) ? strtolower(trim($key)) : 'briefcase';

        $path = $this->paths[$key] ?? ($this->paths['briefcase'] ?? $this->paths['default']);
        $color = $overrideColor ?: ($this->brandColors[$key] ?? '475569');

        // Lighten color for the gradient top highlight.
        $light = $this->lighten($color, 40);
        $dark  = $this->darken($color, 25);

        $uid = 'g3d' . substr(md5($key . $size . $color), 0, 8);

        $r = $size / 2;
        // Inner SVG icon sized ~55% of badge, centered.
        $iconSize = (int) round($size * 0.55);
        $iconOffset = ($size - $iconSize) / 2;

        $svg = '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '" xmlns="http://www.w3.org/2000/svg" style="display:inline-block;vertical-align:middle">'
            . '<defs>'
            . '<radialGradient id="' . $uid . '" cx="35%" cy="28%" r="80%">'
            . '<stop offset="0%" stop-color="#' . $light . '"/>'
            . '<stop offset="55%" stop-color="#' . $color . '"/>'
            . '<stop offset="100%" stop-color="#' . $dark . '"/>'
            . '</radialGradient>'
            . '<radialGradient id="' . $uid . 'hl" cx="40%" cy="22%" r="45%">'
            . '<stop offset="0%" stop-color="#ffffff" stop-opacity="0.75"/>'
            . '<stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>'
            . '</radialGradient>'
            . '<filter id="' . $uid . 'sh" x="-30%" y="-30%" width="160%" height="160%">'
            . '<feDropShadow dx="0" dy="' . round($size * 0.06) . '" stdDeviation="' . round($size * 0.05) . '" flood-color="#000000" flood-opacity="0.35"/>'
            . '</filter>'
            . '<clipPath id="' . $uid . 'clip"><circle cx="' . $r . '" cy="' . $r . '" r="' . $r . '"/></clipPath>'
            . '</defs>'
            // 3D badge sphere
            . '<g filter="url(#' . $uid . 'sh)">'
            . '<circle cx="' . $r . '" cy="' . $r . '" r="' . ($r - 1) . '" fill="url(#' . $uid . ')"/>'
            // glossy highlight
            . '<circle cx="' . $r . '" cy="' . $r . '" r="' . ($r - 1) . '" fill="url(#' . $uid . 'hl)"/>'
            // bottom inner shadow for depth
            . '<circle cx="' . $r . '" cy="' . ($r + $r * 0.5) . '" r="' . $r . '" fill="#000000" opacity="0.18" clip-path="url(#' . $uid . 'clip)"/>'
            . '<circle cx="' . $r . '" cy="' . $r . '" r="' . ($r - 1) . '" fill="none" stroke="#ffffff" stroke-opacity="0.25" stroke-width="1"/>'
            . '</g>'
            // the platform icon glyph in white, centered
            . '<g transform="translate(' . $iconOffset . ',' . $iconOffset . ') scale(' . ($iconSize / 24) . ')">'
            . '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">'
            . $path
            . '</svg>'
            . '</g>'
            . '</svg>';

        return $svg;
    }

    /**
     * Lighten a hex color by a percentage (0-100).
     */
    protected function lighten(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $r = (int) round($r + (255 - $r) * ($percent / 100));
        $g = (int) round($g + (255 - $g) * ($percent / 100));
        $b = (int) round($b + (255 - $b) * ($percent / 100));
        return str_pad(dechex(min($r, 255)), 2, '0', STR_PAD_LEFT)
             . str_pad(dechex(min($g, 255)), 2, '0', STR_PAD_LEFT)
             . str_pad(dechex(min($b, 255)), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Darken a hex color by a percentage (0-100).
     */
    protected function darken(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $r = (int) round($r * (1 - $percent / 100));
        $g = (int) round($g * (1 - $percent / 100));
        $b = (int) round($b * (1 - $percent / 100));
        return str_pad(dechex(max($r, 0)), 2, '0', STR_PAD_LEFT)
             . str_pad(dechex(max($g, 0)), 2, '0', STR_PAD_LEFT)
             . str_pad(dechex(max($b, 0)), 2, '0', STR_PAD_LEFT);
    }
}
