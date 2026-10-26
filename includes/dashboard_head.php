<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BarberBook Dashboard</title>
    <link rel="stylesheet" href="includes/style.css?v=7">
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">✂</div>
        <div>
            <div class="brand-name">BarberBook</div>
            <div class="brand-subtitle">BARBERSHOP SYSTEM</div>
        </div>
    </div>

    <div class="menu-label">MENU</div>

    <nav>
        <a href="index.php" class="nav-link active">
            <span class="nav-icon">▦</span>
            Dashboard
        </a>
        <a href="view_appointments.php" class="nav-link">
            <span class="nav-icon">◫</span>
            Sessions
        </a>
        <a href="backend/add_appointment.php" class="nav-link">
            <span class="nav-icon">＋</span>
            Book Session
        </a>
        <a href="backend/search_appointments.php" class="nav-link">
            <span class="nav-icon">⌕</span>
            Search
        </a>
        <?php if ($currentUser['role'] === 'admin'): ?>
            <a href="transactions.php" class="nav-link"><span class="nav-icon">₱</span>Transactions</a>
            <a href="users.php" class="nav-link"><span class="nav-icon">♙</span>Staff Accounts</a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-account">
        <strong><?= htmlspecialchars($currentUser['name']); ?> · <?= htmlspecialchars(ucfirst($currentUser['role'])); ?></strong>
        <form action="logout.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->csrfToken()); ?>">
            <button type="submit">Sign out</button>
        </form>
    </div>

    <div class="sidebar-bottom">
        <div class="scissors">✂</div>
        <strong>BarberBook</strong>
        <small>Manage your appointments</small>
    </div>
</aside>

<main class="main-content">
