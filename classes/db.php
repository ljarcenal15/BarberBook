<?php
class Database
{
    private string $host;
    private string $dbName;
    private string $username;
    private string $password;
    private ?PDO $connection = null;

    public function __construct()
    {
        $environment = getenv('APP_ENV') ?: 'local';
        $host = getenv('DB_HOST');
        $dbName = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');

        if ($environment === 'production' && (!$host || !$dbName || !$username || !$password)) {
            throw new RuntimeException('Production database environment variables are not configured.');
        }

        $this->host = $host ?: 'localhost';
        $this->dbName = $dbName ?: 'barberbook_db';
        $this->username = $username ?: 'root';
        $this->password = $password ?: '';
    }

    public function getConnection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $exception) {
            error_log('Database connection failed: ' . $exception->getMessage());
            http_response_code(500);
            die('Database connection failed. Check the server configuration.');
        }

        return $this->connection;
    }
}