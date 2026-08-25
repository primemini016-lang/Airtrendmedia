# MiniWorkers — Microjob Marketplace (AirtrendMedia)

A full-featured Laravel microjob marketplace platform where users can post tasks, complete microjobs, earn money, and manage their wallet. Built with Laravel 11+, PHP 8.3, Tailwind CSS, and Alpine.js.

**Production domain:** [airtrendmedia.com](https://airtrendmedia.com)

## Features

### Core Marketplace
- **Task system** — Post microjobs, workers book and submit proof, task owners approve/reject
- **Gigs system** — Freelancers post gigs with social media platform integration
- **Marketplace (buy/sell)** — Users list items for sale with images and categories
- **Wallet system** — Deposits via Paystack, withdrawals, transaction history
- **Affiliate program** — Earn rewards for referring new users

### Mandatory Activation Fee
- All users must pay a **$5 activation fee** before accessing any marketplace feature
- Enforced via middleware on all task booking, wallet, and withdrawal routes

### Admin Panel (Blue/White Theme)
- **Dashboard** with live stats (blue/white professional design, no dark theme)
- **User management** — ban, activate, adjust balance, login as user (impersonation)
- **Task moderation** — approve/reject tasks and proofs
- **Finance** — deposits, withdrawals, transactions
- **Category management** with social media icons and custom colors
- **Gigs & Marketplace moderation**
- **Ads management** — place ads at any position (header, footer, sidebar, etc.)
- **Appearance** — upload logo/favicon, set theme colors, universal CSS, HTML injection
- **Email settings** — SMTP configuration with test email
- **Payment keys** — Paystack API keys (set AFTER installation, not during)
- **System update** — pull updates from GitHub directly from admin panel
- **Notifications** — broadcast notifications to all users
- **Dark/light theme toggle** with localStorage persistence

### User Features
- **Dashboard** with earnings, tasks, and bookings overview
- **Profile management** with image upload
- **Messaging system**
- **Realtime notification bell** with AJAX polling
- **Dark/light theme toggle**
- **Submit image proof** for completed tasks

### Public Features
- Browse tasks, gigs, and marketplace listings
- Beautiful blue/white theme throughout
- Professional error pages (404, 500, 419, 503)
- Responsive design with dark mode support

### Email Templates
- Beautiful custom blue/white HTML email template
- Email verification (OTP)
- Password reset code
- Password changed confirmation

## Technology Stack

- **Backend:** Laravel 11+ / PHP 8.3
- **Frontend:** Tailwind CSS (CDN), Alpine.js, custom CSS
- **Auth:** Dual system — JWT (REST API) + Session (Blade web)
- **Database:** MySQL (production) / SQLite (testing)
- **Payments:** Paystack
- **Email:** SMTP

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/primemini016-lang/Airtrendmedia.git
   cd Airtrendmedia
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Set up database in `.env`:**
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=miniworkers
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders:**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Set permissions:**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

7. **Or use the web installer:** Navigate to your site and follow the installation wizard.
   - Note: Payment API keys are NOT set during installation. Configure them after install from **Admin → Settings → Payment Keys**.

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

### System Updates
The admin panel includes a GitHub auto-update system. Navigate to **System → System Update** to pull the latest changes from the repository.

## License

This project is proprietary software developed for AirtrendMedia.
