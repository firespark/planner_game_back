<?php
require_once __DIR__ . '/../config/Database.php';

class Project
{
    private $conn;
    private $table = 'projects';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function get()
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table}");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($projects as &$project) {
            $project['end_date'] = $this->calculateEndDate($project);
            $project['max_points'] = $this->calculateMaxPoints($project['id']);
        }

        return $projects;
    }

    public function create($data)
    {
        $stmt = $this->conn->prepare("
        INSERT INTO {$this->table}
            (title, start_date, segment_length, total_segments, minimum_percentage, total_points)
        VALUES
            (:title, :start_date, :segment_length, :total_segments, :minimum_percentage, 0)
    ");

        $success = $stmt->execute([
            ':title' => $data['title'],
            ':start_date' => $data['start_date'],
            ':segment_length' => $data['segment_length'],
            ':total_segments' => $data['total_segments'],
            ':minimum_percentage' => $data['minimum_percentage']
        ]);

        return $success ? $this->conn->lastInsertId() : false;
    }

    public function update($data)
    {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table} SET
                title = :title,
                start_date = :start_date,
                segment_length = :segment_length,
                total_segments = :total_segments,
                minimum_percentage = :minimum_percentage
            WHERE id = 1
        ");

        return $stmt->execute([
            ':title' => $data['title'],
            ':start_date' => $data['start_date'],
            ':segment_length' => $data['segment_length'],
            ':total_segments' => $data['total_segments'],
            ':minimum_percentage' => $data['minimum_percentage']
        ]);
    }

    public function addPoints($points)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET total_points = total_points + :points WHERE id = 1");
        $stmt->execute([':points' => $points]);
    }

    public function getVisibleDateRange()
    {
        $project = $this->get();
        $today = new DateTime();
        $start = $today;
        $end = (new DateTime($project['start_date']))->modify('+' . ($project['segment_length'] * $project['total_segments'] - 1) . ' days');

        return [
            'start' => max($start, new DateTime($project['start_date']))->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ];
    }

    public function getSegmentDates($projectId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$project) {
            return [];
        }

        $startDate = new DateTime($project['start_date']);
        $totalDays = $project['segment_length'] * $project['total_segments'];

        $dates = [];
        for ($i = 0; $i < $totalDays; $i++) {
            $dates[] = $startDate->format('Y-m-d');
            $startDate->modify('+1 day');
        }

        return $dates;
    }


    public function getDatesInSegment($segmentNumber)
    {
        $project = $this->get();
        if (!$project || $segmentNumber < 1 || $segmentNumber > $project['total_segments']) {
            return [];
        }

        $segmentLength = (int) $project['segment_length'];
        $startDate = new DateTime($project['start_date']);
        $startDate->modify('+' . ($segmentNumber - 1) * $segmentLength . ' days');

        $dates = [];
        for ($i = 0; $i < $segmentLength; $i++) {
            $dates[] = $startDate->format('Y-m-d');
            $startDate->modify('+1 day');
        }

        return $dates;
    }

    public function calculateEndDate($project)
    {
        $start = new DateTime($project['start_date']);
        $days = ($project['segment_length'] * $project['total_segments']) - 1;
        $start->modify("+{$days} days");
        return $start->format('Y-m-d');
    }

    public function calculateMaxPoints($projectId)
    {
        $stmt = $this->conn->prepare("SELECT SUM(start_points) as total FROM tasks WHERE project_id = :id");
        $stmt->execute([':id' => $projectId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


}
