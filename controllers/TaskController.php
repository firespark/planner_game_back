<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../core/Response.php';

class TaskController
{
    private $task;

    public function __construct($db)
    {
        $this->task = new Task($db);
    }

    public function create($data)
    {
        $id = $this->task->create($data);
        Response::json(['success' => true, 'id' => $id]);
    }

    public function markDone($taskId)
    {
        $this->task->markDone($taskId);
        $this->task->archive($taskId);
        Response::json(['success' => true]);
    }

    public function getBySlot($slotId)
    {
        $tasks = $this->task->getBySlot($slotId);
        Response::json($tasks);
    }
}
