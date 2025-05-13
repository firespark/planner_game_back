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

    public function update($id, $title, $completed)
    {
        $stmt = $this->conn->prepare("
        UPDATE {$this->table}
        SET title = :title, done = :completed
        WHERE id = :id
    ");

        return $stmt->execute([
            ':title' => $title,
            ':completed' => $completed ? 1 : 0,
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

    public function decayUnfinishedTasks($today)
    {
        $stmt = $this->conn->prepare("
        SELECT id, start_points, date
        FROM {$this->table}
        WHERE date < :today AND done = 0
    ");
        $stmt->execute([':today' => $today]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $currentDate = new DateTime($today);

        foreach ($tasks as $task) {
            $taskDate = new DateTime($task['date']);

            $daysLate = $taskDate->diff($currentDate)->days;

            $newPoints = max(0, $task['start_points'] * (1 - 0.1 * $daysLate));

            $updateStmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET
                current_points = :points,
                date = :today
            WHERE id = :id
        ");
            $updateStmt->execute([
                ':points' => $newPoints,
                ':today' => $today,
                ':id' => $task['id']
            ]);
        }
    }


    public function delete($id)
    {

        $stmt = $this->conn->prepare("SELECT current_points, project_id FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            return false;
        }

        $points = $task['current_points'];
        $projectId = $task['project_id'];

        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $success = $stmt->execute([':id' => $id]);

        if (!$success) {
            return false;
        }

        $project = new Project($this->conn);
        $project->addPoints(-$points);

        return true;
    }

}

