<?php
require_once __DIR__ . '/classes/db.php';
require_once __DIR__ . '/classes/Auth.php';

$database = new Database();
$auth = new Auth($database->getConnection());
if ($auth->currentUser() !== null) {
    header('Location: index.php');
    exit;
}
if (!$auth->canRegister()) {
    header('Location: login.php?setup=complete');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$auth->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your account form expired. Refresh the page and try again.';
    } else {
        $error = $auth->register(
            $_POST['full_name'] ?? '',
            $_POST['email'] ?? '',
            $_POST['password'] ?? ''
        );
        if ($error === '') {
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - BarberBook</title>
    <link rel="stylesheet" href="includes/style.css?v=7">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-intro">
            <a class="auth-brand" href="register.php"><span class="brand-icon">✂</span><span>BarberBook</span></a>
            <div class="auth-intro-copy">
                <span class="section-label">A CLEANER WAY TO RUN YOUR SHOP</span>
                <h1>Start with<br>a fresh page.</h1>
                <p>Create your account to manage sessions and payment records.</p>
            </div>
            <span class="auth-issue">SESSION MANAGEMENT · PAYMENT TRACKING</span>
        </section>
        <section class="auth-panel">
            <div class="auth-form-wrap">
                <span class="breadcrumb">GET STARTED</span>
                <h2>Create account</h2>
                <p class="auth-helper">Set up your BarberBook sign-in.</p>
                <?php if ($error !== ''): ?><div class="alert-error" role="alert"><?= htmlspecialchars($error); ?></div><?php endif; ?>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->csrfToken()); ?>">
                    <label for="full_name">Full name</label>
                    <input id="full_name" name="full_name" type="text" autocomplete="name" maxlength="120" value="<?= htmlspecialchars($_POST['full_name'] ?? ''); ?>" required autofocus>
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    <label for="password">Password <span class="auth-hint">At least 8 characters</span></label>
                    <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                    <button class="btn" type="submit">Create account</button>
                </form>
                <p class="auth-switch">Already have an account? <a href="login.php">Sign in</a></p>
            </div>
        </section>
    </main>
</body>
</html>