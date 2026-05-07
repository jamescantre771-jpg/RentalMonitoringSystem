# Rental Monitoring System — Setup Guide

## Requirements
- XAMPP (Apache + MySQL + PHP 8+)
- phpMyAdmin (included in XAMPP)

---

## Step 1 — Copy Files
Copy the entire `rental_system/` folder into:
```
C:\xampp\htdocs\rental_system\
```

## Step 2 — Import the Database
1. Open your browser → go to http://localhost/phpmyadmin
2. Click **Import** tab (top menu)
3. Click **Choose File** → select `rental_system/database.sql`
4. Scroll down → click **Go**
5. You should see: `rental_monitoring` database created with tables: `users`, `rentals`, `payments`

## Step 3 — Configure Database (if needed)
Open `api/config.php` and update:
```php
define('DB_HOST', 'localhost');   // usually stays localhost
define('DB_NAME', 'rental_monitoring');
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password (empty by default in XAMPP)
```

## Step 4 — Start XAMPP
- Open XAMPP Control Panel
- Click **Start** next to **Apache**
- Click **Start** next to **MySQL**

## Step 5 — Open the System
Go to: http://localhost/rental_system/

### Default Admin Login
| Field    | Value              |
|----------|--------------------|
| Email    | admin@rental.com   |
| Password | Admin@1234         |

---

## File Structure
```
rental_system/
│
├── index.html          ← Login & Signup page
├── dashboard.php       ← Main dashboard (protected)
├── database.sql        ← Import this into phpMyAdmin
│
└── api/
    ├── config.php      ← Database connection + session helpers
    ├── auth.php        ← Login / Signup / Logout API
    ├── rentals.php     ← Full CRUD API for rental units
    └── payments.php    ← Payments API
```

## API Endpoints
| Method | URL                          | Action              |
|--------|------------------------------|---------------------|
| POST   | api/auth.php?action=signup   | Register user       |
| POST   | api/auth.php?action=login    | Login               |
| POST   | api/auth.php?action=logout   | Logout              |
| GET    | api/auth.php?action=check    | Check session       |
| GET    | api/rentals.php              | List rentals        |
| GET    | api/rentals.php?id=N         | Get one rental      |
| POST   | api/rentals.php              | Create rental       |
| PUT    | api/rentals.php?id=N         | Update rental       |
| DELETE | api/rentals.php?id=N         | Delete rental       |
| GET    | api/payments.php?rental_id=N | List payments       |
| POST   | api/payments.php             | Record payment      |
| DELETE | api/payments.php?id=N        | Delete payment      |

## Troubleshooting
- **"Database connection failed"** → Make sure MySQL is running in XAMPP and DB_PASS is correct
- **"Unauthorized"** → Session expired, just log in again
- **Blank page** → Check Apache error log at `C:\xampp\logs\error.log`
- **phpMyAdmin won't open** → Make sure port 80 isn't blocked by another app (Skype, IIS)
