<?php

class Auth
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'httponly' => true,
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'samesite' => 'Lax'
            ]);
            session_start();
        }

        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS app_users (
                UserID INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                FullName VARCHAR(120) NOT NULL,
                Email VARCHAR(190) NOT NULL UNIQUE,
                PasswordHash VARCHAR(255) NOT NULL,
                CreatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $roleColumn = $this->connection->query("SHOW COLUMNS FROM app_users LIKE 'Role'")->fetch();
        if (!$roleColumn) {
            $this->connection->exec(
                "ALTER TABLE app_users ADD COLUMN Role ENUM('admin', 'staff') NOT NULL DEFAULT 'admin'"
            );
        }
    }

    public function register(string $name, string $email, string $password): string
    {
        if (!$this->canRegister()) {
            return 'The initial account is already set up. Please sign in.';
        }

        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Enter a name and a valid email address.';
        }
        if (strlen($password) < 8) {
            return 'Use a password with at least 8 characters.';
        }

        try {
            $statement = $this->connection->prepare(
                'INSERT INTO app_users (FullName, Email, PasswordHash, Role)
                 VALUES (:name, :email, :password, :role)'
            );
            $statement->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => 'admin'
            ]);

            $this->startUserSession((int) $this->connection->lastInsertId(), $name, $email);
            return '';
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return 'An account with that email already exists.';
            }
            return 'Unable to create the account. Please try again.';
        }
    }

    public function canRegister(): bool
    {
        $statement = $this->connection->prepare('SELECT COUNT(*) FROM app_users');
        $statement->execute();
        return (int) $statement->fetchColumn() === 0;
    }

    public function login(string $email, string $password): bool
    {
        $statement = $this->connection->prepare(
            'SELECT UserID, FullName, Email, PasswordHash, Role FROM app_users WHERE Email = :email LIMIT 1'
        );
        $statement->execute([':email' => strtolower(trim($email))]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['PasswordHash'])) {
            return false;
        }

        if (password_needs_rehash($user['PasswordHash'], PASSWORD_DEFAULT)) {
            $update = $this->connection->prepare(
                'UPDATE app_users SET PasswordHash = :password WHERE UserID = :id'
            );
            $update->execute([
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':id' => $user['UserID']
            ]);
        }

        $this->startUserSession((int) $user['UserID'], $user['FullName'], $user['Email'], $user['Role']);
        return true;
    }

    public function currentUser(): ?array
    {
        if (!isset($_SESSION['user']['id'])) {
            return null;
        }

        $statement = $this->connection->prepare(
            'SELECT UserID, FullName, Email, Role FROM app_users WHERE UserID = :id LIMIT 1'
        );
        $statement->execute([':id' => (int) $_SESSION['user']['id']]);
        $user = $statement->fetch();

        if (!$user) {
            unset($_SESSION['user']);
            return null;
        }

        $_SESSION['user'] = [
            'id' => (int) $user['UserID'],
            'name' => $user['FullName'],
            'email' => $user['Email'],
            'role' => $user['Role']
        ];
        return $_SESSION['user'];
    }

    public function createStaff(string $name, string $email, string $password): string
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Enter a name and a valid email address.';
        }
        if (strlen($password) < 8) {
            return 'Use a password with at least 8 characters.';
        }

        try {
            $statement = $this->connection->prepare(
                'INSERT INTO app_users (FullName, Email, PasswordHash, Role)
                 VALUES (:name, :email, :password, :role)'
            );
            $statement->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => 'staff'
            ]);
            return '';
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return 'An account with that email already exists.';
            }
            return 'Unable to create the staff account. Please try again.';
        }
    }

    public function getUsers(): array
    {
        $statement = $this->connection->prepare(
            'SELECT UserID, FullName, Email, Role, CreatedAt FROM app_users ORDER BY CreatedAt, UserID'
        );
        $statement->execute();
        return $statement->fetchAll();
    }

    public function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function isValidCsrfToken(?string $token): bool
    {
        return is_string($token)
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $parameters['path'],
                'domain' => $parameters['domain'],
                'secure' => $parameters['secure'],
                'httponly' => $parameters['httponly'],
                'samesite' => $parameters['samesite'] ?? 'Lax'
            ]);
        }
        session_destroy();
    }

    private function startUserSession(int $id, string $name, string $email, string $role): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'role' => $role
        ];
        unset($_SESSION['csrf_token']);
    }
}