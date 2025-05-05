<?php
class Setting {
    private $conn;
    private $table = 'settings';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table} LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($minPercentage) {
        $query = "UPDATE {$this->table} SET min_percentage = :min_percentage";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':min_percentage', $minPercentage);
        $stmt->execute();
    }
}
