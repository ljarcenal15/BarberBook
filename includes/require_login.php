<?php
require_once __DIR__ . '/../classes/db.php';
require_once __DIR__ . '/../classes/Auth.php';

$authDatabase = new Database();
$auth = new Auth($authDatabase->getConnection());
$currentUser = $auth->currentUser();

if ($currentUser === null) {
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $appRoot = dirname($scriptPath);
    if (basename($appRoot) === 'backend') {
        $appRoot = dirname($appRoot);
    }
    header('Location: ' . rtrim($appRoot, '/') . '/login.php');
    exit;
}

function require_role(string $role): void
{
    global $currentUser;

    if (($currentUser['role'] ?? '') !== $role) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="en"><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Access denied - BarberBook</title><link rel="stylesheet" href="includes/style.css?v=7"><body><main class="access-denied"><span class="section-label">403 · ACCESS DENIED</span><h1>Admin access required</h1><p>Your staff account does not have permission to open this section.</p><a class="btn" href="index.php">Back to dashboard</a></main></body></html>';
        exit;
    }
}