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
            ['Social Media', 'social-media', 0.50, 1],
            ['Content & Writing', 'content-writing', 0.80, 1],
            ['Design & Creative', 'design-creative', 1.00, 1],
            ['Web & Tech', 'web-tech', 1.50, 1],
            ['Marketing & SEO', 'marketing-seo', 1.20, 1],
            ['Surveys & Reviews', 'surveys-reviews', 0.40, 1],
            ['App Install & Test', 'app-install-test', 0.60, 1],
            ['Video & Audio', 'video-audio', 1.10, 1],
        ];

        $position = 0;
        foreach ($parents as $p) {
            $parent = TaskCategory::updateOrCreate(
                ['slug' => $p[1]],
                [
                    'name'      => $p[0],
                    'price'     => $p[2],
                    'min_amount'=> $p[3],
                    'active'    => true,
                    'position'  => $position++,
                    'parent_id' => null,
                ]
            );

            // Add a few subcategories per parent.
            $subs = match ($p[1]) {
                'social-media' => ['Facebook Likes', 'Twitter/X Retweets', 'Instagram Followers', 'YouTube Subscribers', 'TikTok Engagement'],
                'content-writing' => ['Blog Comments', 'Article Writing', 'Product Reviews', 'Forum Posts'],
                'design-creative' => ['Logo Design', 'Banner Design', 'Photo Editing', 'Illustration'],
                'web-tech' => ['Website Testing', 'Bug Reporting', 'Code Review', 'Data Entry'],
                'marketing-seo' => ['Backlinks', 'Directory Submission', 'Keyword Research', 'Email Marketing'],
                'surveys-reviews' => ['Survey Completion', 'Product Feedback', 'App Reviews', 'Service Reviews'],
                'app-install-test' => ['Android App Install', 'iOS App Install', 'App Testing', 'Sign-up Tasks'],
                'video-audio' => ['Video Testimonials', 'Voice Over', 'Video Editing', 'Subtitling'],
                default => [],
            };

            $subPos = 0;
            foreach ($subs as $sub) {
                TaskCategory::updateOrCreate(
                    ['slug' => Str::slug($sub.'-'.$p[1])],
                    [
                        'parent_id' => $parent->id,
                        'name'      => $sub,
                        'price'     => $p[2],
                        'min_amount'=> 1,
                        'active'    => true,
                        'position'  => $subPos++,
                    ]
                );
            }
        }
    }
}
