<?php

namespace Database\Seeders;

use App\Models\TaskCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $parents = [
            ['Social Media', 'social-media', 0.50, 1, 'social', '#1877F2'],
            ['Content & Writing', 'content-writing', 0.80, 1, 'edit', '#2563EB'],
            ['Design & Creative', 'design-creative', 1.00, 1, 'palette', '#7C3AED'],
            ['Web & Tech', 'web-tech', 1.50, 1, 'code', '#0EA5E9'],
            ['Marketing & SEO', 'marketing-seo', 1.20, 1, 'trending', '#059669'],
            ['Surveys & Reviews', 'surveys-reviews', 0.40, 1, 'clipboard', '#D97706'],
            ['App Install & Test', 'app-install-test', 0.60, 1, 'phone', '#DB2777'],
            ['Video & Audio', 'video-audio', 1.10, 1, 'video', '#DC2626'],
        ];

        $position = 0;
        foreach ($parents as $p) {
            [$name, $slug, $price, $minAmount, $icon, $color] = $p;
            $parent = TaskCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'      => $name,
                    'price'     => $price,
                    'min_amount'=> $minAmount,
                    'active'    => true,
                    'position'  => $position++,
                    'parent_id' => null,
                    'icon'      => $icon,
                    'color'     => $color,
                ]
            );

            // Subcategories — exact real social-media interaction icons.
            // Each sub: [name, icon, color, basePrice, minAmount]
            $subs = match ($slug) {
                'social-media' => [
                    ['Facebook Page Likes',        'facebook-like',  '#1877F2', 0.02, 1],
                    ['Facebook Post Reactions',    'facebook-react', '#1877F2', 0.02, 1],
                    ['Facebook Shares',            'facebook-share', '#1877F2', 0.03, 1],
                    ['Facebook Followers',         'facebook-follow','#1877F2', 0.04, 1],
                    ['Facebook Group Joins',       'facebook-group', '#1877F2', 0.05, 1],
                    ['Facebook Video Views',       'facebook-view',  '#1877F2', 0.01, 100],
                    ['Facebook 5-Star Reviews',    'facebook-star',  '#1877F2', 0.08, 1],
                    ['Instagram Followers',        'instagram-follow','#E1306C', 0.04, 1],
                    ['Instagram Likes',            'instagram-like', '#E1306C', 0.02, 1],
                    ['Instagram Comments',         'instagram-comment','#E1306C',0.05, 1],
                    ['Instagram Story Views',      'instagram-story','#E1306C', 0.02, 50],
                    ['Instagram Reels Views',      'instagram-reel', '#E1306C', 0.01, 100],
                    ['Instagram Saves',            'instagram-save', '#E1306C', 0.03, 1],
                    ['YouTube Subscribers',        'youtube-sub',    '#FF0000', 0.06, 1],
                    ['YouTube Likes',              'youtube-like',   '#FF0000', 0.02, 1],
                    ['YouTube Video Views',        'youtube-view',   '#FF0000', 0.01, 100],
                    ['YouTube Comments',           'youtube-comment','#FF0000', 0.05, 1],
                    ['YouTube Watch Hours',        'youtube-watch',  '#FF0000', 0.10, 1],
                    ['Twitter / X Followers',      'twitter-follow', '#1DA1F2', 0.04, 1],
                    ['Twitter / X Retweets',       'twitter-retweet','#1DA1F2', 0.03, 1],
                    ['Twitter / X Likes',          'twitter-like',   '#1DA1F2', 0.02, 1],
                    ['Twitter / X Comments',       'twitter-comment','#1DA1F2', 0.05, 1],
                    ['TikTok Followers',           'tiktok-follow',  '#000000', 0.04, 1],
                    ['TikTok Likes',               'tiktok-like',    '#000000', 0.02, 1],
                    ['TikTok Views',               'tiktok-view',    '#000000', 0.01, 100],
                    ['TikTok Comments',            'tiktok-comment', '#000000', 0.05, 1],
                    ['TikTok Shares',              'tiktok-share',   '#000000', 0.03, 1],
                    ['LinkedIn Connections',       'linkedin-connect','#0A66C2', 0.05, 1],
                    ['LinkedIn Page Followers',    'linkedin-follow','#0A66C2', 0.04, 1],
                    ['LinkedIn Post Likes',        'linkedin-like',  '#0A66C2', 0.02, 1],
                    ['Telegram Channel Joins',     'telegram-join',  '#0088CC', 0.04, 1],
                    ['Telegram Post Views',        'telegram-view',  '#0088CC', 0.01, 100],
                    ['WhatsApp Group Joins',       'whatsapp-join',  '#25D366', 0.05, 1],
                    ['Pinterest Followers',        'pinterest-follow','#E60023', 0.04, 1],
                    ['Pinterest Repins',           'pinterest-pin',  '#E60023', 0.03, 1],
                    ['Spotify Plays',              'spotify-play',   '#1DB954', 0.01, 100],
                    ['Spotify Followers',          'spotify-follow', '#1DB954', 0.04, 1],
                    ['SoundCloud Plays',           'soundcloud-play','#FF5500', 0.01, 100],
                    ['SoundCloud Followers',       'soundcloud-follow','#FF5500',0.04, 1],
                    ['Twitch Followers',           'twitch-follow',  '#9146FF', 0.04, 1],
                    ['Twitch Channel Views',       'twitch-view',    '#9146FF', 0.01, 100],
                    ['Discord Server Joins',       'discord-join',   '#5865F2', 0.05, 1],
                    ['Reddit Upvotes',             'reddit-upvote',  '#FF4500', 0.03, 1],
                    ['Reddit Subscribers',         'reddit-sub',     '#FF4500', 0.04, 1],
                    ['Quora Upvotes',              'quora-upvote',   '#B92B27', 0.03, 1],
                    ['Medium Claps',               'medium-clap',    '#00AB6C', 0.02, 1],
                    ['VK Followers',               'vk-follow',      '#0077FF', 0.04, 1],
                    ['VK Likes',                   'vk-like',        '#0077FF', 0.02, 1],
                    // ── Additional world social-media platforms ──
                    ['Snapchat Followers',         'snapchat-follow','#FFFC00', 0.05, 1],
                    ['Snapchat Story Views',       'snapchat-view',  '#FFFC00', 0.02, 50],
                    ['X (Twitter) Posts Engagement','x-like',        '#000000', 0.02, 1],
                    ['X (Twitter) Followers',      'x-follow',       '#000000', 0.04, 1],
                    ['X (Twitter) Reposts',        'x-retweet',      '#000000', 0.03, 1],
                    ['Threads Followers',          'threads-follow', '#000000', 0.04, 1],
                    ['Threads Likes',              'threads-like',   '#000000', 0.02, 1],
                    ['Tumblr Followers',           'tumblr-follow',  '#36465D', 0.04, 1],
                    ['Tumblr Reblogs',             'tumblr-reblog',  '#36465D', 0.03, 1],
                    ['Vimeo Followers',            'vimeo-follow',   '#1AB7EA', 0.05, 1],
                    ['Vimeo Video Views',          'vimeo-view',     '#1AB7EA', 0.01, 100],
                    ['Dailymotion Followers',      'dailymotion-follow','#0066DC',0.05, 1],
                    ['Dailymotion Video Views',    'dailymotion-view','#0066DC', 0.01, 100],
                    ['Mixcloud Followers',         'mixcloud-follow','#5000FF', 0.05, 1],
                    ['Mixcloud Plays',             'mixcloud-play',  '#5000FF', 0.01, 100],
                    ['Patreon Supporters',         'patreon-pledge', '#FF424D', 0.20, 1],
                    ['Patreon Followers',          'patreon-follow', '#FF424D', 0.05, 1],
                    ['Kick Channel Followers',     'kick-follow',    '#53FC18', 0.05, 1],
                    ['Kick Stream Views',          'kick-view',      '#53FC18', 0.01, 100],
                    ['Rumble Channel Followers',   'rumble-follow',  '#85C742', 0.05, 1],
                    ['Rumble Video Views',         'rumble-view',    '#85C742', 0.01, 100],
                    ['Clubhouse Followers',        'clubhouse-follow','#651B93', 0.06, 1],
                    ['Signal Group Joins',         'signal-follow',  '#3A76F0', 0.05, 1],
                    ['Viber Group Joins',          'viber-follow',   '#7360F2', 0.05, 1],
                    ['Viber Messages',             'viber-message',  '#7360F2', 0.03, 1],
                    ['LINE Friends',               'line-follow',    '#06C755', 0.05, 1],
                    ['LINE Messages',              'line-message',   '#06C755', 0.03, 1],
                    ['Skype Contacts',             'skype-follow',   '#00AFF0', 0.06, 1],
                    ['Truth Social Followers',     'truth-follow',   '#D4467A', 0.05, 1],
                    ['Truth Social Likes',         'truth-like',     '#D4467A', 0.02, 1],
                    ['Mastodon Followers',         'mastodon-follow','#6364FF', 0.05, 1],
                    ['Mastodon Boosts',            'mastodon-boost', '#6364FF', 0.03, 1],
                    ['Weibo Followers',            'weibo-follow',   '#E6162D', 0.05, 1],
                    ['Weibo Reposts',              'weibo-repost',   '#E6162D', 0.03, 1],
                    ['WeChat Group Joins',         'wechat-follow',  '#07C160', 0.06, 1],
                    ['Likee Followers',            'likee-follow',   '#FFCC00', 0.05, 1],
                    ['Likee Video Views',          'likee-view',     '#FFCC00', 0.01, 100],
                    ['ShareChat Followers',        'sharechat-follow','#F76808', 0.05, 1],
                    ['Kuaishou Followers',         'kuaishou-follow','#FF8000', 0.05, 1],
                    ['Kuaishou Video Views',       'kuaishou-view',  '#FF8000', 0.01, 100],
                    ['OnlyFans Subscribers',       'onlyfans-subscribe','#00AFF0',0.30, 1],
                    ['OnlyFans Followers',         'onlyfans-follow','#00AFF0', 0.06, 1],
                    ['Trovo Channel Followers',    'trovo-follow',   '#39E7B6', 0.05, 1],
                    ['Trovo Stream Views',         'trovo-view',     '#39E7B6', 0.01, 100],
                    ['Xing Contacts',              'xing-follow',    '#026466', 0.06, 1],
                    ['Meetup Group Joins',         'meetup-follow',  '#F65858', 0.07, 1],
                    ['Goodreads Followers',        'goodreads-follow','#553B0D', 0.05, 1],
                    ['Goodreads Book Reviews',     'goodreads-review','#553B0D', 0.10, 1],
                    ['Untappd Followers',          'untappd-follow', '#FFC000', 0.05, 1],
                    ['Substack Subscribers',       'substack-subscribe','#FF6719',0.15, 1],
                    ['Substack Followers',         'substack-follow','#FF6719', 0.05, 1],
                    ['Behance Project Views',      'behance-follow', '#1769FF', 0.02, 100],
                    ['Behance Appreciations',      'behance-like',   '#1769FF', 0.03, 1],
                    ['Dribbble Followers',         'dribbble-follow','#EA4C89', 0.05, 1],
                    ['Dribbble Likes',             'dribbble-like',  '#EA4C89', 0.02, 1],
                    ['Flickr Followers',           'flickr-follow',  '#0063DC', 0.05, 1],
                    ['GitHub Followers',           'github-follow',  '#181717', 0.05, 1],
                    ['GitHub Stars',               'github-star',    '#181717', 0.04, 1],
                    ['Quora Followers',            'quora-follow',   '#B92B27', 0.04, 1],
                    ['Medium Followers',           'medium-follow',  '#00AB6C', 0.04, 1],
                    ['Google Reviews',             'google-review',  '#4285F4', 0.12, 1],
                    ['Trustpilot Reviews',         'trustpilot-review','#00B67A',0.12, 1],
                    ['Google Play App Installs',   'google-play-install','#414141',0.30, 1],
                    ['Apple App Store Installs',   'appstore-install','#0D96F6',0.40, 1],
                    ['Website Traffic / Visits',   'website-visit',  '#2563EB', 0.01, 100],
                    ['Website Sign-ups',           'signup',         '#2563EB', 0.20, 1],
                ],
                'content-writing' => [
                    ['Blog Comments',      'comment', '#2563EB', 0.05, 1],
                    ['Article Writing',    'edit',    '#2563EB', 0.80, 1],
                    ['Product Reviews',    'star',    '#2563EB', 0.08, 1],
                    ['Forum Posts',        'chat',    '#2563EB', 0.06, 1],
                    ['Guest Posts',        'edit',    '#2563EB', 1.00, 1],
                ],
                'design-creative' => [
                    ['Logo Design',       'palette',  '#7C3AED', 2.00, 1],
                    ['Banner Design',     'image',    '#7C3AED', 1.00, 1],
                    ['Photo Editing',     'photo',    '#7C3AED', 0.50, 1],
                    ['Illustration',      'palette',  '#7C3AED', 1.50, 1],
                    ['Thumbnail Design',  'image',    '#7C3AED', 0.80, 1],
                ],
                'web-tech' => [
                    ['Website Testing',   'code',  '#0EA5E9', 0.60, 1],
                    ['Bug Reporting',     'bug',   '#0EA5E9', 0.50, 1],
                    ['Code Review',       'code',  '#0EA5E9', 1.00, 1],
                    ['Data Entry',        'table', '#0EA5E9', 0.30, 1],
                    ['API Testing',       'code',  '#0EA5E9', 0.80, 1],
                ],
                'marketing-seo' => [
                    ['Backlinks',            'link',     '#059669', 0.40, 1],
                    ['Directory Submission', 'folder',   '#059669', 0.30, 1],
                    ['Keyword Research',     'search',   '#059669', 1.00, 1],
                    ['Email Marketing',      'mail',     '#059669', 0.50, 1],
                    ['Social Shares',        'share',    '#059669', 0.20, 1],
                ],
                'surveys-reviews' => [
                    ['Survey Completion',  'clipboard', '#D97706', 0.40, 1],
                    ['Product Feedback',   'star',      '#D97706', 0.30, 1],
                    ['App Reviews',        'star',      '#D97706', 0.50, 1],
                    ['Service Reviews',    'star',      '#D97706', 0.40, 1],
                ],
                'app-install-test' => [
                    ['Android App Install', 'phone', '#DB2777', 0.30, 1],
                    ['iOS App Install',     'phone', '#DB2777', 0.40, 1],
                    ['App Testing',         'phone', '#DB2777', 0.60, 1],
                    ['Sign-up Tasks',       'user',  '#DB2777', 0.50, 1],
                    ['App Reviews',         'star',  '#DB2777', 0.50, 1],
                ],
                'video-audio' => [
                    ['Video Testimonials', 'video', '#DC2626', 2.00, 1],
                    ['Voice Over',         'mic',   '#DC2626', 1.50, 1],
                    ['Video Editing',      'video', '#DC2626', 1.00, 1],
                    ['Subtitling',         'video', '#DC2626', 0.80, 1],
                    ['Video Views',        'view',  '#DC2626', 0.01, 100],
                ],
                default => [],
            };

            $subPos = 0;
            foreach ($subs as $sub) {
                [$subName, $subIcon, $subColor, $subPrice, $subMin] = $sub;
                TaskCategory::updateOrCreate(
                    ['slug' => Str::slug($subName . '-' . $slug)],
                    [
                        'parent_id' => $parent->id,
                        'name'      => $subName,
                        'price'     => $subPrice,
                        'min_amount'=> $subMin,
                        'active'    => true,
                        'position'  => $subPos++,
                        'icon'      => $subIcon,
                        'color'     => $subColor,
                    ]
                );
            }
        }
    }
}
