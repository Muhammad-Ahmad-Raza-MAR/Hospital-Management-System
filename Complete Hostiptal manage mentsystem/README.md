# MediCore HMS — Setup Guide

## Files Included
| File | Purpose |
|------|---------|
| `index.html` | Main frontend (Bootstrap 5 + jQuery + custom UI) |
| `pro.php` | AJAX backend — handles all form submissions, returns JSON |
| `schema.sql` | MySQL database schema (run this first) |

---

## Quick Setup

### 1. Database
```sql
-- In phpMyAdmin or MySQL CLI:
source schema.sql;
-- OR import schema.sql via phpMyAdmin → Import
```

### 2. Configure Database (pro.php)
Edit the top of `pro.php`:
```php
define('DB_HOST', 'localhost');   // your DB host
define('DB_USER', 'root');        // your DB username
define('DB_PASS', '');            // your DB password
define('DB_NAME', 'hosss');       // your DB name
```

### 3. Deploy
Place all 3 files in your web server root (e.g. `htdocs/hospital/`):
```
htdocs/
  hospital/
    index.html
    pro.php
    schema.sql   ← (optional, just for reference)
```

### 4. Open
```
http://localhost/hospital/index.html
```

---

## Features
- **13 data entry forms** across 7 management sections
- **AJAX submissions** — no page reloads, instant feedback
- **Toast notifications** — success/error/warning banners
- **Live validation** — real-time field feedback
- **Responsive sidebar** — mobile-friendly collapsible nav
- **Bootstrap 5** + **jQuery 3.7** + **Bootstrap Icons**
- **Prepared statements** — SQL injection protected
- **JSON API** — clean request/response format

## Requirements
- PHP 7.4+ with MySQLi extension
- MySQL 5.7+ or MariaDB 10.3+
- Any web server (Apache/Nginx/XAMPP/WAMP)
