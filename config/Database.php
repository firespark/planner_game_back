<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

class Database
{
    private $conn;

    public function connect()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $host = $_ENV['DB_HOST'];
        $db_name = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];

        try {
            $this->conn = new PDO(
                "mysql:host={$host};dbname={$db_name};charset=utf8",
                $username,
                $password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}
