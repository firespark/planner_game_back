<?php
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../core/Response.php';

class ProjectController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Project($db);
    }

    public function get()
    {
        Response::json($this->model->get());
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

        $result = $this->model->create($data);

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
        $this->model->update($data);
        Response::json(['success' => true]);
    }

    public function dateRange()
    {
        Response::json($this->model->getVisibleDateRange());
    }

    public function segmentDates($projectId)
    {
        Response::json($this->model->getSegmentDates($projectId));
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

        $dates = $this->model->getDatesInSegment((int) $segmentNumber);

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
