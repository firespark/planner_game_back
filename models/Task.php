<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Project.php';

class Task
{
    private $conn;
    private $table = 'tasks';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (project_id, date, title, start_points, current_points, done, created_at) VALUES (:project_id, :date, :title, :start_points, :start_points, 0, NOW())");

        $success = $stmt->execute([
            ':project_id' => $data['project_id'],
            ':date' => $data['date'],
            ':title' => $data['title'],
            ':start_points' => $data['points']
        ]);

        if (!$success)
            return;

        $id = $this->conn->lastInsertId();

        $project = new Project($this->conn);
        $project->addPoints($data['points']);

        return $id;
    }

    public function update($id, $title)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET title = :title WHERE id = :id");

        return $stmt->execute([
            ':title' => $title,
            ':id' => $id
        ]);
    }


    public function getForRange($start, $end)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE date BETWEEN :start AND :end");
        $stmt->execute([':start' => $start, ':end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function markDone($id)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET done = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function markUndone($id)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET done = 0 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function decayUnfinishedTasks($date)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET date = :today, current_points = ROUND(start_points * 0.9, 2) WHERE date < :today AND done = 0");
        $stmt->execute([':today' => $date]);
    }



}

