<?php

class Payment
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function ensureTable(): void
    {
        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS payment_transactions (
                TransactionID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                AppointmentID INT NOT NULL,
                Amount DECIMAL(10, 2) NOT NULL,
                PaymentMethod VARCHAR(30) NOT NULL,
                CreatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_payment_appointment (AppointmentID),
                INDEX idx_payment_created (CreatedAt)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function record(int $appointmentId, float $amount, string $method): string
    {
        $allowedMethods = ['Cash', 'Card', 'E-wallet', 'Bank transfer'];
        if ($amount <= 0 || !in_array($method, $allowedMethods, true)) {
            return 'Enter a valid amount and payment method.';
        }

        try {
            $this->connection->beginTransaction();
            $appointmentStatement = $this->connection->prepare(
                'SELECT a.Status, s.Price
                 FROM appointment a
                 INNER JOIN service s ON s.ServiceID = a.ServiceID
                 WHERE a.AppointmentID = :id
                 FOR UPDATE'
            );
            $appointmentStatement->execute([':id' => $appointmentId]);
            $appointment = $appointmentStatement->fetch();

            if (!$appointment || $appointment['Status'] === 'Cancelled') {
                $this->connection->rollBack();
                return 'Choose an active session before recording a payment.';
            }

            $paidStatement = $this->connection->prepare(
                'SELECT COALESCE(SUM(Amount), 0) FROM payment_transactions WHERE AppointmentID = :id'
            );
            $paidStatement->execute([':id' => $appointmentId]);
            $paid = (float) $paidStatement->fetchColumn();
            $balance = (float) $appointment['Price'] - $paid;

            if ($amount > $balance + 0.001) {
                $this->connection->rollBack();
                return 'Payment exceeds the outstanding balance of ₱' . number_format(max(0, $balance), 2) . '.';
            }

            $insert = $this->connection->prepare(
                'INSERT INTO payment_transactions (AppointmentID, Amount, PaymentMethod)
                 VALUES (:appointment, :amount, :method)'
            );
            $insert->execute([
                ':appointment' => $appointmentId,
                ':amount' => number_format($amount, 2, '.', ''),
                ':method' => $method
            ]);

            $this->connection->commit();
            return '';
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            return 'Unable to record the payment. Please try again.';
        }
    }

    public function getAppointmentsWithBalance(): array
    {
        $statement = $this->connection->prepare(
            'SELECT a.AppointmentID, a.AppointmentDate, a.Status,
                    c.FullName AS CustomerName, s.ServiceName, s.Price,
                    COALESCE(SUM(p.Amount), 0) AS PaidAmount,
                    s.Price - COALESCE(SUM(p.Amount), 0) AS Balance
             FROM appointment a
             INNER JOIN customer c ON c.CustomerID = a.CustomerID
             INNER JOIN service s ON s.ServiceID = a.ServiceID
             LEFT JOIN payment_transactions p ON p.AppointmentID = a.AppointmentID
             WHERE a.Status <> :cancelled
             GROUP BY a.AppointmentID, a.AppointmentDate, a.Status,
                      c.FullName, s.ServiceName, s.Price
             HAVING Balance > 0
             ORDER BY a.AppointmentDate DESC, a.AppointmentID DESC'
        );
        $statement->execute([':cancelled' => 'Cancelled']);
        return $statement->fetchAll();
    }

    public function getAll(): array
    {
        $statement = $this->connection->prepare(
            'SELECT p.TransactionID, p.Amount, p.PaymentMethod, p.CreatedAt,
                    a.AppointmentID, a.AppointmentDate,
                    c.FullName AS CustomerName, s.ServiceName
             FROM payment_transactions p
             INNER JOIN appointment a ON a.AppointmentID = p.AppointmentID
             INNER JOIN customer c ON c.CustomerID = a.CustomerID
             INNER JOIN service s ON s.ServiceID = a.ServiceID
             ORDER BY p.CreatedAt DESC, p.TransactionID DESC'
        );
        $statement->execute();
        return $statement->fetchAll();
    }

    public function getSummary(): array
    {
        $statement = $this->connection->prepare(
            'SELECT COUNT(*) AS transaction_count,
                    COALESCE(SUM(Amount), 0) AS total_amount,
                    COALESCE(SUM(CASE WHEN DATE(CreatedAt) = CURDATE() THEN Amount ELSE 0 END), 0) AS today_amount
             FROM payment_transactions'
        );
        $statement->execute();
        return $statement->fetch();
    }
}