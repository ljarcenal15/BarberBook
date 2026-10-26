# BarberBook Deployment Guide

The source repository is public, but GitHub does not run PHP. Deploy this project to a PHP/MySQL host such as cPanel shared hosting.

## 1. Create hosting and a domain

Choose a hosting plan that supports PHP 8+, MySQL/MariaDB, PDO MySQL, `mbstring`, and Apache `.htaccess`. Connect a domain or use the temporary domain provided by the host. In cPanel, use **Domains** to confirm the document root for the site.

## 2. Create the database

In cPanel, open **MySQL Databases** and:

1. Create a new database.
2. Create a new database user with a unique, strong password.
3. Add the user to the database and grant the privileges required by the app.
4. Open **phpMyAdmin**, select the new empty database, choose **Import**, and import `database/schema.sql` from this project.

Use only the clean schema for a real deployment. Do not import `database/demo_transactions.sql`; that file inserts fake customers, sessions, and payments for testing.

## 3. Upload the application

1. Extract the deployment ZIP on your computer.
2. In cPanel **File Manager**, open the domain's document root (often `public_html`).
3. Create a folder named `barberbook` if you want the site at `/barberbook/`.
4. Upload the project files and folders into that folder, preserving the structure. `index.php`, `book.php`, `.htaccess`, and `classes/` should be directly inside the application folder.
5. Keep the provided `.htaccess` files. Apache overrides must be enabled by the host.

The booking page will be `https://YOUR-DOMAIN/barberbook/book.php` if deployed in that subfolder, or `https://YOUR-DOMAIN/book.php` if deployed at the document root.

## 4. Configure production database access

Never put production credentials in GitHub. In cPanel File Manager, edit the server copy of the application's root `.htaccess` and append these lines using the values shown in **MySQL Databases**:

```apache
SetEnv APP_ENV production
SetEnv DB_HOST localhost
SetEnv DB_NAME your_cpanelprefix_database
SetEnv DB_USER your_cpanelprefix_user
SetEnv DB_PASS "your-unique-database-password"
```

Use the exact database host supplied by your provider; it may not be `localhost`. If the provider offers a secure environment-variable/settings panel, use that instead. Keep these real values only on the host; do not commit or upload your edited production `.htaccess` back to GitHub. If `SetEnv` is not allowed, ask the host how to set PHP environment variables or request help configuring this file securely.

## 5. Initialize and test

1. Open `https://YOUR-DOMAIN/barberbook/register.php` once and create the Admin account. Public owner registration closes after the first account.
2. Sign in and create Staff accounts if needed.
3. Open **Transactions** to initialize its ledger table.
4. Test a customer booking at `/barberbook/book.php` with a future slot, then confirm it appears as Pending in **Sessions**.
5. Verify Admin-only Transactions and Staff Accounts require an Admin account.
6. Verify the site uses HTTPS and that requesting `/barberbook/database/schema.sql` is denied.

If the app shows “Database connection failed,” verify the hosting database settings and that `pdo_mysql` is enabled. Do not enable detailed PHP errors on a public production site.

## Security notes

- Use a dedicated database user, not a hosting administrator account.
- Use HTTPS and a strong Admin password.
- Keep production credentials out of the repository and deployment ZIP.
- Never use the demo seed on the production database.
- Back up the database through the host's private backup tools.