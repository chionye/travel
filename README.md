# SkyWave Travel — Flight & Hotel Booking App

A full-featured flight and hotel booking web application built with PHP, MySQL, jQuery, and TailwindCSS. Payments via cryptocurrency or bank transfer with admin-controlled wallet/bank details.

---

## Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled
- File upload support enabled in PHP

All of the above are standard on any cPanel shared hosting.

---

## cPanel Installation (Step-by-Step)

### Step 1 — Upload Files

1. Zip the entire project folder
2. Log into cPanel → **File Manager**
3. Navigate to `public_html` (or your subdirectory)
4. Upload the zip file and **Extract** it
5. Make sure all files are directly in `public_html/` (or your chosen folder)

### Step 2 — Create Database

1. In cPanel → **MySQL Databases**
2. Create a new database (e.g. `mysite_travel`)
3. Create a new database user with a strong password
4. Add the user to the database with **All Privileges**
5. Note down: database name, username, password, host (usually `localhost`)

### Step 3 — Set Folder Permissions

In cPanel File Manager, set these permissions:

```
uploads/               → 755
uploads/payment_proofs/ → 755  
uploads/logos/         → 755
includes/              → 755
```

To set permissions: right-click folder → **Change Permissions**

### Step 4 — Run the Installer

Visit: `https://yourdomain.com/install/`

1. **Step 1** — Check system requirements (all should be green)
2. **Step 2** — Enter your database credentials, site URL, and create your admin account
3. **Step 3** — Installation complete!

> ⚠️ **Security:** After installation, delete or rename the `install/` folder via File Manager.

### Step 5 — Configure Your Site

Log into the Admin Panel: `https://yourdomain.com/admin/`

Use the credentials you set during installation.

Go to **Settings** and configure:
- **Site Info** — website name, logo, contact details, social media
- **Payment Methods** — crypto wallet addresses, bank transfer details
- **Admin Account** — change your login credentials

### Step 6 — Add Flights & Hotels

- Go to **Admin → Flights → Add New Flight** to add your first flight
- Go to **Admin → Hotels → Add New Hotel** to add your first hotel
- All data is managed 100% from the admin panel — no code editing needed!

---

## Directory Structure

```
/
├── index.php              # Home page with search
├── flights.php            # Flight search results
├── hotels.php             # Hotel search results
├── book.php               # Booking form
├── payment.php            # Payment submission
├── booking-success.php    # Booking confirmation
├── dashboard.php          # User dashboard
├── login.php              # User login
├── register.php           # User registration
├── logout.php
├── itinerary.php          # Booking itinerary view
├── admin/
│   ├── login.php          # Admin login
│   ├── index.php          # Admin dashboard
│   ├── flights.php        # Manage flights (add/edit/delete)
│   ├── hotels.php         # Manage hotels (add/edit/delete)
│   ├── bookings.php       # View all bookings
│   ├── booking-view.php   # Approve/decline payments
│   ├── settings.php       # Site & payment settings
│   └── includes/
│       └── header.php     # Admin layout
├── includes/
│   ├── config.php         # Database & site config (auto-generated)
│   ├── db.php             # Database connection
│   ├── functions.php      # Helper functions
│   ├── auth.php           # Authentication
│   ├── email.php          # Email functions
│   ├── header.php         # Site header
│   └── footer.php         # Site footer
├── assets/
│   ├── css/style.css
│   └── js/main.js
├── uploads/
│   ├── payment_proofs/    # User payment proof uploads
│   └── logos/             # Site logo uploads
└── install/
    ├── index.php          # Setup wizard
    └── schema.sql         # Database schema
```

---

## Admin Features

| Feature | Description |
|---|---|
| **Dashboard** | Stats overview — bookings, revenue, pending payments |
| **Flights** | Add, edit, delete, activate/deactivate flights |
| **Hotels** | Add, edit, delete, activate/deactivate hotels |
| **Bookings** | View all bookings with filters (type, payment status) |
| **Payment Approval** | View payment proof image, approve or decline with email notification |
| **Settings → Site Info** | Name, logo, contact emails/phones, social media links |
| **Settings → Payment** | BTC/ETH/USDT wallets, bank transfer details, email from address |
| **Settings → Admin Account** | Change username, email and password |

---

## User Features

| Feature | Description |
|---|---|
| **Search** | Search flights by route/date/class, hotels by city/stars/price |
| **Book** | Fill passenger details, contact info, special requests |
| **Pay** | Choose crypto or bank transfer, upload payment proof |
| **Dashboard** | View all bookings with status, payment status |
| **Itinerary** | Full printable itinerary for each booking |
| **Email Notifications** | Received when payment proof submitted, approved, or declined |

---

## Email Notifications

Emails are sent using PHP `mail()` which works on all cPanel hosting (configured via cPanel's built-in mail server).

Set your **From Email** in Admin → Settings → Payment Methods.

Emails sent automatically:
1. **Payment Received** — when user submits payment proof
2. **Booking Confirmed** — when admin approves payment
3. **Payment Declined** — when admin declines payment (with reason)

---

## Default Admin Login

Set during installation at `https://yourdomain.com/install/`

Change credentials anytime at: **Admin → Settings → Admin Account**

---

## Security

- CSRF protection on all forms
- Password hashing with `password_hash()` (bcrypt)
- SQL injection prevention via PDO prepared statements
- XSS prevention via `htmlspecialchars()` throughout
- PHP execution blocked in uploads directory
- Directory listing disabled via `.htaccess`
- Config file protected from direct access

---

## Troubleshooting

**"Database Connection Failed"** — Check `includes/config.php` credentials match your cPanel database

**Upload not working** — Set `uploads/` and subdirectories to permission `755` in File Manager

**Emails not sending** — Ensure your cPanel hosting has `sendmail` configured (most do). Set the From Email in Settings.

**`.htaccess` not working** — Enable `mod_rewrite` in cPanel → Apache Handlers, or contact your host

**Blank white page** — Enable PHP error display temporarily by adding `ini_set('display_errors', 1);` at the top of `includes/config.php`
