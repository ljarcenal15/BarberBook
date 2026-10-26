<?php
require_once __DIR__ . '/../includes/require_login.php';

require_once __DIR__ . '/../classes/db.php';
require_once __DIR__ . '/../classes/Appointment.php';

$database = new Database();
$db = $database->getConnection();

$appointment = new Appointment($db);

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../view_appointments.php");
    exit;
}

$appointment->delete($id);

header("Location: ../view_appointments.php?deleted=1");
exit;
?>