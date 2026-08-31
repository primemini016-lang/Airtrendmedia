# Airtrendmedia — Deployment Guide (Automatic Web Installer)

This guide explains how to get your Airtrendmedia marketplace live on your
domain using the built‑in web installer. **No SSH, no Composer, no terminal
commands are required.** You only need a browser.

---

## What you need before you start

1. **A web hosting account** (shared cPanel hosting, VPS, or any host that
   supports **PHP 8.3 or newer** and **MySQL 5.7+ / MariaDB 10.3+**).
2. **An empty MySQL database.** Create one in your hosting control panel
   (cPanel → MySQL Databases). Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost` or `127.0.0.1`)
   - Database port (usually `3306`)
3. **The `airtrendmedia-complete.zip` file** (this package, which already
   includes the `vendor/` folder — so you do **not** need to run Composer).

---

## Step 1 — Upload the files

1. Log in to your hosting control panel (cPanel, DirectAdmin, Plesk, etc.)
   and open the **File Manager**, **or** connect with an FTP client
   (FileZilla, Cyberduck, WinSCP, etc.).
2. Find your domain's web root folder. This is usually:
   - `public_html/` for cPanel
   - `htdocs/` for some hosts
   - `httpdocs/` for Plesk
   - the folder shown in your domain's "Document Root" setting
3. **Upload `airtrendmedia-complete.zip`** into that web root folder.
4. **Extract** the zip inside the web root (cPanel File Manager: right‑click
   the zip → *Extract*; or use FTP to upload the already‑unzipped folder).
5. After extraction your web root should contain folders like `app/`,
   `public/`, `vendor/`, `resources/`, `database/`, and files like
   `artisan`, `composer.json`, `.env.example`, `.htaccess`.

> **Important (two options for the document root):**
>
> **Option A (recommended):** Set your domain's **Document Root** to the
> `public/` subfolder (e.g. `public_html/public`). This is the most secure
> setup because your `.env`, `app/`, and `database/` files are never web
> accessible. In cPanel → *Domains* → edit the domain → change Document Root
> to `public_html/public` (or wherever you uploaded + `/public`).
>
> **Option B (if you cannot change the document root):** Leave the document
> root pointing at the project root. The bundled root `.htaccess`
> automatically routes all traffic into `public/index.php` and blocks
> access to sensitive folders. This works on almost all Apache hosts with
> `mod_rewrite` enabled.

---

## Step 2 — Set folder permissions (only if the installer complains)

On most hosts the permissions are already correct after extraction. If the
installer reports a "not writable" error, set these folders to **755** (or
**775** if 755 fails) and make sure they are owned by your hosting user:

- `storage/` and all subfolders
- `bootstrap/cache/`
- `.env` (the file, if it exists) — `644`

In cPanel File Manager: right‑click the folder → *Permissions* → set to
`755` and apply recursively. The installer creates the `.env` file for you,
so the **project root folder itself** must also be writable by PHP.

---

## Step 3 — Visit your installer URL

Open your browser and go to:

```
https://airtrendmedia.com/install
```

(replace `airtrendmedia.com` with your actual domain.)

You will see the **Airtrendmedia Installer** page with two sections.

---

## Step 4 — Fill in the form

### Section 1 — Database Details

Enter the database details you created in "What you need before you start":

| Field | Example |
|-------|---------|
| Database Host | `localhost` (or `127.0.0.1`) |
| Database Port | `3306` |
| Database Name | `airtrendmedia` (the DB you created) |
| Database Username | `airtrend_dbuser` |
| Database Password | your DB password |
| App / Site Name | `Airtrendmedia` (or your brand name) |
| App URL | `https://airtrendmedia.com` (your domain, with https://) |

### Section 2 — Admin Account Details

This creates your **super‑administrator** login. Pick a strong password
(minimum 8 characters):

| Field | Example |
|-------|---------|
| Admin Full Name | `John Doe` |
| Admin Username | `admin` (letters, numbers, `_`, `.` only) |
| Admin Email | `you@example.com` |
| Admin Password | a strong password (min 8 chars) |
| Confirm Password | same password again |

---

## Step 5 — Click "Install Now"

The installer will:

1. Test your database connection (instant).
2. Write the `.env` configuration file.
3. Generate the security keys (`APP_KEY` and `JWT_SECRET`).
4. Create **all** database tables (`migrate:fresh`).
5. Seed **default data** — countries, currencies, categories, settings,
   plus demo gigs, listings, tasks, blogs, reviews and 10 sample users so
   your site is not empty.
