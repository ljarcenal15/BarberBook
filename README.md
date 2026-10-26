# BarberBook

A PHP and MySQL barbershop appointment manager with separate **Sessions** (appointments) and **Transactions** (payments) areas.

## Features

- Dashboard with appointment counts and recent sessions
- Public customer booking page at `book.php`; customers do not need a staff account
- Create, search, edit, and manage appointments
- Record partial or full payments against a session
- Block payments above the remaining service balance
- Separate payment totals and transaction history
- Responsive shared interface for desktop and mobile

## Requirements

- XAMPP with Apache, PHP 8.0 or later, and MySQL/MariaDB
- Existing `barberbook_db` database with `customer`, `barber`, `service`, and `appointment` tables
- Git for publishing to GitHub

For a fresh deployment, create an empty MySQL/MariaDB database and import `database/schema.sql` in phpMyAdmin. It creates the application tables and sample barber/service catalog, but contains no customer data, bookings, accounts, or payments. The Transactions page can also create its payment ledger automatically when first opened.

The first visit to `register.php` creates the shop's initial Admin account. After setup, public registration is disabled and users must sign in. Admins can create Staff accounts from **Staff Accounts**. Staff can manage sessions but cannot access payment transactions or account management. Passwords are stored as hashes, not plaintext. Existing accounts are assigned the Admin role the first time the updated auth code runs, and active sessions receive the role automatically.

## Run locally

1. Place the project folder in `C:\xampp\htdocs\MIDTERM`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. For local XAMPP, the default connection is `localhost`, `barberbook_db`, user `root`, and an empty password. For hosting, set the environment variables listed in `.env.example`; `APP_ENV=production` requires all database credentials to be present.
4. Open `http://localhost/MIDTERM/`.
5. Customers can book at `http://localhost/MIDTERM/book.php` without signing in.
6. Open **Transactions** to initialize its payment ledger.

Do not use the sample local database credentials on a public host. Configure production credentials outside version control and use a dedicated database user.

## Sessions and transactions

Sessions are appointments stored in the existing `appointment` table. Transactions are individual payments stored in `payment_transactions` and linked to a session by `AppointmentID`. A session may have multiple partial payments; the total cannot exceed the service price.

For a demo install, first import `database/schema.sql`, then import `database/demo_transactions.sql` using phpMyAdmin. It adds three clearly labeled demo Sessions and three full-payment Transactions. Use only a disposable/test database; never import demo data into production or a database containing real customers.

## Publish to GitHub

Create a **public** repository on GitHub, then run these commands from this folder after installing Git and configuring your GitHub identity:

```powershell
git init
git add .
git commit -m "Prepare BarberBook sessions and transactions"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPOSITORY.git
git push -u origin main
```

Replace the remote URL with your repository URL. To invite `@carljoshy` to a private repository instead, use **Settings → Collaborators** on GitHub.

## Deploy

Deploy the PHP files to an Apache/PHP host with MySQL/MariaDB, create an empty database, import `database/schema.sql`, and set the production environment variables from `.env.example` in the hosting control panel. Point the domain/document root at the project root; the dashboard no longer assumes the local `/MIDTERM/` path. The deployed URL depends on the hosting provider and domain. Do not publish a local `localhost` URL as a deployed link. The included `.htaccess` files disable directory listings and block direct access to SQL/Markdown setup files; keep Apache `AllowOverride` enabled for these protections.