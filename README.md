# Airtrendmedia — Social · Microjobs · Advertisers in One App

Airtrendmedia is a complete all-in-one platform that combines a Facebook-clone social media network, a micro-job freelance marketplace, and an advertisers system into a single application with one unified admin "God-mode" control panel. Built with Laravel 11+, PHP 8.3, Tailwind CSS, and Alpine.js.

**Production domain:** [airtrendmedia.com](https://airtrendmedia.com)
**Repository:** [primemini016-lang/Airtrendmedia](https://github.com/primemini016-lang/Airtrendmedia)

---

## Three Worlds, One App

### 1. Social Media (Facebook Clone)
A full social network with Facebook-style UI and interactions.

- **Feed** with colored posts, images, videos, and rich text
- **Stories** with 24-hour expiry and story viewer modal
- **Reels** and short-form video support
- **Blogs** with full-screen article pages
- **Reactions** — Like, Love, Haha, Wow, Sad, Angry on every post
- **Comments** with nested replies and media attachments
- **Shares & views** counters on all content
- **Groups** — create, join, post, and manage members
- **Pages** with monetization dashboards (followers, views, earnings)
- **Messenger** with real-time typing indicators and unlimited emoji reactions on every message
- **Notification bell** with AJAX polling
- **Action sounds** — synthesized tones for like, comment, message, notification, send, and error events (Web Audio API with .wav fallback)

### 2. Micro-Job Marketplace
A complete freelance marketplace where users post and complete tasks.

- **Task system** — Post microjobs, workers book and submit proof, owners approve/reject
- **Gigs system** — Freelancers post gigs with social-media platform integration
- **82 micro-job subcategories** with exact real social-media interaction icons:
  - Facebook (likes, reactions, shares, follows, group joins, views, stars)
  - Instagram (follows, likes, comments, story views, reels, saves)
  - YouTube (subscribers, likes, views, comments, watch time)
  - Twitter/X (follows, retweets, likes, comments)
  - TikTok (follows, likes, views, comments, shares)
  - LinkedIn, Telegram, WhatsApp, Pinterest, Spotify, SoundCloud, Twitch, Discord, Reddit, Quora, Medium, VK
  - Content writing, design, web/tech, marketing/SEO, surveys, app installs, video/audio
- **Marketplace (buy/sell)** — List items with images and categories
- **Wallet** — Deposits via Paystack, withdrawals, full transaction history
- **Affiliate program** — Earn rewards for referring new users
- Minimum **$0.01 per interaction** across all categories

### 3. Advertisers System
A self-serve advertising platform integrated directly into the social feed.

- **Sponsored ads in feed** — Image, video, and text ad types
- **$0.02 per click** default CPC (admin-adjustable)
- **"Sponsored" label** on every ad unit
- **Admin review** — All ads require approval before going live (auto/manual toggle)
- **Advertiser dashboard** with impressions, clicks, spend, and CTR stats
- **Account-type switching** — Users switch between Advertiser and Freelancer modes

---

## Monetization System

Content creators monetize their social pages through an automated eligibility system.

- **Auto-enable thresholds** — 500 paid followers ($5 each) + 1,000 eligible views + 1,000 real engagement interactions automatically enable monetization
- **Admin override** — Admins can disable monetization for any page with a required reason
- **Monetization gifts** — Admins can grant monetization status or bonus earnings
- **Per-page earnings dashboard** showing followers, views, engagement, and payout history

## Blue Verification Badge

A paid verification system modeled after social platforms.

- **Government ID required** — Users submit ID documents for review
- **$5 monthly subscription** with 30-day validity
- **Auto-expire** — Badges expire automatically after 30 days unless renewed
- **Admin management** — Review submissions, approve/deny, revoke badges
- **Auto-approval toggle** — Admins can enable automatic approval

## KYC (Know Your Customer)

Identity verification required before any withdrawal.

- **Document submission** — Users upload ID and proof of address
- **Admin review** — View submitted documents, approve or reject
- **Auto/manual approval** toggle
- **Withdrawal gate** — Withdrawals blocked until KYC is approved

## Sponsored Ads

- **Ad types** — Image, video, and text
- **CPC pricing** starting at $0.02/click
- **Admin review queue** with approve/deny
- **Real-time stats** — impressions, clicks, CTR, spend
- **"Sponsored" label** on all ad creatives

## Anti-Cheat System

Protects platform integrity and prevents fraud.

- **One account per IP** enforcement (configurable)
- **One account per government ID** to prevent duplicate accounts
- **View manipulation detection** with configurable thresholds
- **Flag system** — Suspicious activity flagged for admin review
- **Admin anti-cheat dashboard** with open/resolved flags

## Free Trial & Activation

- **3-day free trial** — New users get full access for 3 days
- **$5 activation fee** — Required after trial for marketplace access
- Enforced via middleware on task booking, wallet, and withdrawal routes

---

## Push Notifications

Phoenix-style native notifications with images and slides.

- **Firebase Cloud Messaging** integration
- **OneSignal** integration as alternative provider
- **Admin push settings** — Configure provider, keys, and test sends
- **Push logs** — Full delivery history and status tracking
- **Provider toggle** — none, firebase, or onesignal

## PWA (Progressive Web App)

Installable, offline-capable web application.

- **Installable** on mobile and desktop with app icons
- **Offline support** — Cached pages and offline fallback screen
- **Native app behavior** — Fullscreen, splash screen, theme color
- **Admin PWA settings** — Toggle enabled/offline/native behavior
- **Editable branding** — Logo, app name, colors, and custom CSS from admin
- **Maskable icons** for adaptive Android icons

## Action Sounds

Audio feedback for user interactions throughout the app.

- **Synthesized tones** via Web Audio API (no audio files required)
- **.wav file fallback** for custom sound packs
- **Sound types** — like, comment, message, notification, send, error
- **User toggle** — Sounds can be enabled/disabled per user (localStorage)
- **Auto-wiring** — Elements with `data-sound` attributes play sounds on click

---

## Admin "God-Mode" Control Panel

One unified admin system controls everything across all three platforms.

### Dashboard
- Live stats across social, marketplace, and advertiser systems
- Pending review tiles — KYC, verification badges, sponsored ads, anti-cheat flags
- God-mode overview of all platform activity

### User Management
- **View, edit, extend, ban, unban** any user
- **Adjust balance** and wallet funds
- **Login as user** (impersonation) for support
- **Account-type control** — Force advertiser/freelancer/social modes
- **Monetization control** — Enable/disable with required reason
- **Verification management** — Approve, revoke, extend badges

### Content Moderation
- **Social posts** — View, edit, delete, pin any post
- **Comments & reactions** — Moderate all interactions
- **Stories & reels** — Remove inappropriate content
- **Groups & pages** — Manage, verify, or dissolve
- **Blogs** — Edit, feature, or remove articles
- **Messenger** — Visibility into conversations for abuse handling

### Marketplace Moderation
- **Tasks** — Approve/reject, refund, force-complete
- **Gigs** — Approve, feature, or remove
- **Marketplace listings** — Moderate buy/sell items
- **Proof submissions** — Review worker submissions

### Finance
- **Deposits & withdrawals** — Approve, reject, process
- **Transactions** — Full ledger with filters
- **Paystack integration** — Payment keys set after install
- **Currency settings** — Dollar-to-currency conversion

### Extended Feature Management
- **KYC review** — View documents, approve/deny
- **Verification badges** — Review ID submissions, manage subscriptions
- **Sponsored ads** — Review queue, approve/deny, view stats
- **Anti-cheat** — Review flags, ban offenders, adjust thresholds
- **Monetization** — Review eligible pages, disable with reason, grant gifts
- **Push notifications** — Configure Firebase/OneSignal, send broadcasts
- **PWA settings** — Toggle features, manage offline behavior

### System Configuration
- **Appearance** — Upload logo/favicon, set theme colors, universal CSS, HTML injection
- **Email settings** — SMTP with auto-approval or manual verification toggle
- **Payment keys** — Paystack public/secret keys
- **System update** — Pull updates from GitHub directly (auto-update from `primemini016-lang/Airtrendmedia`)
- **Notifications** — Broadcast to all users
- **Ad management** — Place ads at any position (header, footer, sidebar)
- **Category management** with social-media icons and custom colors
- **Dark/light theme toggle**

### Installation Wizard (Recommended — Single Page)
- **Only requires** database credentials + admin account details on ONE page
- **Bulletproof atomic install** — either everything succeeds or nothing is written (no half-installed state)
- Payment keys are NOT collected during installation — set them after install
- Auto-generates APP_KEY and JWT secret (no manual `key:generate` or `jwt:secret` needed)
- Creates the admin account, runs all migrations, and seeds default categories in one operation
- Friendly error messages with preserved input if anything fails (e.g. wrong DB credentials)
- Locks the installer automatically after a successful install (`storage/app/installed.json`)

---

## Technology Stack

- **Backend:** Laravel 11+ / PHP 8.3
- **Frontend:** Tailwind CSS (CDN), Alpine.js, custom CSS
- **Auth:** Dual system — JWT (REST API) + Session (Blade web)
- **Database:** MySQL (production) / SQLite (local testing)
- **Payments:** Paystack with dollar-to-currency conversion
- **Email:** SMTP with auto/manual approval options
- **Push:** Firebase Cloud Messaging / OneSignal
- **PWA:** Service worker + manifest with offline support
- **Icons:** Custom inline SVG icon system (Blade `<x-icon>` component + `@categoryIcon` directive)

---

## Installation

### Requirements
- PHP 8.3+
- MySQL 5.7+ (or SQLite for local testing)
- Composer
- Web server (Apache/Nginx) or `php artisan serve`

### Steps

1. **Clone the repository:**
   ```bash
   git clone https://github.com/primemini016-lang/Airtrendmedia.git
   cd Airtrendmedia
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Set permissions:**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

4. **Run the web installer (recommended):** Navigate to your site's `/install` URL in a browser:
   ```
   https://your-domain.com/install
   ```
   - Fill in **Database Details** (host, port, database name, username, password)
   - Fill in **Admin Account Details** (name, username, email, password)
   - Click **Install Now** — the wizard handles everything else atomically:
     - Tests the database connection
     - Writes your credentials to `.env`
     - Generates `APP_KEY` and `JWT_SECRET` automatically
     - Runs `migrate:fresh --seed` (creates all 70 tables + seed data)
     - Creates your admin account
     - Locks the installer and redirects to the success page
   - If anything fails, you get a friendly error and can retry without losing your input
   - Payment API keys are set AFTER install from **Admin → Settings → Payment Keys**

### Manual Installation (Advanced — Only If Web Installer Is Unavailable)

If you prefer the command line or the web installer is not reachable:

1. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

2. **Set up database in `.env`:**
   ```bash
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=airtrendmedia
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```

3. **Run migrations and seeders:**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Create the install marker** (so the installer is locked):
   ```bash
   php -r "file_put_contents(storage_path('app/installed.json'), json_encode(['installed_at' => date('Y-m-d H:i:s'), 'version' => '2.0.0'], JSON_PRETTY_PRINT));"
   ```

### Local Testing (SQLite)
For quick local testing, use SQLite instead of MySQL:
```bash
# In .env
DB_CONNECTION=sqlite
# Remove DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan migrate:fresh --seed
php artisan serve
```

---

## Important Notes

### Authentication Guard
This application uses a **dual auth system**:
- `user` guard (JWT) — for REST API endpoints
- `web` guard (Session) — for Blade/web controllers

**The default guard in `config/auth.php` is `user` (JWT).** All web controllers and Blade layouts MUST explicitly use `Auth::guard('web')` or `auth('web')`. Using `Auth::attempt()` or `auth()->user()` without specifying the guard will fall back to JWT and return null, causing 500 errors.

### Payment Keys
Paystack API keys are intentionally NOT collected during the installation wizard. After installation:
1. Log in to the Admin Dashboard
2. Navigate to **Settings → Payment Keys**
3. Enter your Paystack public and secret keys

### Push Notifications
Configure push notifications after install from **Admin → Push Settings**:
1. Choose provider (Firebase or OneSignal)
2. Enter API keys
3. Send a test notification
4. View delivery logs in **Admin → Push Logs**

### System Updates
The admin panel includes a GitHub auto-update system. Navigate to **System → System Update** to pull the latest changes from the `primemini016-lang/Airtrendmedia` repository directly from the admin panel — no terminal access required.

### PWA Configuration
PWA is enabled by default. Manage it from **Admin → PWA Settings**:
- Toggle PWA enabled/offline/native behavior
- Icons are pre-generated in `public/images/`
- Service worker at `public/sw.js`
- Manifest at `public/manifest.json`

### Action Sounds
Action sounds work out of the box using synthesized Web Audio API tones. To use custom sound files, replace the files in `public/sounds/` (like.wav, comment.wav, message.wav, notification.wav, send.wav, error.wav). Users can toggle sounds on/off from the UI.

### AI Features
The platform includes AI-assisted features similar to major social networks, with automatic system updates pulled from the GitHub repository to keep the platform current.

---

## Default Admin Access
After running the installer or seeder, an admin account is created. Use the installer wizard to set your admin credentials, or check the database seeder for default credentials during development.

---

## Project Structure

```
airtrendmedia/
├── app/
│   ├── Http/Controllers/    # Web, API, Admin, Social controllers
│   ├── Models/              # Eloquent models (User, Post, Story, Gig, etc.)
│   └── Services/            # SettingService, IconRendererService, etc.
├── database/
│   ├── migrations/          # Full schema
│   └── seeders/             # CategorySeeder (82 subcategories), etc.
├── public/
│   ├── css/app.css          # Custom styles
│   ├── js/action-sounds.js  # Action sound player
│   ├── sounds/*.wav         # Sound files
│   ├── images/icon-*.png    # PWA icons
│   ├── manifest.json        # PWA manifest
│   └── sw.js                # Service worker
├── resources/views/
│   ├── layouts/             # app, admin, user, social layouts
│   ├── social/              # Feed, chat, stories, reels, blogs
│   ├── admin/               # God-mode admin panel
│   ├── user/                # KYC, verification, account-type, ads
│   └── public/              # Homepage with feature tabs
└── .env.example             # Full environment template
```

---

## License

This project is proprietary software developed for Airtrendmedia.