6. Create your admin account from the form.
7. Link `public/storage` for uploaded files.
8. Write the install lock file and lock the installer.

This takes **a few seconds to about a minute**. The button shows a spinner
while it works — **do not refresh or close the page**.

If something goes wrong, the installer shows a friendly error message and
keeps your entered details so you can fix the issue and click
**Install Now** again.

---

## Step 6 — Done! Log in to your admin panel

When the installer finishes you will see a **"Installation Complete"**
screen with next steps:

1. **Go to your admin panel:** `https://airtrendmedia.com/admin/login`
   and sign in with the admin username + password you just created.
2. **Set your payment keys:** Admin → Settings → Payment Keys → enter your
   Paystack public & secret keys (and/or other gateways you use).
3. **Configure currencies & email:** Admin → Currencies (set your default)
   and Admin → Email Settings (SMTP) so the platform can send mail.

Then visit `https://airtrendmedia.com/` to see your live marketplace!

---

## After installation — security checklist

- [ ] The installer is now **locked**. Visiting `/install` again redirects
      to your homepage. You can optionally delete the
      `app/Http/Controllers/Web/InstallController.php` and its routes for
      extra safety, but this is not required.
- [ ] In Admin → Settings, set `APP_DEBUG=false` (the installer already
      sets this) and review all site settings.
- [ ] Set up a **daily backup** of your database in your hosting panel.
- [ ] Set up **HTTPS/SSL** (Let's Encrypt is free in cPanel) — the App URL
      you entered should use `https://`.

---

## Troubleshooting

### "No application encryption key has been specified" or 500 error on /install
The installer auto‑generates the `APP_KEY`. If you see this, it means the
project root is not writable by PHP. Set the project root folder to `755`
(or `775`) and refresh `/install`. The installer will create `.env` and
the key automatically.

### Database connection failed
- Double‑check the database name, username, password, and host.
- The database user must have **ALL PRIVILEGES** on the database. In
  cPanel → MySQL Databases → add the user to the database with all
  privileges.
- Some hosts use `localhost` for the DB host; others use an IP or a
  hostname like `db123456.hosting.example`. Check your host's DB page.

### Migration failed / "SQLSTATE" errors
- Make sure the database is **empty** (no tables with conflicting names).
  The installer uses `migrate:fresh` which drops existing tables, but some
  hosts restrict `DROP` privileges — grant ALL PRIVILEGES to the DB user.
- MySQL version must be 5.7+ or MariaDB 10.3+.

### Uploaded images / profile pictures appear broken
The `public/storage` symlink may not work on your host. The package
includes a fallback `.htaccess` inside `public/storage/` that rewrites
requests to the real storage folder. If images are still broken, run the
Python helper from your host's terminal (if available):

```bash
python3 airtrendmedia_helper.py symlink
```

Or manually create the symlink / copy `storage/app/public/*` into
`public/storage/`.

### The page shows a blank white screen
- Check that your domain's document root is set to `public/` (Option A) OR
  that the root `.htaccess` is present (Option B) and `mod_rewrite` is
  enabled on your host.
- Make sure `storage/` and `bootstrap/cache/` are writable (`755` or
  `775`).
- Temporarily set `APP_DEBUG=true` in `.env` to see the error, then set it
  back to `false`.

### I want to re‑run the installer
Delete `storage/app/installed.json` and visit `/install` again. This will
wipe and recreate the database, so **back up first** if you have real data.

---

## Python helper (optional, for hosts with terminal/SSH access)

A dependency‑free Python 3 script is included for maintenance tasks. From
the project root:

```bash
python3 airtrendmedia_helper.py all         # full maintenance run
python3 airtrendmedia_helper.py clear-cache # clear + rebuild caches
python3 airtrendmedia_helper.py symlink     # fix the storage symlink
python3 airtrendmedia_helper.py permissions # set safe folder permissions
python3 airtrendmedia_helper.py migrate     # run pending migrations
python3 airtrendmedia_helper.py optimize    # cache config + routes
python3 airtrendmedia_helper.py health      # quick health check
python3 airtrendmedia_helper.py report      # full environment report
```

Run `python3 airtrendmedia_helper.py` with no arguments to see all
options. The script auto‑detects PHP and the project root and works on
shared hosting without any pip installs.

---

That's it — upload, visit `/install`, enter your database and admin
details, and your marketplace is live. 🎉
