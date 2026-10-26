# BarberBook Deployment Guide

The source repository is public, but GitHub does not run PHP. Deploy this project to a PHP/MySQL host such as InfinityFree.

## 1. Create hosting and a domain

In the InfinityFree client area, create/activate a hosting account and use its free subdomain or connect a domain. Confirm the account supports the PHP version required by the project, MySQL/MariaDB, PDO MySQL, `mbstring`, and Apache `.htaccess`.

## 2. Create the database

In the hosting control panel, open **MySQL Databases** and:

1. Create a new database.
2. Create a new database user with a unique, strong password.
3. Add the user to the database and grant the privileges required by the app.
4. Copy the displayed MySQL hostname, full database name (including the account prefix), full database username (including its prefix), and port if one is shown. InfinityFree's MySQL host is not necessarily `localhost`.
5. Open the phpMyAdmin link supplied by the hosting panel, select the new empty database, choose **Import**, and import `database/schema.sql` from this project.

Use only the clean schema for a real deployment. Do not import `database/demo_transactions.sql`; that file inserts fake customers, sessions, and payments for testing.

## 3. Upload the application

1. Extract `BarberBook-deploy.zip` on your computer.
2. In the hosting File Manager or FTP, open the account's `htdocs` folder.
3. Upload the extracted project contents into `htdocs`; `index.php`, `book.php`, `.htaccess`, and `classes/` should be directly inside `htdocs`.
4. Delete the ZIP from `htdocs` after upload so visitors cannot download the source archive.
5. Keep the provided `.htaccess` files. If the host reports a 500 error, check its documentation/support for supported `.htaccess` directives before removing protections.

With files in `htdocs`, the booking page will be `https://YOUR-DOMAIN/book.php` (or the equivalent InfinityFree temporary subdomain URL).

## 4. Configure production database access

Never put production credentials in GitHub, chat, or a public `.htaccess`. The database password included in any shared screenshot/chat/code should be changed in the host control panel before deployment. Create `barberbook-config.php` in the directory **above `htdocs`** using the host's File Manager. Make sure it is outside the public web root. Use the template `barberbook-config.example.php` and replace every placeholder with the values shown by the hosting database page:

```php
<?php
return [
	'environment' => 'production',
	'host' => 'the-mysql-hostname-from-your-host-panel',
	'database' => 'the-full-prefixed-database-name',
	'username' => 'the-full-prefixed-database-user',
	'password' => 'your-new-database-password'
];
```

The app checks for that private config file automatically. If your provider does not allow files outside `htdocs`, create `config.local.php` in the application root instead; the supplied `.htaccess` denies direct browser access to it. Do not upload the real config to GitHub or include it in the deployment ZIP.

## 5. Initialize and test

1. Open `https://YOUR-DOMAIN/register.php` once and create the Admin account. Public owner registration closes after the first account.
2. Sign in and create Staff accounts if needed.
3. Open **Transactions** to initialize its ledger table.
4. Test a customer booking at `/book.php` with a future slot, then confirm it appears as Pending in **Sessions**.
5. Verify Admin-only Transactions and Staff Accounts require an Admin account.
6. Verify the site uses HTTPS and that requesting `/database/schema.sql` is denied.

If the app shows “Database connection failed,” verify the hosting database settings and that `pdo_mysql` is enabled. Do not enable detailed PHP errors on a public production site.

## Security notes

- Use a dedicated database user, not a hosting administrator account.
- Use HTTPS and a strong Admin password.
- Keep production credentials out of the repository and deployment ZIP.
- Never use the demo seed on the production database.
- Back up the database through the host's private backup tools.