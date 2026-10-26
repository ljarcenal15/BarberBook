<?php
require_once __DIR__ . '/includes/require_login.php';
require_role('admin');
require_once __DIR__ . '/classes/db.php';
require_once __DIR__ . '/classes/Payment.php';

$database = new Database();
$db = $database->getConnection();
$payment = new Payment($db);
$payment->ensureTable();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$auth->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your payment form expired. Refresh the page and try again.';
    } else {
        $error = $payment->record(
            (int) ($_POST['appointment_id'] ?? 0),
            (float) ($_POST['amount'] ?? 0),
            $_POST['payment_method'] ?? ''
        );
    }

    if ($error === '') {
        header('Location: transactions.php?recorded=1');
        exit;
    }
}

$sessions = $payment->getAppointmentsWithBalance();
$transactions = $payment->getAll();
$summary = $payment->getSummary();
$selectedAppointment = (int) ($_POST['appointment_id'] ?? $_GET['appointment_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - BarberBook</title>
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
        <a href="index.php" class="nav-link"><span class="nav-icon">▦</span>Dashboard</a>
        <a href="view_appointments.php" class="nav-link"><span class="nav-icon">◫</span>Sessions</a>
        <a href="backend/add_appointment.php" class="nav-link"><span class="nav-icon">＋</span>Book Session</a>
        <a href="backend/search_appointments.php" class="nav-link"><span class="nav-icon">⌕</span>Search</a>
        <a href="transactions.php" class="nav-link active"><span class="nav-icon">₱</span>Transactions</a>
        <a href="users.php" class="nav-link"><span class="nav-icon">♙</span>Staff Accounts</a>
    </nav>
    <div class="sidebar-account">
        <strong><?= htmlspecialchars($currentUser['name']); ?> · Admin</strong>
        <form action="logout.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->csrfToken()); ?>">
            <button type="submit">Sign out</button>
        </form>
    </div>
    <div class="sidebar-bottom">
        <div class="scissors">✂</div>
        <strong>BarberBook</strong>
        <small>Sessions and payments</small>
    </div>
</aside>

<main class="main-content">
    <header class="top-header">
        <div>
            <div class="breadcrumb">HOME / TRANSACTIONS</div>
            <h1>Transactions</h1>
            <p>Record payments and review the payment history.</p>
        </div>
        <a href="view_appointments.php" class="new-appointment">View Sessions <span>→</span></a>
    </header>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-top"><div class="stat-icon blue">₱</div><span class="stat-label">COLLECTED</span></div>
            <div class="stat-number">₱<?= number_format((float) $summary['total_amount'], 2); ?></div>
            <div class="stat-description">Payments recorded to date</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><div class="stat-icon green">✓</div><span class="stat-label">TODAY</span></div>
            <div class="stat-number">₱<?= number_format((float) $summary['today_amount'], 2); ?></div>
            <div class="stat-description">Collected today</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><div class="stat-icon orange">↔</div><span class="stat-label">RECORDS</span></div>
            <div class="stat-number"><?= (int) $summary['transaction_count']; ?></div>
            <div class="stat-description">Payment transactions</div>
        </div>
    </section>

    <section class="appointment-card form-card">
        <div class="card-header">
            <div><div class="section-label">PAYMENT ENTRY</div><h2>Record a Transaction</h2></div>
        </div>
        <?php if ($error !== ''): ?>
            <div class="alert-error"><?= htmlspecialchars($error); ?></div>
        <?php elseif (isset($_GET['recorded'])): ?>
            <div class="alert-success">Payment recorded successfully.</div>
        <?php endif; ?>
        <?php if (empty($sessions)): ?>
            <div class="empty-state">
                <strong>No outstanding session balances</strong>
                <span>All sessions are paid, or no active sessions have been booked.</span>
            </div>
        <?php else: ?>
            <form method="POST" class="form-grid">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->csrfToken()); ?>">
                <div class="form-group full">
                    <label for="appointment_id">Session</label>
                    <select id="appointment_id" name="appointment_id" required>
                        <option value="">Choose a session</option>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?= (int) $session['AppointmentID']; ?>" <?= $selectedAppointment === (int) $session['AppointmentID'] ? 'selected' : ''; ?>>
                                #<?= (int) $session['AppointmentID']; ?> · <?= htmlspecialchars($session['CustomerName']); ?> · <?= htmlspecialchars($session['ServiceName']); ?> · Balance ₱<?= number_format((float) $session['Balance'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Amount (₱)</label>
                    <input id="amount" name="amount" type="number" min="0.01" step="0.01" value="<?= htmlspecialchars($_POST['amount'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="payment_method">Payment method</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Choose a method</option>
                        <?php foreach (['Cash', 'Card', 'E-wallet', 'Bank transfer'] as $method): ?>
                            <option value="<?= htmlspecialchars($method); ?>" <?= ($_POST['payment_method'] ?? '') === $method ? 'selected' : ''; ?>><?= htmlspecialchars($method); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions full">
                    <button type="submit" class="btn">Record Payment</button>
                </div>
            </form>
        <?php endif; ?>
    </section>

    <section class="appointment-card transaction-history">
        <div class="card-header">
            <div><div class="section-label">PAYMENT HISTORY</div><h2>All Transactions</h2></div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>TRANSACTION</th><th>DATE</th><th>SESSION</th><th>CUSTOMER</th><th>SERVICE</th><th>METHOD</th><th>AMOUNT</th></tr></thead>
                <tbody>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="7" class="empty-state"><strong>No transactions recorded yet</strong><span>Payments you record will appear here.</span></td></tr>
                <?php else: ?>
                    <?php foreach ($transactions as $row): ?>
                        <tr>
                            <td><span class="appointment-id">#<?= (int) $row['TransactionID']; ?></span></td>
                            <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($row['CreatedAt']))); ?></td>
                            <td><a class="session-link" href="view_appointments.php">#<?= (int) $row['AppointmentID']; ?></a></td>
                            <td><?= htmlspecialchars($row['CustomerName']); ?></td>
                            <td><?= htmlspecialchars($row['ServiceName']); ?></td>
                            <td><?= htmlspecialchars($row['PaymentMethod']); ?></td>
                            <td><strong>₱<?= number_format((float) $row['Amount'], 2); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer><strong>BarberBook</strong> &nbsp; • &nbsp; Online Barbershop Appointment System</footer>
</main>
</body>
</html>