<?php
require_once __DIR__ . '/../config/Database.php';

class Task
{
    private $conn;
    private $table = 'tasks';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getBySlot($slotId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE slot_id = :slot_id AND archived = 0");
        $stmt->bindParam(':slot_id', $slotId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (slot_id, title, points, done, archived, created_at)
            VALUES (:slot_id, :title, :points, 0, 0, NOW())");
        $stmt->execute([
            ':slot_id' => $data['slot_id'],
            ':title' => $data['title'],
            ':points' => $data['points']
        ]);
        return $this->conn->lastInsertId();
    }

    public function markDone($taskId)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET done = 1 WHERE id = :id");
        $stmt->bindParam(':id', $taskId);
        return $stmt->execute();
    }

    public function archive($taskId)
    {
        $task = $this->getById($taskId);
        if (!$task)
            return false;

        $stmt = $this->conn->prepare("INSERT INTO archive (task_id, completed_at, points_earned)
            VALUES (:task_id, NOW(), :points)");
        $stmt->execute([
            ':task_id' => $taskId,
            ':points' => $task['points']
        ]);

        $stmt = $this->conn->prepare("UPDATE {$this->table} SET archived = 1 WHERE id = :id");
        $stmt->bindParam(':id', $taskId);
        return $stmt->execute();
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
