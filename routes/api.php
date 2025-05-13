<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/ProjectController.php';

function route()
{
    $db = (new Database())->connect();
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    $taskController = new TaskController($db);
    $projectController = new ProjectController($db);

    switch (true) {
        // --- Projects ---
        case $uri === '/api/projects' && $method === 'GET':
            $projectController->get();
            break;

        case preg_match('#^/api/projects/(\d+)$#', $uri, $matches) && $method === 'GET':
            $projectController->getById((int) $matches[1]);
            break;

        case $uri === '/api/projects/create' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $projectController->create($data);
            break;

        case preg_match('#^/api/projects/update/(\d+)$#', $uri, $matches) && $method === 'POST':
            $id = (int) $matches[1];
            $data = json_decode(file_get_contents('php://input'), true);
            $projectController->update($id, $data);
            break;

        case preg_match('#^/api/projects/delete/(\d+)$#', $uri, $matches) && $method === 'DELETE':
            $id = (int) $matches[1];
            $projectController->delete($id);
            break;

        case $uri === '/api/projects/date-range' && $method === 'GET':
            $projectController->dateRange();
            break;

        case preg_match('#^/api/projects/(\d+)/dates$#', $uri, $matches) && $method === 'GET':
            $projectController->segmentDates((int) $matches[1]);
            break;

        // --- Tasks ---
        case $uri === '/api/tasks/range' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->getForRange($data);
            break;

        case $uri === '/api/tasks/create' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->create($data);
            break;

        case preg_match('#^/api/tasks/update/(\d+)$#', $uri, $matches) && $method === 'PUT':
            $id = (int) $matches[1];
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->update($id, $data);
            break;


        case $uri === '/api/tasks/done' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->markDone($data['id']);
            break;

        case $uri === '/api/tasks/undone' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->markUndone($data['id']);
            break;

        case preg_match('#^/api/tasks/delete/(\d+)$#', $uri, $matches) && $method === 'DELETE':
            $id = (int) $matches[1];
            $taskController->delete($id);
            break;

        case $uri === '/api/tasks/decay' && $method === 'GET':
            $taskController->decayUnfinished();
            break;

        // --- Default ---
        default:
            http_response_code(404);
            echo 'Not Found';
            break;
    }
}
