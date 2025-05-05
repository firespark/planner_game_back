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
        if (!isset($data['date'], $data['title'], $data['points'])) {
            Response::json(['error' => 'Missing fields'], 400);
        }

        $id = $this->model->create($data);
        Response::json(['success' => true, 'id' => $id]);
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

    public function archiveTasks()
    {
        $this->model->archiveTasks();
        Response::json(['success' => true]);
    }
}
