<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/SettingController.php';
require_once __DIR__ . '/../controllers/ArchiveController.php';

function route()
{
    $db = (new Database())->connect();
    $uri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];

    $taskController = new TaskController($db);
    $settingController = new SettingController($db);
    $archiveController = new ArchiveController($db);

    switch (true) {
        // --- Tasks ---
        case $uri === '/api/tasks/create' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->create($data);
            break;

        case $uri === '/api/tasks/done' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->markDone($data['id']);
            break;

        case preg_match('#^/api/tasks/(\d+)$#', $uri, $matches) && $method === 'GET':
            $slotId = (int) $matches[1];
            $taskController->getBySlot($slotId);
            break;

        // --- Archive ---
        case $uri === '/api/archive' && $method === 'GET':
            $archiveController->get();
            break;

        case preg_match('#^/api/archive/task/(\d+)$#', $uri, $matches) && $method === 'GET':
            $taskId = (int) $matches[1];
            $archiveController->getByTaskId($taskId);
            break;

        case preg_match('#^/api/archive/(\d+)$#', $uri, $matches) && $method === 'DELETE':
            $archiveId = (int) $matches[1];
            $archiveController->delete($archiveId);
            break;

        // --- Settings ---
        case $uri === '/api/settings' && $method === 'GET':
            $settingController->get();
            break;

        case $uri === '/api/settings/update' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $settingController->update($data);
            break;

        // --- Default ---
        default:
            http_response_code(404);
            echo 'Not Found';
            break;
    }
}
