<?php
require_once __DIR__ . '/../includes/require_login.php';
require_once __DIR__ . '/../classes/db.php';
require_once __DIR__ . '/../classes/Appointment.php';

$database = new Database();
$db = $database->getConnection();
$appointment = new Appointment($db);

$barbers = $appointment->getBarbers();
$services = $appointment->getServices();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $created = $appointment->create(
        $_POST['customer_name'] ?? '',
        $_POST['contact_number'] ?? '',
        $_POST['email'] ?? '',
        $_POST['barber_id'] ?? null,
        $_POST['service_id'] ?? null,
        $_POST['appointment_date'] ?? '',
        $_POST['appointment_time'] ?? ''
    );

    if ($created) {
        header("Location: ../view_appointments.php");
        exit();
    } else {
        $message = "Failed to book appointment. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - BarberBook</title>
    <link rel="stylesheet" href="../includes/style.css?v=7">
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
        <a href="../index.php" class="nav-link">
            <span class="nav-icon">▦</span>
            Dashboard
        </a>
        <a href="../view_appointments.php" class="nav-link">
            <span class="nav-icon">◫</span>
            Sessions
        </a>
        <a href="add_appointment.php" class="nav-link active">
            <span class="nav-icon">＋</span>
            Book Session
        </a>
        <a href="search_appointments.php" class="nav-link">
            <span class="nav-icon">⌕</span>
            Search
        </a>
        <?php if ($currentUser['role'] === 'admin'): ?>
            <a href="../transactions.php" class="nav-link"><span class="nav-icon">₱</span>Transactions</a>
            <a href="../users.php" class="nav-link"><span class="nav-icon">♙</span>Staff Accounts</a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-account">
        <strong><?= htmlspecialchars($currentUser['name']); ?> · <?= htmlspecialchars(ucfirst($currentUser['role'])); ?></strong>
        <form action="../logout.php" method="POST">
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

    <header class="top-header">
        <div>
            <div class="breadcrumb">HOME / BOOK SESSION</div>
            <h1>Book a Session</h1>
            <p>Schedule a new barbershop service session.</p>
        </div>

        <a href="../view_appointments.php" class="new-appointment btn-muted">
            <span>←</span> View All List
        </a>
    </header>

    <section class="appointment-card form-card booking-card">
        <div class="card-header">
            <div>
                <div class="section-label">NEW SESSION</div>
                <h2>Session Details</h2>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert-error">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="add_appointment.php" method="POST" class="form-shell">

            <label>
                Customer Full Name
                <input type="text" name="customer_name" placeholder="e.g. Juan Dela Cruz" required>
            </label>

            <label>
                Contact Number
                <input type="text" name="contact_number" placeholder="e.g. 09123456789">
            </label>

            <label>
                Email Address
                <input type="email" name="email" placeholder="e.g. juan@example.com" required>
            </label>

            <label>
                Select Barber
                <select name="barber_id" required>
                    <option value="">-- Select Barber --</option>
                    <?php foreach ($barbers as $b): ?>
                        <option value="<?= $b['BarberID']; ?>">
                            <?= htmlspecialchars($b['FullName'] ?? $b['BarberName'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Service
                <select name="service_id" required>
                    <option value="">-- Select Service --</option>
                    <?php foreach ($services as $s): ?>
                        <option value="<?= $s['ServiceID']; ?>">
                            <?= htmlspecialchars($s['ServiceName']); ?> - ₱<?= number_format($s['Price'], 2); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="form-row">
                <label>
                    Date
                    <input type="date" name="appointment_date" required>
                </label>

                <label>
                    Time
                    <input type="time" name="appointment_time" required>
                </label>
            </div>

            <div class="form-actions-left">
                <button type="submit" class="btn">
                    Confirm Booking
                </button>
                <a href="../index.php" class="btn btn-muted">
                    Cancel
                </a>
            </div>

        </form>
    </section>

    <footer>
        <strong>BarberBook</strong> &nbsp; • &nbsp; Online Barbershop Appointment System
    </footer>

</main>

</body>
</html>