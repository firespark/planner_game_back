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


    public function update($data)
    {
        $this->projectModel->update($data);
        Response::json(['success' => true]);
    }

    public function dateRange()
    {
        Response::json($this->projectModel->getVisibleDateRange());
    }

    public function segmentDates($projectId)
    {
        $dates = $this->projectModel->getSegmentDates($projectId);

        if (empty($dates)) {
            Response::json(['success' => false, 'error' => 'No dates found for this project'], 404);
            return;
        }

        $project = $this->projectModel->getById($projectId);
        $segmentLength = (int) $project['segment_length'];

        $tasks = $this->taskModel->getForRange($dates[0], end($dates));

        // группировка задач по дате
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
                'points' => $task['start_points']
            ];
        }

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

        Response::json($segments);
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

}
