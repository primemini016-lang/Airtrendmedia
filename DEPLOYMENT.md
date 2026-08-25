# Deployment Guide — MiniWorkers / AirtrendMedia

## Quick Start

### Option 1: Git Clone (Recommended)

```bash
# Clone the repository
git clone https://github.com/primemini016-lang/Airtrendmedia.git
cd Airtrendmedia

# Install dependencies
composer install --optimize-autoloader --no-dev

# Configure environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Set permissions
chmod -R 775 storage bootstrap/cache

# Configure your web server to point to /public
```

Then visit your domain in a browser and follow the installation wizard.

### Option 2: Upload ZIP

1. Download `miniworkers-full-deploy.zip` (includes vendor dependencies)
2. Extract to your server's web root
3. Copy `.env.example` to `.env`
4. Run `php artisan key:generate` and `php artisan jwt:secret`
5. Set permissions: `chmod -R 775 storage bootstrap/cache`
6. Visit your domain to use the web installer

## Web Installer Steps

The installer wizard will guide you through:

1. **Requirements check** — Verifies PHP 8.3+, extensions, and writable directories
2. **Database setup** — Enter your MySQL connection details and app URL
3. **Email settings** — Configure SMTP for sending verification emails
   - ⚠️ **Payment API keys are NOT collected here** — they are set after installation
4. **Admin account** — Create your super admin user
5. **Finish** — Installation complete, you can now log in

## Post-Installation Configuration

### 1. Set Payment API Keys

After installation, log in to your Admin Dashboard and navigate to:
**Settings → Payment Keys**

Enter your Paystack:
- Public Key (`pk_test_...` or `pk_live_...`)
- Secret Key (`sk_test_...` or `sk_live_...`)
- Base URL (default: `https://api.paystack.co`)
- Callback URL (e.g., `https://airtrendmedia.com/wallet/deposit/verify`)

### 2. Configure Email (SMTP)

Navigate to: **Settings → Email Settings**

Enter your SMTP details:
- Mail Host (e.g., `smtp.gmail.com`)
- Mail Port (e.g., `587`)
- Username and Password
- From Address and Name

**Anti-spam tips:**
- Use a dedicated email sending service (Mailgun, SendGrid, Amazon SES) for high volume
- Set up SPF, DKIM, and DMARC records on your domain
- Use a consistent From address
- Avoid spam-trigger words in email subjects

### 3. Customize Appearance

Navigate to: **Settings → Appearance**

- Upload your logo and favicon
- Set primary and accent theme colors
- Add custom CSS in the Universal CSS field
- Add HTML code injection (header, footer, body, sidebar)

### 4. Create Categories

Navigate to: **Categories**

- Create task categories with social media icons
- Set custom colors for each category
- Configure default pricing and minimum workers

### 5. Manage Ads

Navigate to: **Settings → Ads**

- Create ads at various positions (header top/bottom, footer, sidebar)
- Choose ad type: image, HTML, or text
- Schedule ads with start/end dates

## Web Server Configuration

### Nginx

```nginx
server {
    listen 80;
    server_name airtrendmedia.com www.airtrendmedia.com;
    root /var/www/airtrendmedia/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache

Ensure `mod_rewrite` is enabled and the `.htaccess` file in `/public` handles routing.

## Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_URL=https://airtrendmedia.com` in `.env`
- [ ] Configure SSL/HTTPS certificate
- [ ] Set up payment API keys (Paystack)
- [ ] Configure SMTP email settings
- [ ] Upload logo and favicon
- [ ] Create task categories
- [ ] Set up cron for queue workers: `* * * * * cd /var/www/airtrendmedia && php artisan schedule:run >> /dev/null 2>&1`
- [ ] Run `php artisan config:cache` and `php artisan route:cache`
- [ ] Set proper file permissions on `storage/` and `bootstrap/cache/`

## System Updates

To update the application from the admin panel:
1. Navigate to **System → System Update**
2. Enter the branch name (default: `main`)
3. Click "Run Update"

This will pull the latest code from GitHub and run migrations.

## Security Notes

- The `.env` file contains sensitive data — ensure it's not publicly accessible
- Never commit `.env` to version control (it's in `.gitignore`)
- Regularly update dependencies with `composer update`
- The dual auth system uses JWT for API and sessions for web — both are secured
- CSRF tokens protect all form submissions
- User passwords are hashed with bcrypt

## Troubleshooting

### 500 Error on Login
If users get a 500 error when logging in, check that all web controllers use `Auth::guard('web')` explicitly. The default guard is `user` (JWT) which doesn't support session-based authentication.

### Email Not Sending
1. Verify SMTP credentials in **Settings → Email Settings**
2. Check that the mail server allows connections from your server IP
3. Test with the "Send Test Email" button in email settings
4. Check `storage/logs/laravel.log` for mail errors

### Payment Not Working
1. Verify Paystack keys are set in **Settings → Payment Keys**
2. Ensure the callback URL matches your domain
3. Check that Paystack webhook is configured (if using webhooks)
