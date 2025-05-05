<?php
require_once __DIR__ . '/../config/Database.php';
class Setting
{
    private $conn;
    private $table = 'settings';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function get()
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data)
    {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET
            project_start_date = :project_start_date,
            segment_length = :segment_length,
            total_segments = :total_segments,
            minimum_percentage = :minimum_percentage"
        );

        return $stmt->execute([
            ':project_start_date' => $data['project_start_date'],
            ':segment_length' => $data['segment_length'],
            ':total_segments' => $data['total_segments'],
            ':minimum_percentage' => $data['minimum_percentage']
        ]);
    }
}

