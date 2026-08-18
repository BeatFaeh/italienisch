<?php
declare(strict_types=1);

final class Database
{
    private mysqli $connection;

    public function __construct(array $config)
    {
        $this->connection = new mysqli(
            (string)$config['host'],
            (string)$config['username'],
            (string)$config['password'],
            (string)$config['database']
        );

        if ($this->connection->connect_errno) {
            throw new RuntimeException('Die Verbindung zur Datenbank ist fehlgeschlagen.');
        }

        $this->connection->set_charset('utf8mb4');
    }

    public function connection(): mysqli
    {
        return $this->connection;
    }
}
