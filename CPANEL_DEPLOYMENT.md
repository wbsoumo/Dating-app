# cPanel Shared Hosting & Production Deployment Guide

This document provides a step-by-step guide for deploying the **DilSe Match Dating Platform Backend & AdminLTE 3 Panel** to any standard **cPanel Shared Hosting** or VPS environment.

---

## 1. Hosting Requirements Checklist

| Requirement | Minimum Supported | Recommended |
|---|---|---|
| **PHP Version** | `8.2+` | `8.2` or `8.3` |
| **MySQL / MariaDB** | `MySQL 5.7+` / `MariaDB 10.3+` | `MySQL 8.0` |
| **PHP Extensions** | `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PCRE`, `PDO`, `pdo_mysql`, `Tokenizer`, `XML` | Default on modern cPanel |
| **SSL Certificate** | Required for API HTTPS & Razorpay Webhooks | Let's Encrypt (cPanel AutoSSL) |

---

## 2. Directory Layout & Public Root Configuration

In cPanel shared hosting, the web-accessible root directory is typically `public_html/`. Because Laravel places its public entry point in `public/index.php`, choose **Method A** (recommended) or **Method B**.

### Method A: Point Subdomain or Addon Domain to `/public` (Recommended)
1. In cPanel, go to **Domains** or **Subdomains**.
2. When creating or editing your domain (e.g. `api.yourdomain.com` or `backend.yourdomain.com`), set **Document Root** to:
   ```text
   /home/yourusername/dating-backend/public
   ```
3. Upload all project files into `/home/yourusername/dating-backend`.

### Method B: Root Directory `.htaccess` Redirect
If your main domain must point directly to `public_html/`:
1. Upload all project files into `public_html/`.
2. Create or place the following `.htaccess` file directly inside `public_html/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## 3. Database Setup via cPanel

1. In cPanel, open **MySQL Database Wizard**.
2. Create a new database: e.g. `cpaneluser_datingapp`.
3. Create a new MySQL user: e.g. `cpaneluser_dbuser` with a secure password.
4. Assign the user to the database with **ALL PRIVILEGES**.

### Option 1: Automated Web Installer (Fastest)
1. Open your browser and navigate to:
   ```text
   https://yourdomain.com/install
   ```
2. The installation wizard will:
   - Check PHP version and extension compatibility.
   - Verify directory permissions.
   - Test your MySQL credentials and write them to `.env`.
   - Automatically run all migrations and master seeders.
   - Create your Super Administrator account.
   - Optionally seed ~2,020 Indian demo profiles with realistic coordinates and photos.
   - Automatically lock itself after completion for security (`storage/installed.lock`).

### Option 2: phpMyAdmin Direct SQL Import
If you prefer importing directly:
1. Open **phpMyAdmin** from cPanel.
2. Select your newly created database.
3. Click the **Import** tab.
4. Choose the file: `database/dating_app.sql`.
5. Click **Go**. All tables, constraints, master settings, plans, and demo accounts will import instantly.
6. Copy `.env.example` to `.env` and fill in your DB credentials:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cpaneluser_datingapp
   DB_USERNAME=cpaneluser_dbuser
   DB_PASSWORD=YourSecurePasswordHere
   ```

---

## 4. File Permissions

In cPanel **File Manager** or via SSH, ensure the following directories have write permissions (`0755` for directories, `0644` for files):

```bash
chmod -R 775 storage bootstrap/cache public/uploads
```

---

## 5. Razorpay Payment Gateway Setup

1. Log in to the AdminLTE 3 Admin Panel at:
   ```text
   https://yourdomain.com/admin/login
   ```
   - **Email:** `admin@datingapp.com`
   - **Password:** `Admin@123456`
2. Navigate to **System Settings > Razorpay Gateway**.
3. Configure the following:
   - **Mode:** Select `Test` for sandbox or `Live` for real payments.
   - **Razorpay Key ID:** `rzp_live_...` (or `rzp_test_...`)
   - **Razorpay Key Secret:** Your secret key from Razorpay Dashboard.
   - **Razorpay Webhook Secret:** Create a webhook secret in Razorpay Dashboard.
4. In your [Razorpay Dashboard](https://dashboard.razorpay.com/) > **Settings > Webhooks**:
   - Add Webhook URL: `https://yourdomain.com/api/v1/payments/webhook`
   - Active Events:
     - `payment.captured`
     - `payment.failed`
     - `order.paid`
     - `refund.processed`
   - Secret: Enter the same secret configured in the Admin Panel.

---

## 6. Scheduled Tasks (Cron Jobs) in cPanel

To automatically handle daily swipe limit resets, subscription expirations, and background maintenance:

1. In cPanel, navigate to **Cron Jobs**.
2. Select Interval: **Once Per Minute** (`* * * * *`).
3. Add the following command (replace `/usr/local/bin/php` and path with your server's path):
   ```bash
   /usr/local/bin/php /home/yourusername/dating-backend/artisan schedule:run >> /dev/null 2>&1
   ```

---

## 7. Demo Data Management (Before Production Launch)

1. Open the Admin Panel at `/admin/dashboard`.
2. Click **Demo Data Manager** in the sidebar.
3. Review the demo count (default: 2,020 Indian profiles).
4. When ready to launch to real users, click **Permanently Purge All Demo Profiles** and type `DELETE DEMO DATA` to cleanly wipe sample records while leaving your master configuration intact!

---

## 8. Mobile App Developer Integration Hand-off

Mobile application developers building iOS and Android apps can view complete endpoint contracts, sample JSON requests, response schemas, and copyable cURL commands at:

- **Interactive Web Documentation:** `https://yourdomain.com/api/docs`
- **Postman Collection Download:** `https://yourdomain.com/api/docs/postman`
- **API Base URL:** `https://yourdomain.com/api/v1`
