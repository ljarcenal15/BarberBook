    <header class="top-header">
        <div>
            <div class="breadcrumb">HOME / DASHBOARD</div>
            <h1>Dashboard</h1>
            <p>Manage your barbershop appointments with ease.</p>
        </div>
        <a href="backend/add_appointment.php" class="new-appointment">
            <span>＋</span> New Appointment
        </a>
    </header>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-top"><div class="stat-icon blue">◫</div><span class="stat-label">SESSIONS</span></div>
            <div class="stat-number"><?= $total; ?></div>
            <div class="stat-description">All booked sessions</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><div class="stat-icon orange">◷</div><span class="stat-label">PENDING</span></div>
            <div class="stat-number"><?= $pending; ?></div>
            <div class="stat-description">Waiting for confirmation</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><div class="stat-icon green">✓</div><span class="stat-label">CONFIRMED</span></div>
            <div class="stat-number"><?= $confirmed; ?></div>
            <div class="stat-description">Upcoming appointments</div>
        </div>
        <div class="stat-card">
            <div class="stat-top"><div class="stat-icon purple">★</div><span class="stat-label">COMPLETED</span></div>
            <div class="stat-number"><?= $completed; ?></div>
            <div class="stat-description">Finished appointments</div>
        </div>
    </section>

    <section class="appointment-card">
        <div class="card-header">
            <div>
                <div class="section-label">SESSION MANAGEMENT</div>
                <h2>Recent Sessions</h2>
            </div>
            <a href="view_appointments.php" class="view-all">View All <span>→</span></a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>CUSTOMER</th><th>BARBER</th><th>SERVICE</th>
                        <th>DATE</th><th>TIME</th><th>STATUS</th><th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($recentRecords)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            <div class="empty-icon">✂</div>
                            <strong>No appointments yet</strong>
                            <span>Start by booking your first appointment.</span>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentRecords as $row): ?>
                        <?php $status = strtolower($row['Status']); ?>
                        <tr>
                            <td><span class="appointment-id">#<?= htmlspecialchars($row['AppointmentID']); ?></span></td>
                            <td>
                                <div class="customer">
                                    <div class="customer-avatar"><?= strtoupper(substr(htmlspecialchars($row['CustomerName']), 0, 1)); ?></div>
                                    <div><strong><?= htmlspecialchars($row['CustomerName']); ?></strong><small>Customer</small></div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['BarberName']); ?></td>
                            <td><span class="service-name"><?= htmlspecialchars($row['ServiceName']); ?></span></td>
                            <td><?= htmlspecialchars($row['AppointmentDate']); ?></td>
                            <td><?= date('h:i A', strtotime($row['AppointmentTime'])); ?></td>
                            <td><span class="status <?= $status; ?>"><?= htmlspecialchars($row['Status']); ?></span></td>
                            <td><a href="backend/update_appointment.php?id=<?= $row['AppointmentID']; ?>" class="action-button">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="quick-section">
        <h2>Quick Actions</h2>
        <div class="quick-grid">
            <a href="backend/add_appointment.php" class="quick-card">
                <div class="quick-icon">＋</div>
                <div><strong>Book Session</strong><span>Create a new booking</span></div><b>→</b>
            </a>
            <a href="view_appointments.php" class="quick-card">
                <div class="quick-icon">◫</div>
                <div><strong>View Sessions</strong><span>Manage all bookings</span></div><b>→</b>
            </a>
            <a href="backend/search_appointments.php" class="quick-card">
                <div class="quick-icon">⌕</div>
                <div><strong>Search</strong><span>Find an appointment</span></div><b>→</b>
            </a>
            <a href="transactions.php" class="quick-card">
                <div class="quick-icon">₱</div>
                <div><strong>Transactions</strong><span>Record and review payments</span></div><b>→</b>
            </a>
        </div>
    </section>
