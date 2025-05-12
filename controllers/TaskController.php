<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../core/Response.php';

class TaskController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Task($db);
    }

    public function create($data)
    {
        $errors = [];

        $required = ['date', 'title', 'points'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $errors[] = "Field '$field' is required.";
            }
        }

        if (isset($data['date']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
            $errors[] = "Invalid date format (expected YYYY-MM-DD).";
        }

        if (isset($data['points']) && (!is_numeric($data['points']) || $data['points'] < 1 || $data['points'] > 100000)) {
            $errors[] = "Points must be a number from 1 to 100000.";
        }

        if (!empty($errors)) {
            Response::json([
                'success' => false,
                'error' => join(", ", $errors)
            ], 400);
            return;
        }

        $id = $this->model->create($data);

        if ($id) {
            Response::json(['success' => true, 'id' => $id]);
        } else {
            Response::json(['success' => false, 'error' => 'Failed to create the task'], 500);
        }
    }

    public function update($id, $data)
    {
        $errors = [];

        if (!$id || !is_numeric($id)) {
            $errors[] = "Valid 'id' is required.";
        }

        if (!isset($data['title']) || trim($data['title']) === '') {
            $errors[] = "Field 'title' is required.";
        }

        $completed = isset($data['completed']) ? (bool) $data['completed'] : false;

        if (!empty($errors)) {
            Response::json([
                'success' => false,
                'error' => join(", ", $errors)
            ], 400);
            return;
        }

        $result = $this->model->update($id, $data['title'], $completed);

        if ($result) {
            Response::json(['success' => true]);
        } else {
            Response::json(['success' => false, 'error' => 'Failed to update the task'], 500);
        }
    }


    public function getForRange($data)
    {
        if (!isset($data['start'], $data['end'])) {
            Response::json(['error' => 'Missing date range'], 400);
        }

        $tasks = $this->model->getForRange($data['start'], $data['end']);
        Response::json($tasks);
    }

    public function markDone($id)
    {
        $this->model->markDone($id);
        Response::json(['success' => true]);
    }

    public function markUndone($id)
    {
        $this->model->markUndone($id);
        Response::json(['success' => true]);
    }

    public function decayUnfinished()
    {
        $today = date('Y-m-d');
        $this->model->decayUnfinishedTasks($today);
        Response::json(['success' => true]);
    }

    public function delete($id)
    {
        if (!$id || !is_numeric($id)) {
            Response::json(['success' => false, 'error' => 'Valid task ID is required.'], 400);
            return;
        }

        $result = $this->model->delete($id);

        if ($result) {
            Response::json(['success' => true]);
        } else {
            Response::json(['success' => false, 'error' => 'Failed to delete the task'], 500);
        }
    }

}
