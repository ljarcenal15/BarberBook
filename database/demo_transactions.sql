-- Demo data only. Use only in a disposable/test database, never production.
-- Creates three clearly labeled demo customers, sessions, and payments.
-- Import database/schema.sql before importing this file.

INSERT INTO customer (FullName, Email, ContactNumber) VALUES
        ('Demo Customer One', 'demo.customer.one@example.test', '000-000-0001'),
        ('Demo Customer Two', 'demo.customer.two@example.test', '000-000-0002'),
        ('Demo Customer Three', 'demo.customer.three@example.test', '000-000-0003')
ON DUPLICATE KEY UPDATE FullName = VALUES(FullName), ContactNumber = VALUES(ContactNumber);

INSERT INTO appointment (CustomerID, BarberID, ServiceID, AppointmentDate, AppointmentTime, Status)
SELECT c.CustomerID, b.BarberID, s.ServiceID, DATE_ADD(CURDATE(), INTERVAL 14 DAY), '10:00:00', 'Confirmed'
FROM customer c
CROSS JOIN barber b
CROSS JOIN service s
WHERE c.Email = 'demo.customer.one@example.test'
    AND b.FullName = 'Barber Mike'
    AND s.ServiceName = 'Haircut'
    AND NOT EXISTS (
            SELECT 1 FROM appointment a
            WHERE a.CustomerID = c.CustomerID AND a.BarberID = b.BarberID
                AND a.ServiceID = s.ServiceID AND a.AppointmentDate = DATE_ADD(CURDATE(), INTERVAL 14 DAY)
                AND a.AppointmentTime = '10:00:00'
    );

INSERT INTO appointment (CustomerID, BarberID, ServiceID, AppointmentDate, AppointmentTime, Status)
SELECT c.CustomerID, b.BarberID, s.ServiceID, DATE_ADD(CURDATE(), INTERVAL 15 DAY), '11:00:00', 'Confirmed'
FROM customer c
CROSS JOIN barber b
CROSS JOIN service s
WHERE c.Email = 'demo.customer.two@example.test'
    AND b.FullName = 'Barber Sam'
    AND s.ServiceName = 'Haircut + Shave'
    AND NOT EXISTS (
            SELECT 1 FROM appointment a
            WHERE a.CustomerID = c.CustomerID AND a.BarberID = b.BarberID
                AND a.ServiceID = s.ServiceID AND a.AppointmentDate = DATE_ADD(CURDATE(), INTERVAL 15 DAY)
                AND a.AppointmentTime = '11:00:00'
    );

INSERT INTO appointment (CustomerID, BarberID, ServiceID, AppointmentDate, AppointmentTime, Status)
SELECT c.CustomerID, b.BarberID, s.ServiceID, DATE_ADD(CURDATE(), INTERVAL 16 DAY), '12:00:00', 'Confirmed'
FROM customer c
CROSS JOIN barber b
CROSS JOIN service s
WHERE c.Email = 'demo.customer.three@example.test'
    AND b.FullName = 'Barber Mike'
    AND s.ServiceName = 'Haircut + Shave'
    AND NOT EXISTS (
            SELECT 1 FROM appointment a
            WHERE a.CustomerID = c.CustomerID AND a.BarberID = b.BarberID
                AND a.ServiceID = s.ServiceID AND a.AppointmentDate = DATE_ADD(CURDATE(), INTERVAL 16 DAY)
                AND a.AppointmentTime = '12:00:00'
    );

INSERT INTO payment_transactions (AppointmentID, Amount, PaymentMethod)
SELECT a.AppointmentID, s.Price, 'Cash'
FROM appointment a
INNER JOIN customer c ON c.CustomerID = a.CustomerID
INNER JOIN service s ON s.ServiceID = a.ServiceID
WHERE c.Email = 'demo.customer.one@example.test'
    AND a.AppointmentDate = DATE_ADD(CURDATE(), INTERVAL 14 DAY) AND a.AppointmentTime = '10:00:00'
    AND NOT EXISTS (SELECT 1 FROM payment_transactions p WHERE p.AppointmentID = a.AppointmentID);

INSERT INTO payment_transactions (AppointmentID, Amount, PaymentMethod)
SELECT a.AppointmentID, s.Price, 'Card'
FROM appointment a
INNER JOIN customer c ON c.CustomerID = a.CustomerID
INNER JOIN service s ON s.ServiceID = a.ServiceID
WHERE c.Email = 'demo.customer.two@example.test'
    AND a.AppointmentDate = DATE_ADD(CURDATE(), INTERVAL 15 DAY) AND a.AppointmentTime = '11:00:00'
    AND NOT EXISTS (SELECT 1 FROM payment_transactions p WHERE p.AppointmentID = a.AppointmentID);

INSERT INTO payment_transactions (AppointmentID, Amount, PaymentMethod)
SELECT a.AppointmentID, s.Price, 'E-wallet'
FROM appointment a
INNER JOIN customer c ON c.CustomerID = a.CustomerID
INNER JOIN service s ON s.ServiceID = a.ServiceID
WHERE c.Email = 'demo.customer.three@example.test'
    AND a.AppointmentDate = DATE_ADD(CURDATE(), INTERVAL 16 DAY) AND a.AppointmentTime = '12:00:00'
    AND NOT EXISTS (SELECT 1 FROM payment_transactions p WHERE p.AppointmentID = a.AppointmentID);