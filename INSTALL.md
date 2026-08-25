# Airtrendmedia — Installation Guide

## Quick Start (5 Minutes)

### Requirements
- **PHP 8.3+** with extensions: pdo_mysql, mbstring, openssl, curl, gd (or imagick), fileinfo, json
- **MySQL 5.7+ / MariaDB 10.3+**
- A web server (Apache/Nginx) OR you can use `php artisan serve` for quick testing

### Step-by-Step Installation

**1. Upload the script to your server**
Upload the entire `airtrendmedia/` folder to your web root (e.g., `public_html/` or `/var/www/html/`).

**2. Set permissions**
Make these directories writable:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

**3. Create a MySQL database**
Create an empty database and user in MySQL/MariaDB:
```sql
CREATE DATABASE airtrendmedia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'airtrend'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON airtrendmedia.* TO 'airtrend'@'localhost';
FLUSH PRIVILEGES;
```

**4. Point your browser to the installer**
Visit: `http://your-domain.com/install`

The installer will guide you through:
1. **Requirements Check** — verifies PHP version and extensions
2. **Database Configuration** — enter your MySQL credentials (the installer writes the `.env` file and runs all migrations + seeders automatically)
3. **App Settings** — optional email/SMTP configuration
4. **Admin Account** — create your administrator username, email, and password
5. **Finish** — installation complete!

**5. Log in**
- **Admin panel:** `http://your-domain.com/admin/login`
- **User area:** `http://your-domain.com/login`

### Important Notes

- The **APP_KEY and JWT_SECRET are generated automatically** during installation. You do NOT need to run `composer install` or `php artisan key:generate` — everything is pre-packaged.
- The **vendor/ directory is included** in this package, so all PHP dependencies are ready. No Composer required.
- After installation, the `/install` route is disabled (the app writes `storage/app/installed.json`).
- If you need to reinstall, delete `storage/app/installed.json` and visit `/install` again.

### Using PHP Built-in Server (Quick Local Test)

```bash
cd airtrendmedia
php artisan serve
```
Then visit `http://localhost:8000/install`

### Error Screen

The platform uses a branded error screen ("We Couldn't Process Your Request.") instead of showing raw errors. This screen:
- Shows your website logo
- Only appears when a page doesn't exist (404) or a server error occurs (500)
- **Never** shows during form validation (those show inline field errors)
- Can be customized from **Admin Panel → Appearance → Error Screen Content**

### Admin Features

After logging in as admin, you can manage:
- **Dashboard** — site overview and statistics
- **Appearance** — logo, favicon, theme color, error screen content & logo
- **Users** — view, edit, activate, ban users
- **Tasks/Microjobs** — manage the marketplace
- **Ads** — manage advertisements
- **Blog** — posts and categories
- **Social features** — feed, stories, etc.
- **Payment Keys** — configure Paystack
- **Settings** — all site configuration

### Troubleshooting

**"500 Internal Server Error" on install page:**
- Ensure `storage/` and `bootstrap/cache/` are writable
- Check that PHP 8.3+ with required extensions is installed
- The installer auto-generates APP_KEY on first load, so this should not happen

**"Database connection failed":**
- Verify your MySQL credentials are correct
- Ensure the database exists and the user has privileges
- Check MySQL is running

**Blank page / white screen:**
- Check `storage/logs/laravel.log` for errors
- Ensure all PHP extensions are installed
- Set `APP_DEBUG=true` temporarily in `.env` to see errors
