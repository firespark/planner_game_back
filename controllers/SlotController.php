<?php
require_once __DIR__ . '/../models/Slot.php';
require_once __DIR__ . '/../core/Response.php';

class SlotController {
    private $model;

    public function __construct($db) {
        $this->model = new Slot($db);
    }

    public function index() {
        $slots = $this->model->getAll();
        Response::json($slots);
    }

    public function create($data) {
        if (!isset($data['date'])) {
            Response::json(['error' => 'Date is required'], 400);
            return;
        }

        $this->model->create($data['date']);
        Response::json(['success' => true]);
    }
}
