<?php
class Slot
{
    private $conn;
    private $table = 'slots';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = "SELECT * FROM {$this->table} ORDER BY date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($date)
    {
        $query = "INSERT INTO {$this->table} (date) VALUES (:date)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        return $stmt->execute();
    }
}
