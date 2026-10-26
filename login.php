<?php
require_once __DIR__ . '/classes/db.php';
require_once __DIR__ . '/classes/Auth.php';

$database = new Database();
$auth = new Auth($database->getConnection());
if ($auth->currentUser() !== null) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$auth->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your sign-in form expired. Refresh the page and try again.';
    } elseif (!$auth->login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        $error = 'Email or password is incorrect.';
    } else {
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - BarberBook</title>
    <link rel="stylesheet" href="includes/style.css?v=7">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-intro">
            <a class="auth-brand" href="login.php"><span class="brand-icon">✂</span><span>BarberBook</span></a>
            <div class="auth-intro-copy">
                <span class="section-label">BARBERSHOP MANAGEMENT</span>
                <h1>Your shop,<br>in good order.</h1>
                <p>Keep sessions moving and payments accounted for.</p>
            </div>
            <span class="auth-issue">SESSION MANAGEMENT · PAYMENT TRACKING</span>
        </section>
        <section class="auth-panel">
            <div class="auth-form-wrap">
                <span class="breadcrumb">WELCOME BACK</span>
                <h2>Sign in</h2>
                <p class="auth-helper">Use your BarberBook account to continue.</p>
                <a class="customer-booking-link" href="book.php">Are you a customer? Book a session <span>→</span></a>
                <?php if (isset($_GET['setup'])): ?><div class="alert-success auth-notice">Account setup is complete. Sign in to continue.</div><?php endif; ?>
                <?php if ($error !== ''): ?><div class="alert-error" role="alert"><?= htmlspecialchars($error); ?></div><?php endif; ?>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->csrfToken()); ?>">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    <button class="btn" type="submit">Sign in</button>
                </form>
                <p class="auth-switch">First time here? <a href="register.php">Set up the owner account</a></p>
            </div>
        </section>
    </main>
</body>
</html>