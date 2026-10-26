<?php
require_once __DIR__ . '/classes/db.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Appointment.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$appointment = new Appointment($db);
$barbers = $appointment->getBarbers();
$services = $appointment->getServices();

$error = '';
$booked = isset($_GET['booked']);
$old = [
    'customer_name' => '',
    'contact_number' => '',
    'email' => '',
    'barber_id' => '',
    'service_id' => '',
    'appointment_date' => '',
    'appointment_time' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $field => $value) {
        $old[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    if (!$auth->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your booking form expired. Refresh the page and try again.';
    } else {
        $dateTime = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i',
            $old['appointment_date'] . ' ' . $old['appointment_time']
        );
        $dateErrors = DateTimeImmutable::getLastErrors();
        $validDate = $dateTime !== false
            && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
            && $dateTime->format('Y-m-d H:i') === $old['appointment_date'] . ' ' . $old['appointment_time']
            && $dateTime > new DateTimeImmutable();
        $validEmail = $old['email'] === '' || filter_var($old['email'], FILTER_VALIDATE_EMAIL);

        if ($old['customer_name'] === '' || mb_strlen($old['customer_name']) > 120 || !$validEmail || !$validDate) {
            $error = 'Check your name, email, and choose a future date and time.';
        } elseif ($old['contact_number'] !== '' && mb_strlen($old['contact_number']) > 40) {
            $error = 'Contact number must be 40 characters or fewer.';
        } else {
            $created = $appointment->create(
                $old['customer_name'],
                $old['contact_number'],
                $old['email'],
                (int) $old['barber_id'],
                (int) $old['service_id'],
                $old['appointment_date'],
                $old['appointment_time']
            );

            if ($created) {
                header('Location: book.php?booked=1');
                exit;
            }
            $error = 'That time may have just been booked. Choose another barber or time and try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Barber Session - BarberBook</title>
    <link rel="stylesheet" href="includes/style.css?v=8">
</head>
<body class="auth-page">
<main class="public-booking-layout">
    <header class="public-booking-header">
        <a class="auth-brand" href="book.php"><span class="brand-icon">✂</span><span>BarberBook</span></a>
        <a class="staff-login-link" href="login.php">Staff sign in <span>→</span></a>
    </header>

    <section class="public-booking-content">
        <div class="public-booking-heading">
            <span class="section-label">YOUR NEXT GOOD CUT</span>
            <h1>Book a session.</h1>
            <p>Choose your service, barber, and a time that works for you.</p>
        </div>

        <?php if ($booked): ?>
            <section class="booking-confirmation" role="status">
                <span class="confirmation-mark">✓</span>
                <div>
                    <h2>Request received</h2>
                    <p>Your session is pending confirmation. The shop will contact you using the details you provided.</p>
                    <a href="book.php" class="action-button">Book another session</a>
                </div>
            </section>
        <?php else: ?>
            <section class="appointment-card form-card public-booking-card">
                <?php if ($error !== ''): ?><div class="alert-error" role="alert"><?= htmlspecialchars($error); ?></div><?php endif; ?>
                <?php if (empty($barbers) || empty($services)): ?>
                    <div class="empty-state"><strong>Online booking is unavailable right now</strong><span>Please contact the shop directly.</span></div>
                <?php else: ?>
                    <form method="POST" class="form-grid public-booking-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->csrfToken()); ?>">
                        <div class="form-group"><label for="customer_name">Your name</label><input id="customer_name" name="customer_name" autocomplete="name" maxlength="120" value="<?= htmlspecialchars($old['customer_name']); ?>" required></div>
                        <div class="form-group"><label for="contact_number">Phone number</label><input id="contact_number" name="contact_number" type="tel" autocomplete="tel" maxlength="40" value="<?= htmlspecialchars($old['contact_number']); ?>" required></div>
                        <div class="form-group full"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" value="<?= htmlspecialchars($old['email']); ?>" required></div>
                        <div class="form-group"><label for="service_id">Service</label><select id="service_id" name="service_id" required><option value="">Choose a service</option><?php foreach ($services as $service): ?><option value="<?= (int) $service['ServiceID']; ?>" <?= $old['service_id'] === (string) $service['ServiceID'] ? 'selected' : ''; ?>><?= htmlspecialchars($service['ServiceName']); ?> · ₱<?= number_format((float) $service['Price'], 2); ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label for="barber_id">Barber</label><select id="barber_id" name="barber_id" required><option value="">Choose a barber</option><?php foreach ($barbers as $barber): ?><option value="<?= (int) $barber['BarberID']; ?>" <?= $old['barber_id'] === (string) $barber['BarberID'] ? 'selected' : ''; ?>><?= htmlspecialchars($barber['FullName']); ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label for="appointment_date">Date</label><input id="appointment_date" name="appointment_date" type="date" min="<?= date('Y-m-d'); ?>" value="<?= htmlspecialchars($old['appointment_date']); ?>" required></div>
                        <div class="form-group"><label for="appointment_time">Time</label><input id="appointment_time" name="appointment_time" type="time" value="<?= htmlspecialchars($old['appointment_time']); ?>" required></div>
                        <div class="form-actions full"><button type="submit" class="btn">Request Session <span aria-hidden="true">→</span></button></div>
                    </form>
                <?php endif; ?>
            </section>
            <p class="booking-footnote">Your request is not confirmed until the shop approves it. Existing barber bookings at the selected time cannot be double-booked.</p>
        <?php endif; ?>
    </section>
    <footer><strong>BarberBook</strong> &nbsp; • &nbsp; Online Barbershop Appointment System</footer>
</main>
</body>
</html>