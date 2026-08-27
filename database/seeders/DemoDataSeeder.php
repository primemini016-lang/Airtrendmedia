<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogLike;
use App\Models\BlogPost;
use App\Models\BlogRate;
use App\Models\BlogView;
use App\Models\Comment;
use App\Models\Gig;
use App\Models\MarketplaceListing;
use App\Models\PtcAd;
use App\Models\Review;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Comprehensive demo data seeder for Airtrendmedia.
 *
 * Creates:
 *  - 10 users with profiles, balances, referral codes, varied account types
 *  - 10 gigs (status=active) with images, descriptions, social platforms
 *  - 10 marketplace listings (status=active) with images, prices, locations
 *  - 10 tasks (status=1 active) across categories
 *  - 6 blog categories + 10 blog posts (status=published) with featured images
 *  - Blog views, likes, comments, rates across posts
 *  - Reviews/ratings on gigs, listings, tasks, and user profiles
 *    (at least one user gets >=10 positive reviews to earn "recommendable" badge)
 *  - Marketplace comments
 *  - 4 PTC ads (status=approved) with images, varied durations
 *  - Transactions for earnings/balance realism
 *
 * Idempotent: wipes demo data on re-run via truncate (respecting FK order).
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Airtrendmedia demo data...');

        // ---- Wipe existing demo data (keep admin + categories + countries) ----
        $this->truncateDemoData();

        // ---- Demo admin (God-mode) account ----
        $this->seedAdmin();

        // ---- Users ----
        $users = $this->seedUsers();

        // ---- Blog Categories ----
        $blogCats = $this->seedBlogCategories();

        // ---- Gigs ----
        $gigs = $this->seedGigs($users);

        // ---- Marketplace Listings ----
        $listings = $this->seedMarketplaceListings($users);

        // ---- Tasks ----
        $tasks = $this->seedTasks($users);

        // ---- Blog Posts ----
        $blogs = $this->seedBlogPosts($users, $blogCats);

        // ---- PTC Ads ----
        $ptcAds = $this->seedPtcAds($users);

        // ---- Reviews (gigs, listings, tasks, profiles) ----
        $this->seedReviews($users, $gigs, $listings, $tasks);

        // ---- Marketplace Comments ----
        $this->seedComments($users, $listings);

        // ---- Blog engagement (views, likes, comments, rates) ----
        $this->seedBlogEngagement($users, $blogs);

        // ---- Transactions ----
        $this->seedTransactions($users);

        // ---- Recompute all user ratings ----
        foreach ($users as $u) {
            Review::recomputeUser($u->id);
        }

        $this->command->info('Demo data seeded successfully!');
        $this->printSummary($users, $gigs, $listings, $tasks, $blogs, $ptcAds);
    }

    private function truncateDemoData(): void
    {
        // Order matters for FK constraints safety on SQLite too
        Review::query()->delete();
        Comment::query()->delete();
        BlogView::query()->delete();
        BlogLike::query()->delete();
        BlogRate::query()->delete();
        BlogComment::query()->delete();
        BlogPost::query()->delete();
        BlogCategory::query()->delete();
        PtcAd::query()->delete();
        MarketplaceListing::query()->delete();
        Gig::query()->delete();
        Task::query()->delete();
        Transaction::query()->delete();

        // Remove demo users (keep admin from installer — admins table is separate)
        User::query()->where('email', 'like', '%@airtrendmedia.demo%')->delete();

        // Reset auto-increment sequences so IDs start at 1 (SQLite)
        $tables = [
            'reviews', 'comments', 'blog_views', 'blog_likes', 'blog_rates',
            'blog_comments', 'blog_posts', 'blog_categories', 'ptc_ads',
            'marketplace_listings', 'gigs', 'tasks', 'transactions', 'users',
        ];
        foreach ($tables as $table) {
            \DB::statement("DELETE FROM sqlite_sequence WHERE name = '{$table}'");
        }
    }

    /**
     * Create a demo God-mode admin account so the platform is usable
     * immediately after seeding (without running the web installer).
     * Credentials: username=admin / password=admin123
     */
    private function seedAdmin(): void
    {
        Admin::updateOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Platform Admin',
                'email'    => 'admin@airtrendmedia.test',
                'password' => \Hash::make('admin123'),
                'role'     => 'super',
            ]
        );

        // Also mark the app as installed so the installer middleware
        // does not redirect every request to /install.
        $installedFile = storage_path('app/installed.json');
        if (! file_exists($installedFile)) {
            file_put_contents($installedFile, json_encode([
                'installed_at' => now()->toDateTimeString(),
                'version'      => '1.0.0',
                'demo'         => true,
            ]));
        }
    }

    private function seedUsers(): array
    {
        $catSocial = TaskCategory::where('name', 'Social Media')->first();
        $catFB = TaskCategory::where('name', 'like', '%Facebook%')->first();
        $catYT = TaskCategory::where('name', 'like', '%YouTube%')->first();

        $profiles = [
            ['sarah_k', 'Sarah Kim', 'Freelance social media manager helping brands grow online. 5+ years experience.', 'freelancer'],
            ['mike_d', 'Mike Davis', 'Digital marketer and SEO specialist. I help businesses rank higher.', 'freelancer'],
            ['lena_p', 'Lena Park', 'Content creator and graphic designer. Let me make your brand shine.', 'freelancer'],
            ['james_w', 'James Wilson', 'Video editor and YouTube growth expert. Fast turnaround guaranteed.', 'freelancer'],
            ['aria_t', 'Aria Torres', 'Influencer marketing consultant. Connecting brands with audiences.', 'both'],
            ['david_c', 'David Chen', 'Full-stack developer and web designer. Building modern websites.', 'freelancer'],
            ['nina_b', 'Nina Brown', 'Copywriter and blogger. Words that convert and engage.', 'freelancer'],
            ['omar_s', 'Omar Saleh', 'Crypto and fintech marketer. Growing Web3 communities.', 'advertiser'],
            ['emma_l', 'Emma Lee', 'PPC and paid ads specialist. Maximizing ROI for every dollar.', 'both'],
            ['carl_j', 'Carl Johnson', 'Marketplace seller and dropshipping expert. Quality products at fair prices.', 'both'],
        ];

        $users = [];
        $countries = ['US', 'GB', 'CA', 'AU', 'DE', 'FR', 'IN', 'BR', 'NG', 'ZA'];

        foreach ($profiles as $i => $p) {
            $username = $p[0];
            $name = $p[1];
            $bio = $p[2];
            $type = $p[3];
            $email = $username . '@airtrendmedia.demo';

            $user = User::create([
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'phone' => '+1' . str_pad((string)(5550000000 + $i), 10, '0', STR_PAD_LEFT),
                'country_code' => $countries[$i],
                'password' => Hash::make('password123'),
                'bio' => $bio,
                'balance' => mt_rand(5, 500) + (mt_rand(0, 99) / 100),
                'total_earned' => mt_rand(50, 5000) + (mt_rand(0, 99) / 100),
                'referral_code' => strtoupper(Str::random(8)),
                'is_verified' => $i < 3, // first 3 are verified
                'is_active' => true,
                'email_verified_at' => now(),
                'activated_at' => now(),
                'account_type' => $type,
                'kyc_status' => $i < 2 ? 'approved' : 'pending',
                'verification_status' => $i < 3 ? 'verified' : 'none',
                'rating_avg' => 0,
                'rating_count' => 0,
                'positive_review_count' => 0,
                'is_recommendable' => false,
            ]);

            $users[] = $user;
        }

        $this->command->info('  Created ' . count($users) . ' demo users');
        return $users;
    }

    private function seedExtraReviewers(int $count): array
    {
        $firstNames = ['Alex', 'Taylor', 'Jordan', 'Riley', 'Casey', 'Morgan', 'Sam', 'Quinn', 'Drew', 'Pat'];
        $lastNames = ['Smith', 'Jones', 'Garcia', 'Miller', 'Lee', 'Walker', 'Hall', 'Young'];

        $reviewers = [];
        for ($i = 0; $i < $count; $i++) {
            $fn = $firstNames[array_rand($firstNames)];
            $ln = $lastNames[array_rand($lastNames)];
            $username = strtolower($fn . $ln . mt_rand(10, 99));
            $user = User::create([
                'username' => $username,
                'name' => $fn . ' ' . $ln,
                'email' => $username . '@airtrendmedia.demo',
                'country_code' => 'US',
                'password' => Hash::make('password123'),
                'bio' => 'Verified community member who provides honest reviews.',
                'balance' => mt_rand(1, 50),
                'total_earned' => mt_rand(10, 500),
                'referral_code' => strtoupper(Str::random(8)),
                'is_verified' => true,
                'is_active' => true,
                'email_verified_at' => now(),
                'activated_at' => now(),
                'account_type' => 'freelancer',
                'kyc_status' => 'approved',
                'verification_status' => 'verified',
                'rating_avg' => 0,
                'rating_count' => 0,
                'positive_review_count' => 0,
                'is_recommendable' => false,
            ]);
            $reviewers[] = $user;
        }

        $this->command->info('  Created ' . count($reviewers) . ' extra reviewer users');
        return $reviewers;
    }

    private function seedBlogCategories(): array
    {
        $cats = [
            ['Digital Marketing', '#2563eb', 'Strategies and tips for growing your online presence.'],
            ['Freelancing', '#059669', 'Guides for freelancers and remote workers.'],
            ['Make Money Online', '#d97706', 'Proven methods to earn income on the internet.'],
            ['Social Media', '#7c3aed', 'Platform-specific growth and engagement tactics.'],
            ['Micro Jobs', '#0891b2', 'Everything about the gig economy and micro tasks.'],
            ['Technology', '#dc2626', 'Latest tech trends and tools for creators.'],
        ];

        $models = [];
        $order = 0;
        foreach ($cats as $c) {
            $models[] = BlogCategory::create([
                'name' => $c[0],
                'slug' => Str::slug($c[0]),
                'description' => $c[2],
                'color' => $c[1],
                'is_active' => true,
                'sort_order' => $order++,
            ]);
        }

        $this->command->info('  Created ' . count($models) . ' blog categories');
        return $models;
    }

    private function seedGigs(array $users): array
    {
        $catFb = TaskCategory::where('name', 'like', '%Facebook%')->first()?->id;
        $catYt = TaskCategory::where('name', 'like', '%YouTube%')->first()?->id;
        $catIg = TaskCategory::where('name', 'like', '%Instagram%')->first()?->id;
        $catTw = TaskCategory::where('name', 'like', '%Twitter%')->first()?->id ?? TaskCategory::where('name', 'like', '%X %')->first()?->id;
        $catSeo = TaskCategory::where('name', 'like', '%SEO%')->first()?->id;
        $catWrite = TaskCategory::where('name', 'like', '%Content%')->first()?->id ?? TaskCategory::where('name', 'like', '%Writing%')->first()?->id;
        $catDesign = TaskCategory::where('name', 'like', '%Design%')->first()?->id ?? TaskCategory::where('name', 'like', '%Graphic%')->first()?->id;
        $catVideo = TaskCategory::where('name', 'like', '%Video%')->first()?->id;
        $catTiktok = TaskCategory::where('name', 'like', '%TikTok%')->first()?->id;

        $gigData = [
            ['Social Media Management Package', 'I will manage your social media accounts for one full week. Daily posts, community engagement, and growth reporting included.', 25.00, 'facebook', 'gigs/gig-demo-1.png', $catFb, 0],
            ['YouTube Subscribers Growth Service', 'Get real, organic YouTube subscribers. I use proven growth strategies to bring genuine viewers to your channel.', 15.00, 'youtube', 'gigs/gig-demo-2.png', $catYt, 1],
            ['Instagram Followers & Engagement Boost', 'Increase your Instagram followers with real, active users. Improved engagement rate guaranteed within 7 days.', 12.00, 'instagram', 'gigs/gig-demo-3.png', $catIg, 2],
            ['Twitter/X Growth & Content Strategy', 'I will grow your Twitter/X following with targeted content and engagement strategies tailored to your niche.', 18.00, 'twitter', 'gigs/gig-demo-4.png', $catTw, 3],
            ['Complete SEO Audit & Optimization', 'Full SEO audit of your website with actionable recommendations. On-page and technical optimization included.', 50.00, 'website', 'gigs/gig-demo-5.png', $catSeo, 4],
            ['Blog Post Writing (1000 words)', 'High-quality, SEO-optimized blog posts on any topic. Well-researched, engaging, and ready to publish.', 20.00, 'website', 'gigs/gig-demo-6.png', $catWrite, 5],
            ['Custom Graphic Design & Branding', 'Professional logo design, social media graphics, and brand identity packages. Unlimited revisions until satisfied.', 35.00, 'instagram', 'gigs/gig-demo-7.png', $catDesign, 6],
            ['Professional Video Editing Service', 'I will edit your raw footage into a polished, engaging video. Includes transitions, music, and color grading.', 30.00, 'youtube', 'gigs/gig-demo-8.png', $catVideo, 7],
            ['TikTok Viral Content Strategy', 'Get your TikTok account growing with viral-worthy content strategies. Trend research and posting schedule included.', 22.00, 'tiktok', 'gigs/gig-demo-9.png', $catTiktok, 8],
            ['Full Social Media Setup & Branding Kit', 'Complete social media setup across all platforms with branded graphics, bios, and content calendar.', 45.00, 'facebook', 'gigs/gig-demo-1.png', $catFb, 9],
        ];

        $gigs = [];
        foreach ($gigData as $i => $g) {
            $user = $users[$g[6]];
            $gigs[] = Gig::create([
                'user_id' => $user->id,
                'category_id' => $g[5],
                'title' => $g[0],
                'description' => $g[1],
                'price' => $g[2],
                'image' => $g[4],
                'gallery' => null,
                'social_platform' => $g[3],
                'social_url' => 'https://' . $g[3] . '.com/demo-' . ($i + 1),
                'status' => 'active',
                'views' => mt_rand(50, 2000),
                'sales' => mt_rand(0, 50),
            ]);
        }

        $this->command->info('  Created ' . count($gigs) . ' gigs');
        return $gigs;
    }

    private function seedMarketplaceListings(array $users): array
    {
        $catGen = TaskCategory::first()?->id;

        $listingData = [
            ['Gaming Laptop - RTX 4060, 16GB RAM', 'High-performance gaming laptop in excellent condition. RTX 4060 GPU, 16GB RAM, 1TB SSD. Perfect for gaming and content creation.', 899.99, 'sell', 'marketplace/market-demo-1.png', 'New York, USA'],
            ['Wireless Earbuds Pro - Noise Cancelling', 'Premium wireless earbuds with active noise cancellation. 30-hour battery life with charging case. Brand new, sealed.', 79.99, 'sell', 'marketplace/market-demo-2.png', 'London, UK'],
            ['Smart Watch Series 7 - Fitness Tracker', 'Feature-packed smartwatch with heart rate monitor, GPS, and 7-day battery life. Compatible with iOS and Android.', 199.99, 'sell', 'marketplace/market-demo-3.png', 'Toronto, Canada'],
            ['Professional DSLR Camera Kit', 'Complete photography kit including DSLR camera, two lenses, tripod, and carrying bag. Lightly used, excellent condition.', 650.00, 'sell', 'marketplace/market-demo-4.png', 'Sydney, Australia'],
            ['RGB Mechanical Gaming Keyboard', 'Premium mechanical keyboard with Cherry MX switches and customizable RGB lighting. Includes wrist rest.', 89.99, 'sell', 'marketplace/market-demo-5.png', 'Berlin, Germany'],
            ['Latest Smartphone 128GB - Unlocked', 'Unlocked smartphone with 128GB storage, triple camera system, and OLED display. Comes with original box and charger.', 449.99, 'sell', 'marketplace/market-demo-6.png', 'Paris, France'],
            ['Designer Travel Backpack - Waterproof', 'Stylish and durable travel backpack with laptop compartment. Waterproof material, USB charging port. 30L capacity.', 59.99, 'sell', 'marketplace/market-demo-7.png', 'Mumbai, India'],
            ['Gaming Mouse - 16000 DPI RGB', 'High-precision gaming mouse with 16000 DPI sensor, programmable buttons, and customizable RGB lighting.', 34.99, 'sell', 'marketplace/market-demo-8.png', 'São Paulo, Brazil'],
            ['Portable Bluetooth Speaker - Waterproof', 'Powerful portable speaker with 360-degree sound, 20-hour battery, and IPX7 waterproof rating. Perfect for outdoors.', 49.99, 'sell', 'marketplace/market-demo-9.png', 'Lagos, Nigeria'],
            ['Online Store Setup & Dropshipping Consultation', 'I will help you set up your online store and provide dropshipping consultation. Includes supplier sourcing guide.', 150.00, 'sell', 'marketplace/market-demo-10.png', 'Johannesburg, South Africa'],
        ];

        $listings = [];
        foreach ($listingData as $i => $l) {
            $user = $users[$i];
            $listings[] = MarketplaceListing::create([
                'user_id' => $user->id,
                'category_id' => $catGen,
                'title' => $l[0],
                'description' => $l[1],
                'price' => $l[2],
                'listing_type' => $l[3],
                'image' => $l[4],
                'gallery' => null,
                'location' => $l[5],
                'status' => 'active',
                'views' => mt_rand(20, 800),
            ]);
        }

        $this->command->info('  Created ' . count($listings) . ' marketplace listings');
        return $listings;
    }

    private function seedTasks(array $users): array
    {
        $cats = TaskCategory::whereNotNull('id')->pluck('id')->take(10)->toArray();
        if (count($cats) < 10) {
            $cats = array_pad($cats, 10, TaskCategory::first()->id);
        }

        $taskData = [
            ['Like and share a Facebook post', 'Visit the provided Facebook post, like it, and share it to your timeline. Submit screenshot as proof.', 0.05],
            ['Subscribe to a YouTube channel', 'Subscribe to the given YouTube channel and watch at least one video. Submit screenshot.', 0.10],
            ['Follow an Instagram account', 'Follow the specified Instagram account and like 3 recent posts. Submit screenshot as proof.', 0.03],
            ['Comment on a blog post', 'Read the provided blog post and leave a thoughtful comment. Submit the comment URL.', 0.08],
            ['Retweet a Twitter/X post', 'Retweet the specified post and follow the account. Submit screenshot as proof.', 0.04],
            ['Watch a 2-minute promotional video', 'Watch the full video and answer a simple question about its content. Submit your answer.', 0.02],
            ['Sign up for a newsletter', 'Subscribe to the provided email newsletter and confirm your subscription. Submit confirmation email screenshot.', 0.06],
            ['Download and test a mobile app', 'Download the specified free app, use it for 5 minutes, and rate it 5 stars. Submit screenshots.', 0.15],
            ['Write a short product review', 'Write a 50-word review for a product on an e-commerce site. Submit the review link.', 0.12],
            ['Complete a quick survey', 'Answer a 5-question survey about online shopping habits. Takes 2 minutes. Submit completion screenshot.', 0.01],
        ];

        $tasks = [];
        foreach ($taskData as $i => $t) {
            $user = $users[$i];
            $tasks[] = Task::create([
                'code' => 'TASK-' . str_pad((string)($i + 1), 4, '0', STR_PAD_LEFT),
                'title' => $t[0],
                'price' => $t[2],
                'action_url' => 'https://example.com/task-' . ($i + 1),
                'details' => $t[1],
                'category_id' => $cats[$i],
                'user_id' => $user->id,
                'date' => now()->addDays(mt_rand(1, 30))->toDateString(),
                'amount' => $t[2],
                'time' => mt_rand(1, 10),
                'total_price' => $t[2] * 10,
                'booked' => mt_rand(0, 5),
                'submitted' => mt_rand(0, 3),
                'completed' => mt_rand(0, 2),
                'status' => 1, // active
                'reject_note' => null,
            ]);
        }

        $this->command->info('  Created ' . count($tasks) . ' tasks');
        return $tasks;
    }

    private function seedBlogPosts(array $users, array $blogCats): array
    {
        $postContent = function (string $topic): string {
            return "<h2>Introduction</h2><p>In today's rapidly evolving digital landscape, understanding {$topic} is more important than ever. Whether you're a seasoned professional or just starting out, the insights shared in this article will help you navigate the challenges and opportunities that lie ahead.</p><h2>Why It Matters</h2><p>The world of online business moves fast. Staying ahead means keeping up with the latest trends, tools, and strategies. This guide breaks down everything you need to know into actionable steps you can implement right away.</p><h2>Key Strategies</h2><p>First, focus on building a strong foundation. Consistency is key — small daily actions compound into significant results over time. Second, leverage automation where possible to free up your time for high-value activities. Third, always measure your results and adjust your approach based on data.</p><h2>Common Mistakes to Avoid</h2><p>Many newcomers try to do everything at once, leading to burnout and scattered efforts. Instead, pick one strategy and master it before moving to the next. Another common pitfall is ignoring audience feedback — your community is your best source of insight.</p><h2>Conclusion</h2><p>Success in the digital space is achievable with the right mindset and tools. Start small, stay consistent, and never stop learning. The journey may be challenging, but the rewards are well worth the effort.</p>";
        };

        $blogData = [
            ['10 Digital Marketing Trends to Watch in 2025', 'blog-demo-1.png', 0, 'Stay ahead of the curve with these emerging digital marketing trends.', 8],
            ['How to Start Freelancing with Zero Experience', 'blog-demo-2.png', 1, 'A complete beginner\'s guide to launching a freelance career from scratch.', 7],
            ['7 Proven Ways to Make Money Online in 2025', 'blog-demo-3.png', 2, 'Legitimate and tested methods for earning income on the internet.', 6],
            ['Social Media Growth: A Platform-by-Platform Guide', 'blog-demo-4.png', 3, 'Master each social platform with tailored growth strategies.', 9],
            ['The Micro Job Economy: Earning One Task at a Time', 'blog-demo-5.png', 4, 'Understanding micro jobs and how they fit into the modern gig economy.', 5],
            ['Essential Tools Every Digital Creator Needs', 'blog-demo-6.png', 5, 'A curated list of tools to boost your productivity and creativity.', 6],
            ['Building a Personal Brand That Stands Out', 'blog-demo-1.png', 0, 'Practical steps to create a memorable and authentic personal brand.', 8],
            ['From Side Hustle to Full-Time: Making the Leap', 'blog-demo-2.png', 1, 'How to transition your side project into a sustainable full-time income.', 7],
            ['The Psychology of Social Media Engagement', 'blog-demo-4.png', 3, 'Understanding why people engage and how to leverage it for growth.', 10],
            ['Passive Income Myths Debunked: What Really Works', 'blog-demo-3.png', 2, 'Separating fact from fiction in the world of passive income.', 6],
        ];

        $blogs = [];
        foreach ($blogData as $i => $b) {
            $author = $users[$i];
            $title = $b[0];
            $blogs[] = BlogPost::create([
                'author_id' => $author->id,
                'category_id' => $blogCats[$b[2]]->id,
                'title' => $title,
                'slug' => Str::slug($title) . '-' . strtolower(Str::random(6)),
                'content' => $postContent(strtolower($b[0])),
                'excerpt' => $b[3],
                'featured_image' => 'blogs/' . $b[1],
                'gallery' => null,
                'meta_description' => $b[3],
                'meta_keywords' => 'digital marketing, freelancing, online income, ' . $blogCats[$b[2]]->name,
                'meta_title' => $title,
                'tags' => $blogCats[$b[2]]->name . ', guide, tips, 2025',
                'status' => 'published',
                'is_featured' => $i < 3,
                'published_at' => now()->subDays(mt_rand(1, 60)),
                'views_count' => mt_rand(100, 5000),
                'likes_count' => mt_rand(10, 300),
                'comments_count' => 0, // updated after seeding comments
                'shares_count' => mt_rand(5, 100),
                'reading_time' => $b[4],
                'ratings_count' => 0, // updated after rates
            ]);
        }

        $this->command->info('  Created ' . count($blogs) . ' blog posts');
        return $blogs;
    }

    private function seedPtcAds(array $users): array
    {
        $advertiser = collect($users)->firstWhere('account_type', 'advertiser') ?? $users[7];

        $ptcData = [
            ['Click & Earn Rewards Portal', 'Visit our rewards portal and discover exclusive deals. Stay for the full duration and confirm to earn instant cash rewards!', 'ptc/ptc-demo-1.png', 10, 'automatic'],
            ['Mega Online Shopping Sale', 'Don\'t miss our biggest sale of the year! Up to 80% off on electronics, fashion, and home goods. Click to explore deals.', 'ptc/ptc-demo-2.png', 20, 'automatic'],
            ['Crypto Trading Made Simple', 'Start your crypto journey today. Learn how to trade with our easy-to-use platform and expert guides. Click to learn more.', 'ptc/ptc-demo-3.png', 30, 'automatic'],
            ['Play & Win - Mobile Gaming App', 'Download our new gaming app and win real prizes! Exciting games and daily tournaments. Tap to start playing now.', 'ptc/ptc-demo-4.png', 15, 'manual'],
        ];

        $ads = [];
        foreach ($ptcData as $i => $a) {
            $duration = $a[3];
            $units = intdiv($duration, 10);
            $reward = $units * 0.005;
            $cost = $reward; // advertiser pays the reward amount minimum

            $ads[] = PtcAd::create([
                'user_id' => $advertiser->id,
                'title' => $a[0],
                'url' => 'https://example.com/ptc-ad-' . ($i + 1),
                'description' => $a[1],
                'image' => $a[2],
                'duration_seconds' => $duration,
                'reward_per_view' => $reward,
                'cost_per_view' => $cost,
                'budget' => mt_rand(10, 100),
                'views_count' => mt_rand(5, 50),
                'max_views' => mt_rand(100, 1000),
                'status' => 'approved',
                'mode' => $a[4],
                'admin_note' => 'Demo PTC ad',
                'approved_at' => now()->subDays(mt_rand(1, 10)),
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(30),
            ]);
        }

        $this->command->info('  Created ' . count($ads) . ' PTC ads');
        return $ads;
    }

    private function seedReviews(array $users, array $gigs, array $listings, array $tasks): void
    {
        $reviewBodies = [
            'Excellent work! Delivered exactly what was promised. Highly recommended.',
            'Great communication and fast delivery. Will definitely order again.',
            'Outstanding quality and attention to detail. Very satisfied!',
            'Amazing service, exceeded my expectations. Five stars all the way.',
            'Professional and reliable. The results speak for themselves.',
            'Good experience overall. A few minor delays but great final result.',
            'Top-notch service. Highly skilled and easy to work with.',
            'Fantastic! The quality was better than I imagined. Thank you!',
            'Reliable, efficient, and friendly. Exactly what I needed.',
            'Perfect work! Will be a returning customer for sure.',
            'Great value for money. The output was professional and polished.',
            'Impressed with the speed and quality. Highly recommend to others.',
        ];

        // ---- Reviews on gigs ----
        foreach ($gigs as $gi => $gig) {
            // Each gig gets 3-6 reviews from different users
            $reviewers = collect($users)->shuffle()->take(mt_rand(3, 6));
            foreach ($reviewers as $reviewer) {
                if ($reviewer->id === $gig->user_id) continue;
                Review::create([
                    'user_id' => $reviewer->id,
                    'reviewable_type' => Gig::class,
                    'reviewable_id' => $gig->id,
                    'rating' => mt_rand(4, 5),
                    'body' => $reviewBodies[array_rand($reviewBodies)],
                    'is_approved' => true,
                ]);
            }
        }

        // ---- Reviews on marketplace listings ----
        foreach ($listings as $li => $listing) {
            $reviewers = collect($users)->shuffle()->take(mt_rand(2, 5));
            foreach ($reviewers as $reviewer) {
                if ($reviewer->id === $listing->user_id) continue;
                Review::create([
                    'user_id' => $reviewer->id,
                    'reviewable_type' => MarketplaceListing::class,
                    'reviewable_id' => $listing->id,
                    'rating' => mt_rand(3, 5),
                    'body' => $reviewBodies[array_rand($reviewBodies)],
                    'is_approved' => true,
                ]);
            }
        }

        // ---- Reviews on tasks ----
        foreach ($tasks as $task) {
            $reviewers = collect($users)->shuffle()->take(mt_rand(2, 4));
            foreach ($reviewers as $reviewer) {
                if ($reviewer->id === $task->user_id) continue;
                Review::create([
                    'user_id' => $reviewer->id,
                    'reviewable_type' => Task::class,
                    'reviewable_id' => $task->id,
                    'rating' => mt_rand(4, 5),
                    'body' => $reviewBodies[array_rand($reviewBodies)],
                    'is_approved' => true,
                ]);
            }
        }

        // ---- Profile reviews (5-star) ----
        // Create extra reviewer-only users so Sarah & Mike can reach >=12 positive reviews
        // (the unique constraint allows only 1 review per reviewer per target, and with 10
        //  core users there are only 9 possible reviewers for any one profile).
        $extraReviewers = $this->seedExtraReviewers(8);

        // Sarah (user 0) gets 12+ positive reviews to become "recommendable"
        $sarah = $users[0];
        $allReviewersForSarah = collect($users)->skip(1)->merge($extraReviewers)->take(12);
        foreach ($allReviewersForSarah as $reviewer) {
            Review::updateOrCreate(
                [
                    'user_id' => $reviewer->id,
                    'reviewable_type' => User::class,
                    'reviewable_id' => $sarah->id,
                ],
                [
                    'rating' => 5,
                    'body' => $reviewBodies[array_rand($reviewBodies)],
                    'is_approved' => true,
                ]
            );
        }

        // Mike (user 1) also gets 12+ positive reviews to become "recommendable"
        $mike = $users[1];
        $allReviewersForMike = collect($users)->filter(fn($u) => $u->id !== $mike->id)->merge($extraReviewers)->take(12);
        foreach ($allReviewersForMike as $reviewer) {
            Review::updateOrCreate(
                [
                    'user_id' => $reviewer->id,
                    'reviewable_type' => User::class,
                    'reviewable_id' => $mike->id,
                ],
                [
                    'rating' => 5,
                    'body' => $reviewBodies[array_rand($reviewBodies)],
                    'is_approved' => true,
                ]
            );
        }

        // Give remaining core users 2-4 profile reviews each
        foreach (array_slice($users, 2) as $user) {
            $reviewers = collect($users)->filter(fn($u) => $u->id !== $user->id)->merge(collect($extraReviewers)->take(2))->shuffle()->take(mt_rand(2, 4));
            foreach ($reviewers as $reviewer) {
                Review::updateOrCreate(
                    [
                        'user_id' => $reviewer->id,
                        'reviewable_type' => User::class,
                        'reviewable_id' => $user->id,
                    ],
                    [
                        'rating' => mt_rand(4, 5),
                        'body' => $reviewBodies[array_rand($reviewBodies)],
                        'is_approved' => true,
                    ]
                );
            }
        }

        $count = Review::count();
        $this->command->info('  Created ' . $count . ' reviews/ratings');
    }

    private function seedComments(array $users, array $listings): void
    {
        $commentBodies = [
            'Is this still available?',
            'Great listing! What\'s your best price?',
            'I\'m interested. Can you ship internationally?',
            'Does this come with a warranty?',
            'Looks good! Do you have more photos?',
            'What\'s the condition? Any scratches?',
            'Can I pay in installments?',
            'Great price for what\'s included!',
        ];

        foreach ($listings as $listing) {
            $commenters = collect($users)->filter(fn($u) => $u->id !== $listing->user_id)->shuffle()->take(mt_rand(2, 4));
            foreach ($commenters as $commenter) {
                $parent = Comment::create([
                    'user_id' => $commenter->id,
                    'commentable_type' => MarketplaceListing::class,
                    'commentable_id' => $listing->id,
                    'parent_id' => null,
                    'body' => $commentBodies[array_rand($commentBodies)],
                    'is_approved' => true,
                ]);

                // Seller replies to some comments
                if (mt_rand(0, 1)) {
                    Comment::create([
                        'user_id' => $listing->user_id,
                        'commentable_type' => MarketplaceListing::class,
                        'commentable_id' => $listing->id,
                        'parent_id' => $parent->id,
                        'body' => 'Yes, it\'s available! Feel free to send an inquiry for more details.',
                        'is_approved' => true,
                    ]);
                }
            }
        }

        $this->command->info('  Created ' . Comment::count() . ' marketplace comments');
    }

    private function seedBlogEngagement(array $users, array $blogs): void
    {
        $blogCommentBodies = [
            'Great article! Really helpful insights, thanks for sharing.',
            'This is exactly what I needed to read today. Bookmarked!',
            'Fantastic guide. I\'ll be implementing these strategies right away.',
            'Well-written and informative. Looking forward to more content like this.',
            'This helped me understand the topic much better. Thank you!',
            'Solid advice. I especially liked the section on common mistakes.',
            'Awesome post! Sharing this with my team.',
            'Very practical tips. Can\'t wait to try them out.',
        ];

        foreach ($blogs as $blog) {
            // Views (create blog_views records)
            $viewers = collect($users)->shuffle()->take(mt_rand(3, 8));
            foreach ($viewers as $viewer) {
                BlogView::create([
                    'post_id' => $blog->id,
                    'user_id' => $viewer->id,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Demo Seeder',
                ]);
            }

            // Likes
            $likers = collect($users)->shuffle()->take(mt_rand(2, 6));
            foreach ($likers as $liker) {
                BlogLike::create([
                    'post_id' => $blog->id,
                    'user_id' => $liker->id,
                ]);
            }

            // Comments
            $commenters = collect($users)->shuffle()->take(mt_rand(2, 5));
            foreach ($commenters as $commenter) {
                BlogComment::create([
                    'post_id' => $blog->id,
                    'user_id' => $commenter->id,
                    'parent_id' => null,
                    'body' => $blogCommentBodies[array_rand($blogCommentBodies)],
                    'is_approved' => true,
                ]);
            }

            // Rates (1-5 stars)
            $raters = collect($users)->shuffle()->take(mt_rand(3, 6));
            foreach ($raters as $rater) {
                BlogRate::create([
                    'post_id' => $blog->id,
                    'user_id' => $rater->id,
                    'rating' => mt_rand(4, 5),
                ]);
            }

            // Update counts
            $blog->update([
                'comments_count' => BlogComment::where('post_id', $blog->id)->count(),
                'ratings_count' => BlogRate::where('post_id', $blog->id)->count(),
            ]);
        }

        $this->command->info('  Created blog engagement: ' . BlogView::count() . ' views, ' . BlogLike::count() . ' likes, ' . BlogComment::count() . ' comments, ' . BlogRate::count() . ' rates');
    }

    private function seedTransactions(array $users): void
    {
        $types = ['deposit', 'earning', 'withdrawal', 'ptc_reward', 'task_payment', 'gig_sale'];

        foreach ($users as $user) {
            // 3-7 transactions per user
            $numTx = mt_rand(3, 7);
            $balance = $user->balance;
            for ($i = 0; $i < $numTx; $i++) {
                $type = $types[array_rand($types)];
                $amount = $type === 'deposit' ? mt_rand(10, 200)
                    : ($type === 'withdrawal' ? mt_rand(5, 100)
                    : (mt_rand(1, 5000) / 100));

                if ($type === 'withdrawal') {
                    $amount = -$amount;
                }

                $balance += $amount;

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'reference' => 'TXN-' . strtoupper(Str::random(10)),
                    'amount' => $amount,
                    'balance_after' => $balance,
                    'currency' => 'USD',
                    'status' => 'completed',
                    'description' => ucfirst(str_replace('_', ' ', $type)) . ' transaction',
                    'ip_address' => '127.0.0.1',
                ]);
            }
        }

        $this->command->info('  Created ' . Transaction::count() . ' transactions');
    }

    private function printSummary(array $users, array $gigs, array $listings, array $tasks, array $blogs, array $ptcAds): void
    {
        $this->command->info('--- Demo Data Summary ---');
        $this->command->info('Users: ' . count($users) . ' (demo creds: any username@airtrendmedia.demo / password123)');
        $this->command->info('Gigs: ' . count($gigs));
        $this->command->info('Marketplace listings: ' . count($listings));
        $this->command->info('Tasks: ' . count($tasks));
        $this->command->info('Blog posts: ' . count($blogs));
        $this->command->info('PTC ads: ' . count($ptcAds));
        $this->command->info('Reviews: ' . Review::count());
        $this->command->info('Comments: ' . Comment::count());
        $recommendable = User::where('is_recommendable', true)->count();
        $this->command->info('Recommendable users (>=10 positive reviews): ' . $recommendable);
    }
}
