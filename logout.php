<?php
require_once __DIR__ . '/classes/db.php';
require_once __DIR__ . '/classes/Auth.php';

$database = new Database();
$auth = new Auth($database->getConnection());

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$auth->isValidCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Invalid sign-out request.');
}

$auth->logout();
header('Location: login.php');
exit;