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
        $this->model->create($data);
        Response::json(['success' => true]);
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

    public function segmentDates()
    {
        Response::json($this->model->getSegmentDates());
    }
}
