-- BarberBook clean deployment schema. Contains no customer, appointment,
-- account, credential, or payment-history records.
-- Import into an empty database created for this application.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS barber (
    BarberID INT NOT NULL AUTO_INCREMENT,
    FullName VARCHAR(100) NOT NULL,
    Specialty VARCHAR(100) DEFAULT NULL,
    Availability VARCHAR(100) DEFAULT NULL,
    PRIMARY KEY (BarberID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service (
    ServiceID INT NOT NULL AUTO_INCREMENT,
    ServiceName VARCHAR(100) NOT NULL,
    Price DECIMAL(8,2) NOT NULL,
    Duration INT NOT NULL,
    PRIMARY KEY (ServiceID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer (
    CustomerID INT NOT NULL AUTO_INCREMENT,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    ContactNumber VARCHAR(20) NOT NULL,
    PRIMARY KEY (CustomerID),
    UNIQUE KEY uq_customer_email (Email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS appointment (
    AppointmentID INT NOT NULL AUTO_INCREMENT,
    CustomerID INT NOT NULL,
    BarberID INT NOT NULL,
    ServiceID INT NOT NULL,
    AppointmentDate DATE NOT NULL,
    AppointmentTime TIME NOT NULL,
    Status ENUM('Pending','Confirmed','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
    PRIMARY KEY (AppointmentID),
    KEY fk_appointment_customer (CustomerID),
    KEY fk_appointment_barber (BarberID),
    KEY fk_appointment_service (ServiceID),
    KEY idx_appointment_slot (BarberID, AppointmentDate, AppointmentTime, Status),
    CONSTRAINT fk_appointment_customer FOREIGN KEY (CustomerID) REFERENCES customer (CustomerID) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_appointment_barber FOREIGN KEY (BarberID) REFERENCES barber (BarberID) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_appointment_service FOREIGN KEY (ServiceID) REFERENCES service (ServiceID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_users (
    UserID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    FullName VARCHAR(120) NOT NULL,
    Email VARCHAR(190) NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Role ENUM('admin','staff') NOT NULL DEFAULT 'admin',
    PRIMARY KEY (UserID),
    UNIQUE KEY uq_app_users_email (Email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
    TransactionID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    AppointmentID INT NOT NULL,
    Amount DECIMAL(10,2) NOT NULL,
    PaymentMethod VARCHAR(30) NOT NULL,
    CreatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (TransactionID),
    KEY idx_payment_appointment (AppointmentID),
    KEY idx_payment_created (CreatedAt),
    CONSTRAINT fk_payment_appointment FOREIGN KEY (AppointmentID) REFERENCES appointment (AppointmentID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO barber (BarberID, FullName, Specialty, Availability) VALUES
    (1, 'Barber Mike', NULL, NULL),
    (2, 'Barber Sam', NULL, NULL)
ON DUPLICATE KEY UPDATE FullName = VALUES(FullName);

INSERT INTO service (ServiceID, ServiceName, Price, Duration) VALUES
    (1, 'Haircut', 250.00, 30),
    (2, 'Haircut + Shave', 350.00, 45)
ON DUPLICATE KEY UPDATE ServiceName = VALUES(ServiceName), Price = VALUES(Price), Duration = VALUES(Duration);

SET FOREIGN_KEY_CHECKS = 1;