<?php
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../core/Response.php';

class ProjectController
{
    private $projectModel;
    private $taskModel;

    public function __construct($db)
    {
        $this->projectModel = new Project($db);
        $this->taskModel = new Task($db);
    }

    public function get()
    {
        Response::json($this->projectModel->get());
    }

    public function getById($id)
    {
        $project = $this->projectModel->getById($id);

        if ($project) {
            Response::json(['success' => true, 'project' => $project]);
        } else {
            Response::json(['success' => false, 'error' => 'Project not found.'], 404);
        }
    }



    public function create($data)
    {
        $errors = [];

        $required = ['title', 'start_date', 'segment_length', 'total_segments', 'minimum_percentage'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $errors[] = "Field '$field' is required.";
            }
        }

        if (isset($data['segment_length']) && (!is_numeric($data['segment_length']) || $data['segment_length'] < 1 || $data['segment_length'] > 14)) {
            $errors[] = 'Segment length must be from 1 to 14.';
        }

        if (isset($data['total_segments']) && (!is_numeric($data['total_segments']) || $data['total_segments'] < 1 || $data['total_segments'] > 24)) {
            $errors[] = 'Total segments must be from 1 to 24.';
        }

        if (isset($data['minimum_percentage']) && (!is_numeric($data['minimum_percentage']) || $data['minimum_percentage'] < 1 || $data['minimum_percentage'] > 100)) {
            $errors[] = 'Minimum percentage must be from 1 to 100.';
        }

        if (!empty($errors)) {
            Response::json([
                'success' => false,
                'error' => join(", ", $errors)
            ], 400);
            return;
        }

        $result = $this->projectModel->create($data);

        if ($result) {
            Response::json([
                'success' => true,
                'project_id' => $result
            ]);
        } else {
            Response::json([
                'success' => false,
                'error' => 'Failed to create the project.'
            ], 500);
        }
    }


    public function update($id, $data)
    {
        $project = $this->projectModel->getById($id);

        if (!$project) {
            Response::json(['success' => false, 'error' => 'Project not found.'], 404);
            return;
        }

        $errors = [];
        $required = ['title'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $errors[] = "Field '$field' is required.";
            }
        }
        if (!empty($errors)) {
            Response::json(['success' => false, 'error' => join(", ", $errors)], 400);
            return;
        }

        $result = $this->projectModel->update($id, $data);

        if ($result) {
            Response::json(['success' => true]);
        } else {
            Response::json(['success' => false, 'error' => 'Failed to update project settings.'], 500);
        }
    }


    public function dateRange()
    {
        Response::json($this->projectModel->getVisibleDateRange());
    }

    public function segmentDates($projectId)
    {
        $today = (new DateTime())->format('Y-m-d');
        $this->taskModel->decayUnfinishedTasks($today);

        $dates = $this->projectModel->getSegmentDates($projectId);

        if (empty($dates)) {
            Response::json(['success' => false, 'error' => 'No dates found for this project'], 404);
            return;
        }

        $project = $this->projectModel->getById($projectId);
        $segmentLength = (int) $project['segment_length'];
        $tasks = $this->taskModel->getForRange($dates[0], end($dates), $projectId);

        // Группировка задач по дате
        $tasksByDate = [];
        foreach ($tasks as $task) {
            $date = $task['date'];
            if (!isset($tasksByDate[$date])) {
                $tasksByDate[$date] = [];
            }
            $tasksByDate[$date][] = [
                'id' => $task['id'],
                'title' => $task['title'],
                'completed' => (bool) $task['done'],
                'start_points' => $task['start_points'],
                'points' => $task['current_points']
            ];
        }

        // Построение сегментов
        $segments = [];
        $currentDate = (new DateTime())->format('Y-m-d');
        $i = 0;
        while ($i < count($dates)) {
            $segmentDates = array_slice($dates, $i, $segmentLength);
            if (empty($segmentDates))
                break;

            $segmentType = 'future';
            if ($segmentDates[count($segmentDates) - 1] < $currentDate) {
                $segmentType = 'past';
            } elseif ($segmentDates[0] <= $currentDate && $segmentDates[count($segmentDates) - 1] >= $currentDate) {
                $segmentType = 'current';
            }

            $segment = [
                'id' => count($segments) + 1,
                'type' => $segmentType,
                'slots' => []
            ];

            foreach ($segmentDates as $date) {
                $segment['slots'][] = [
                    'date' => $date,
                    'tasks' => $tasksByDate[$date] ?? []
                ];
            }

            $segments[] = $segment;
            $i += $segmentLength;
        }

        Response::json([
            'project' => $project,
            'segments' => $segments,
        ]);
    }

    public function segmentDetails($segmentNumber)
    {
        if (!is_numeric($segmentNumber) || $segmentNumber < 1) {
            Response::json([
                'success' => false,
                'error' => 'Invalid segment number'
            ], 400);
            return;
        }

        $dates = $this->projectModel->getDatesInSegment((int) $segmentNumber);

        if (empty($dates)) {
            Response::json([
                'success' => false,
                'error' => 'Segment not found or no dates'
            ], 404);
            return;
        }

        Response::json([
            'success' => true,
            'segment' => $segmentNumber,
            'dates' => $dates
        ]);
    }

    private function calculateTotalPoints(array $tasks)
    {
        $total = 0;
        foreach ($tasks as $task) {
            if ($task['done']) {
                $total += (int) $task['current_points'];
            }
        }
        return $total;
    }

    private function calculateMaxPoints(array $tasks)
    {
        $total = 0;
        foreach ($tasks as $task) {
            $total += (int) $task['start_points'];
        }
        return $total;
    }

    public function delete($id)
    {
        $result = $this->projectModel->delete($id);

        if ($result) {
            Response::json(['success' => true]);
        } else {
            Response::json(['success' => false, 'error' => 'Failed to delete the project.'], 500);
        }
    }


}
