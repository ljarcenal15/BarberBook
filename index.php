<?php
require_once __DIR__ . '/includes/require_login.php';
require_once __DIR__ . '/classes/db.php';
require_once __DIR__ . '/classes/Appointment.php';

$database = new Database();
$appointment = new Appointment($database->getConnection());
$statistics = $appointment->getStatistics();
$recentRecords = array_slice($appointment->getAll(), 0, 5);

$total = $statistics['total'];
$pending = $statistics['pending'];
$confirmed = $statistics['confirmed'];
$completed = $statistics['completed'];

require __DIR__ . '/includes/dashboard_head.php';
require __DIR__ . '/includes/dashboard_content.php';
require __DIR__ . '/includes/dashboard_footer.php';
