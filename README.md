

Built with **Laravel 12**, **PHP 8.2 / 8.3 / 8.4 / 8.5**, Tailwind CSS, and Alpine.js.

**Repository:** [primemini016-lang/Airtrendmedia](https://github.com/primemini016-lang/Airtrendmedia)

---

## What's Inside

### Micro-Job Marketplace
- **Task system** — Post microjobs (min **$0.01** per task); workers book, submit image proof (all image types supported), owners approve/reject, instant wallet credit on approval.
- **Gigs system** — Freelancers post gigs with images, descriptions, social-platform integration, ratings & reviews.
- **200+ real social-media interaction categories** with exact platform icons:
  Facebook, Instagram, YouTube, Twitter/X, TikTok, LinkedIn, Telegram, WhatsApp, Pinterest, Snapchat, Reddit, Discord, Twitch, Spotify, SoundCloud, Quora, Medium, VK, Threads, Tumblr, Vimeo, Dailymotion, Mixcloud, Patreon, Kick, Rumble, Clubhouse, Signal, Viber, Line, Skype, Truth Social, Mastodon, Weibo, WeChat, Likee, ShareChat, Kuaishou, OnlyFans, Trovo, Xing, Meetup, Goodreads, Untappd, Substack, Behance, Dribbble, Flickr, GitHub, Google Reviews, Trustpilot, and more.
- **Marketplace (buy/sell)** — List items with images, prices, locations; comments (threaded), reviews, view counts; admin full management.
- **Wallet** — Deposits via Paystack, withdrawals, full transaction history.
- **Affiliate program** — Earn rewards for referring new users.

Runs **inside** Airtrendmedia. Users watch ads and earn instantly.
- Min **$0.005 per view** (each 10 seconds = $0.005; 20s = $0.01, etc.)
- Min duration **10 seconds**, max **5 hours** (admin-managed).
- Green **"Confirm Execution"** tab — users rewarded instantly after the timer + click.
- Admin sets minimum price, approves ads, controls timing.

### Blogs
- Users create and publish blog posts with featured images & galleries.
- **View count**, **comments + count**, **likes + count**, **rates + count**, **reviews + count**, **shares + count**, reading time, categories, tags.

### Reviews & Ratings (Unified)
- Polymorphic review/rating system covering **gigs, marketplace listings, tasks, and user profiles**.
- **5-star** ratings with cached averages and counts.
- **Recommendable profiles** — users with ≥10 positive (≥4-star) reviews are marked recommendable.
- **Verification badge** system (blue badge + KYC).
- One review per (user, target) — update-or-create.

### Messenger / Chat
- User-to-user chat with **message send/receive sounds**, **reply**, **emojis**, **typing indicator**.
- Facebook-look-alike colored message UI.
- **Notification + message icons** on the header (clickable, working, with unread-count polling).

---

## Admin God-Mode

A single admin panel controls **everything**:
- Marketplace, gigs, tasks, blogs, users management
- **Banner & Popup settings** — header banner, footer banner, popup banner (with description text, image, link, timing), and PWA install popup. 22 admin-managed fields.
- **PWA settings** + manifest generation
- **Site settings** (logo, favicon, colors, custom CSS/HTML, SEO)
- **Email, push/Firebase** settings
- **System update** — GitHub auto-update + AI auto-correction diagnostics (health checks, self-healing)
- Deposits, withdrawals, transactions management

---

## Frontend Icons — Real & 3D

All category icons are **real social-media brand icons** rendered as **3D glossy badges**:
- `IconRendererService::render3D()` produces a circular 3D badge with the platform's **real brand color**, a **radial gradient** highlight, **inner shadow**, and **drop shadow** for depth.
- Applied to category cards on the home page, task cards on browse, and the admin category manager.
- Flat `@categoryIcon` directive still available for inline use.

---

## Python Shared-Hosting Helper

`airtrendmedia_helper.py` — a dependency-free Python 3 script for shared hosting where SSH may be limited:

```bash
python3 airtrendmedia_helper.py --action=all           # clear-cache + symlink + permissions + migrate + health + report
python3 airtrendmedia_helper.py --action=clear-cache
python3 airtrendmedia_helper.py --action=permissions
python3 airtrendmedia_helper.py --action=migrate
python3 airtrendmedia_helper.py --action=migrate-seed
python3 airtrendmedia_helper.py --action=optimize
python3 airtrendmedia_helper.py --action=health
python3 airtrendmedia_helper.py --action=report
```

It auto-detects the PHP binary (preferring PHP ≥ 8.3), the Laravel project root, and writes a JSON deployment report to `storage/logs/deployment_report.json`.

---

## Installation

### Requirements
- **PHP 8.2, 8.3, 8.4, or 8.5** with extensions: pdo_mysql, mbstring, openssl, curl, gd or imagick, fileinfo, json
- MySQL 5.7+ / MariaDB 10.3+ (SQLite supported for local testing)
- Composer 2

### Quick start (local)
```bash
composer install
cp .env.example .env
php artisan key:generate
# edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan serve
```

### Demo credentials (after seeding)
- **Admin (God-mode):** `admin` / `admin123`
- **Demo users:** any `<username>@airtrendmedia.demo` / `password123`
  (e.g. `sarah_k@airtrendmedia.demo`, `mike_d@airtrendmedia.demo`)
- **2 recommendable users** with 12+ 5-star reviews: `sarah_k`, `mike_d`

### Web installer
If `storage/app/installed.json` does not exist, the app redirects to `/install` for a guided setup (admin account, database, site settings).

### Shared hosting
1. Upload all files to your `public_html` (or a subfolder).
2. Point the web root to the `public/` directory.
3. Run `python3 airtrendmedia_helper.py --action=all` (or run `php artisan migrate --force && php artisan db:seed --force && php artisan storage:link` via cron/terminal).
4. Visit the site and complete the installer if prompted.

---

## Demo Data (DemoDataSeeder)

`php artisan db:seed --force` creates:
- 10 core users + 8 extra reviewers (with profiles, balances, referral codes, varied account types)
- 1 demo admin (`admin` / `admin123`)
- 151 reviews, 40 comments, blog engagement (views/likes/comments/rates), transactions

---

## Testing

`smoke_test.php` runs a non-interactive HTTP smoke test of 50 routes (public + authenticated + admin):
```bash
php smoke_test.php
# Expected: PASS: 50 | FAIL: 0 | Total: 50  *** ALL PASSED ***
```

---

## Tech Stack
- Laravel 12.67, PHP 8.2–8.5, Tailwind CSS, Alpine.js
- Eloquent ORM with polymorphic relationships (reviews, comments)
- Session-based web auth + JWT API auth; separate admin guard
- SQLite (local) / MySQL (production)
- PWA-ready (manifest + install popup)

---

## License
Proprietary. © Airtrendmedia.
