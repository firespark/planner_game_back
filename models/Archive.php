<?php
require_once __DIR__ . '/../config/Database.php';

class Archive
{
    private $conn;
    private $table = 'archive';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} ORDER BY completed_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByTaskId($taskId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE task_id = :task_id");
        $stmt->bindParam(':task_id', $taskId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
