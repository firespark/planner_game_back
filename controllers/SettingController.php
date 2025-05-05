<?php
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../core/Response.php';

class SettingController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Setting($db);
    }

    public function get()
    {
        Response::json($this->model->get());
    }

    public function update($data)
    {
        $this->model->update($data);
        Response::json(['success' => true]);
    }
}

