<?php

class Appointment
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(
        string $customerName,
        string $contactNumber,
        string $email,
        $barberId,
        $serviceId,
        string $date,
        string $time
    ): bool {
        try {
            $this->connection->beginTransaction();

            $slotStatement = $this->connection->prepare(
                'SELECT COUNT(*)
                 FROM appointment
                 WHERE BarberID = :barber
                   AND AppointmentDate = :date
                   AND AppointmentTime = :time
                   AND Status <> :cancelled'
            );
            $slotStatement->execute([
                ':barber' => $barberId,
                ':date' => $date,
                ':time' => $time,
                ':cancelled' => 'Cancelled'
            ]);
            if ((int) $slotStatement->fetchColumn() > 0) {
                $this->connection->rollBack();
                return false;
            }

            $customerStatement = $this->connection->prepare(
                'SELECT CustomerID FROM customer WHERE FullName = :name LIMIT 1'
            );
            $customerStatement->execute([':name' => $customerName]);
            $customer = $customerStatement->fetch();

            if ($customer) {
                $customerId = $customer['CustomerID'];

                $customerStatement = $this->connection->prepare(
                    'UPDATE customer
                     SET ContactNumber = :contact, Email = :email
                     WHERE CustomerID = :id'
                );
                $customerStatement->execute([
                    ':contact' => $contactNumber,
                    ':email' => $email,
                    ':id' => $customerId
                ]);
            } else {
                $customerStatement = $this->connection->prepare(
                    'INSERT INTO customer (FullName, ContactNumber, Email)
                     VALUES (:name, :contact, :email)'
                );
                $customerStatement->execute([
                    ':name' => $customerName,
                    ':contact' => $contactNumber,
                    ':email' => $email
                ]);
                $customerId = $this->connection->lastInsertId();
            }

            $appointmentStatement = $this->connection->prepare(
                'INSERT INTO appointment
                    (CustomerID, BarberID, ServiceID, AppointmentDate, AppointmentTime, Status)
                 VALUES (:customer, :barber, :service, :date, :time, :status)'
            );
            $appointmentStatement->execute([
                ':customer' => $customerId,
                ':barber' => $barberId,
                ':service' => $serviceId,
                ':date' => $date,
                ':time' => $time,
                ':status' => 'Pending'
            ]);

            $this->connection->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            return false;
        }
    }

    public function update(
        $id,
        $customerId,
        string $contactNumber,
        $barberId,
        $serviceId,
        string $date,
        string $time,
        string $status
    ): bool {
        try {
            $this->connection->beginTransaction();

            $customerStatement = $this->connection->prepare(
                'UPDATE customer SET ContactNumber = :contact WHERE CustomerID = :customer'
            );
            $customerStatement->execute([
                ':contact' => $contactNumber,
                ':customer' => $customerId
            ]);

            $statement = $this->connection->prepare(
                'UPDATE appointment
                 SET CustomerID = :customer,
                     BarberID = :barber,
                     ServiceID = :service,
                     AppointmentDate = :date,
                     AppointmentTime = :time,
                     Status = :status
                 WHERE AppointmentID = :id'
            );
            $statement->execute([
                ':customer' => $customerId,
                ':barber' => $barberId,
                ':service' => $serviceId,
                ':date' => $date,
                ':time' => $time,
                ':status' => $status,
                ':id' => $id
            ]);

            $this->connection->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            return false;
        }
    }

    public function delete($id): bool
    {
        $statement = $this->connection->prepare(
            'DELETE FROM appointment WHERE AppointmentID = :id'
        );

        return $statement->execute([':id' => $id]);
    }

    public function getAll(): array
    {
        $statement = $this->connection->prepare($this->appointmentQuery() . ' ORDER BY a.AppointmentID DESC');
        $statement->execute();
        return $statement->fetchAll();
    }

    public function getById($id): array|false
    {
        $statement = $this->connection->prepare(
            'SELECT a.*, c.ContactNumber
             FROM appointment a
             INNER JOIN customer c ON a.CustomerID = c.CustomerID
             WHERE a.AppointmentID = :id LIMIT 1'
        );
        $statement->execute([':id' => $id]);
        return $statement->fetch();
    }

    public function search(string $keyword): array
    {
        $statement = $this->connection->prepare(
            $this->appointmentQuery() . '
             WHERE c.FullName LIKE :keyword
                    OR c.ContactNumber LIKE :keyword
                    OR b.FullName LIKE :keyword
                    OR s.ServiceName LIKE :keyword
                    OR a.Status LIKE :keyword
                    OR a.AppointmentDate LIKE :keyword
                 ORDER BY a.AppointmentID DESC'
        );
        $statement->execute([':keyword' => '%' . $keyword . '%']);
        return $statement->fetchAll();
    }

    public function getCustomers(): array
    {
        $statement = $this->connection->prepare(
            'SELECT CustomerID, FullName FROM customer ORDER BY FullName'
        );
        $statement->execute();
        return $statement->fetchAll();
    }

    public function getBarbers(): array
    {
        $statement = $this->connection->prepare(
            'SELECT BarberID, FullName FROM barber ORDER BY FullName'
        );
        $statement->execute();
        return $statement->fetchAll();
    }

    public function getServices(): array
    {
        $statement = $this->connection->prepare(
            'SELECT ServiceID, ServiceName, Price FROM service ORDER BY ServiceName'
        );
        $statement->execute();
        return $statement->fetchAll();
    }

    public function getStatistics(): array
    {
        $statement = $this->connection->prepare(
            'SELECT Status, COUNT(*) AS total FROM appointment GROUP BY Status'
        );
        $statement->execute();

        $statistics = [
            'total' => 0,
            'pending' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];

        foreach ($statement->fetchAll() as $row) {
            $key = strtolower($row['Status']);
            if (array_key_exists($key, $statistics)) {
                $statistics[$key] = (int) $row['total'];
                $statistics['total'] += (int) $row['total'];
            }
        }

        return $statistics;
    }

    private function appointmentQuery(): string
    {
        return 'SELECT
                    a.AppointmentID,
                    a.AppointmentDate,
                    a.AppointmentTime,
                    a.Status,
                    c.FullName AS CustomerName,
                    c.ContactNumber,
                    b.FullName AS BarberName,
                    s.ServiceName,
                    s.Price
                FROM appointment a
                INNER JOIN customer c ON a.CustomerID = c.CustomerID
                INNER JOIN barber b ON a.BarberID = b.BarberID
                INNER JOIN service s ON a.ServiceID = s.ServiceID';
    }
}
