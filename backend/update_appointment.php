<?php
require_once __DIR__ . '/../includes/require_login.php';

require_once __DIR__ . '/../classes/db.php';
require_once __DIR__ . '/../classes/Appointment.php';

$database = new Database();
$db = $database->getConnection();

$appointment = new Appointment($db);

$id = $_GET['id'] ?? $_POST['appointment_id'] ?? null;

if (!$id) {
    die("Invalid appointment ID.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $success = $appointment->update(
        $id,
        $_POST['customer_id'] ?? null,
        $_POST['contact_number'] ?? '',
        $_POST['barber_id'] ?? null,
        $_POST['service_id'] ?? null,
        $_POST['appointment_date'] ?? null,
        $_POST['appointment_time'] ?? null,
        $_POST['status'] ?? 'Pending'
    );

    if ($success) {
        header("Location: ../view_appointments.php?updated=1");
        exit;
    }

    $error = "Unable to update appointment.";
}

$record = $appointment->getById($id);

if (!$record) {
    die("Appointment not found.");
}

$customers = $appointment->getCustomers();
$barbers = $appointment->getBarbers();
$services = $appointment->getServices();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Appointment</title>
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

        <a href="../view_appointments.php" class="nav-link active">
            <span class="nav-icon">◫</span>
            Sessions
        </a>

        <a href="add_appointment.php" class="nav-link">
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
            <div class="breadcrumb">HOME / SESSIONS / EDIT</div>
            <h1>Edit Session</h1>
            <p>Update session information.</p>
        </div>
    </header>

    <div class="form-card">

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden" name="appointment_id" value="<?= $record['AppointmentID']; ?>">

            <div class="form-grid">

                <div class="form-group">
                    <label>Customer</label>
                    <select name="customer_id" required>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['CustomerID']; ?>" <?= $record['CustomerID'] == $c['CustomerID'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($c['FullName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" value="<?= htmlspecialchars($record['ContactNumber'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Barber</label>
                    <select name="barber_id" required>
                        <?php foreach ($barbers as $b): ?>
                            <option value="<?= $b['BarberID']; ?>" <?= $record['BarberID'] == $b['BarberID'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($b['FullName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group full">
                    <label>Service</label>
                    <select name="service_id" required>
                        <?php foreach ($services as $s): ?>
                            <option value="<?= $s['ServiceID']; ?>" <?= $record['ServiceID'] == $s['ServiceID'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($s['ServiceName']); ?> — ₱<?= number_format($s['Price'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="appointment_date" value="<?= htmlspecialchars($record['AppointmentDate']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Time</label>
                    <input type="time" name="appointment_time" value="<?= htmlspecialchars($record['AppointmentTime']); ?>" required>
                </div>

                <div class="form-group full">
                    <label>Status</label>
                    <select name="status">
                        <?php
                        $statuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
                        foreach ($statuses as $status):
                        ?>
                            <option value="<?= $status; ?>" <?= $record['Status'] === $status ? 'selected' : ''; ?>>
                                <?= $status; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <a href="../view_appointments.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn">Save Changes</button>
            </div>

        </form>

    </div>

</main>

</body>
</html>
