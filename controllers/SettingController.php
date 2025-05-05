<?php
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../core/Response.php';

class SettingController {
    private $model;

    public function __construct($db) {
        $this->model = new Setting($db);
    }

    public function get() {
        $settings = $this->model->getAll();
        Response::json($settings);
    }

    public function update($data) {
        if (!isset($data['min_percentage'])) {
            Response::json(['error' => 'Min Percentage is required'], 400);
            return;
        }

        $this->model->update($data['min_percentage']);
        Response::json(['success' => true]);
    }
}
