<?php
require_once __DIR__ . '/includes/require_login.php';
require_role('admin');

$error = '';
$success = isset($_GET['created']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$auth->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your form expired. Refresh the page and try again.';
    } else {
        $error = $auth->createStaff(
            $_POST['full_name'] ?? '',
            $_POST['email'] ?? '',
            $_POST['password'] ?? ''
        );
        if ($error === '') {
            header('Location: users.php?created=1');
            exit;
        }
    }
}

$users = $auth->getUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Accounts - BarberBook</title>
    <link rel="stylesheet" href="includes/style.css?v=7">
</head>
<body>
<aside class="sidebar">
    <div class="brand"><div class="brand-icon">✂</div><div><div class="brand-name">BarberBook</div><div class="brand-subtitle">BARBERSHOP SYSTEM</div></div></div>
    <div class="menu-label">MENU</div>
    <nav>
        <a href="index.php" class="nav-link"><span class="nav-icon">▦</span>Dashboard</a>
        <a href="view_appointments.php" class="nav-link"><span class="nav-icon">◫</span>Sessions</a>
        <a href="backend/add_appointment.php" class="nav-link"><span class="nav-icon">＋</span>Book Session</a>
        <a href="backend/search_appointments.php" class="nav-link"><span class="nav-icon">⌕</span>Search</a>
        <a href="transactions.php" class="nav-link"><span class="nav-icon">₱</span>Transactions</a>
        <a href="users.php" class="nav-link active"><span class="nav-icon">♙</span>Staff Accounts</a>
    </nav>
    <div class="sidebar-account">
        <strong><?= htmlspecialchars($currentUser['name']); ?> · Admin</strong>
        <form action="logout.php" method="POST"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->csrfToken()); ?>"><button type="submit">Sign out</button></form>
    </div>
    <div class="sidebar-bottom"><div class="scissors">✂</div><strong>BarberBook</strong><small>Owner controls</small></div>
</aside>

<main class="main-content">
    <header class="top-header">
        <div><div class="breadcrumb">HOME / STAFF ACCOUNTS</div><h1>Staff Accounts</h1><p>Create staff logins for session management.</p></div>
    </header>
    <section class="appointment-card form-card staff-form-card">
        <div class="card-header"><div><div class="section-label">TEAM ACCESS</div><h2>Create a staff account</h2></div></div>
        <?php if ($error !== ''): ?><div class="alert-error" role="alert"><?= htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert-success">Staff account created.</div><?php endif; ?>
        <form method="POST" class="form-grid staff-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->csrfToken()); ?>">
            <div class="form-group"><label for="full_name">Full name</label><input id="full_name" name="full_name" maxlength="120" autocomplete="name" required></div>
            <div class="form-group"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" required></div>
            <div class="form-group"><label for="password">Temporary password</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required></div>
            <div class="form-actions full"><button class="btn" type="submit">Create Staff Account</button></div>
        </form>
        <p class="role-note">Staff can manage sessions. Only admins can view payment transactions and staff accounts.</p>
    </section>
    <section class="appointment-card transaction-history">
        <div class="card-header"><div><div class="section-label">ACCESS DIRECTORY</div><h2>Team members</h2></div></div>
        <div class="table-wrapper"><table><thead><tr><th>NAME</th><th>EMAIL</th><th>ROLE</th><th>JOINED</th></tr></thead><tbody>
        <?php foreach ($users as $user): ?>
            <tr><td><strong><?= htmlspecialchars($user['FullName']); ?></strong></td><td><?= htmlspecialchars($user['Email']); ?></td><td><span class="status <?= $user['Role'] === 'admin' ? 'confirmed' : 'pending'; ?>"><?= htmlspecialchars(ucfirst($user['Role'])); ?></span></td><td><?= htmlspecialchars(date('M j, Y', strtotime($user['CreatedAt']))); ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    </section>
    <footer><strong>BarberBook</strong> &nbsp; • &nbsp; Owner controls</footer>
</main>
</body>
</html>