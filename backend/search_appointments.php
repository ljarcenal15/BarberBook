<?php
require_once __DIR__ . '/../includes/require_login.php';
require_once __DIR__ . '/../classes/db.php';
require_once __DIR__ . '/../classes/Appointment.php';

$database = new Database();
$db = $database->getConnection();

$appointment = new Appointment($db);

$keyword = $_GET['keyword'] ?? '';
$results = [];

if (!empty($keyword)) {
    $results = $appointment->search($keyword);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Appointments - BarberBook</title>
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
        <a href="add_appointment.php" class="nav-link">
            <span class="nav-icon">＋</span>
            Book Session
        </a>
        <a href="search_appointments.php" class="nav-link active">
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
            <div class="breadcrumb">HOME / SEARCH</div>
            <h1>Search Sessions</h1>
            <p>Find sessions quickly by customer, barber, service, or status.</p>
        </div>

        <a href="add_appointment.php" class="new-appointment">
            <span>＋</span> New Appointment
        </a>
    </header>
    <section class="appointment-card">

        <div class="card-header">
            <div>
                <div class="section-label">LOOKUP SYSTEM</div>
                <h2>Search Database</h2>
            </div>
        </div>

        <form action="search_appointments.php" method="GET" class="search-form">
            <input
                type="text"
                name="keyword"
                placeholder="Search by customer name, barber, service, or status..."
                value="<?= htmlspecialchars($keyword); ?>"
                required
            >
            <button type="submit" class="btn">
                🔍 Search
            </button>
            <?php if (!empty($keyword)): ?>
                <a href="search_appointments.php" class="btn btn-muted">
                    Clear
                </a>
            <?php endif; ?>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>CUSTOMER</th>
                        <th>CONTACT</th>
                        <th>BARBER</th>
                        <th>SERVICE</th>
                        <th>PRICE</th>
                        <th>DATE</th>
                        <th>TIME</th>
                        <th>STATUS</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (empty($keyword)): ?>
                    <tr>
                        <td colspan="9" class="empty-state">
                            <div class="empty-icon">⌕</div>
                            <strong>Type a keyword above to search</strong>
                            <br>
                            <span>You can search by customer name, barber, or service.</span>
                        </td>
                    </tr>
                <?php elseif (empty($results)): ?>
                    <tr>
                        <td colspan="9" class="empty-state error">
                            <div class="empty-icon">❌</div>
                            <strong>No matching appointments found</strong>
                            <br>
                            <span>Try searching for a different keyword.</span>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($results as $row): ?>
                        <?php $status = strtolower($row['Status']); ?>
                        <tr>
                            <td>
                                <span class="appointment-id">#<?= htmlspecialchars($row['AppointmentID']); ?></span>
                            </td>

                            <td>
                                <div class="customer">
                                    <div class="customer-avatar">
                                        <?= strtoupper(substr(htmlspecialchars($row['CustomerName']), 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($row['CustomerName']); ?></strong>
                                    </div>
                                </div>
                            </td>

                            <td><?= htmlspecialchars($row['ContactNumber'] ?? 'N/A'); ?></td>

                            <td><?= htmlspecialchars($row['BarberName']); ?></td>

                            <td>
                                <span class="service-name"><?= htmlspecialchars($row['ServiceName']); ?></span>
                            </td>

                            <td><strong>₱<?= number_format($row['Price'], 2); ?></strong></td>

                            <td><?= htmlspecialchars($row['AppointmentDate']); ?></td>

                            <td><?= date('h:i A', strtotime($row['AppointmentTime'])); ?></td>

                            <td>
                                <span class="status <?= $status; ?>"><?= htmlspecialchars($row['Status']); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </section>

    <footer>
        <strong>BarberBook</strong> &nbsp; • &nbsp; Online Barbershop Appointment System
    </footer>

</main>

</body>
</html>