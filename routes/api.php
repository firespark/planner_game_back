<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/ProjectController.php';

function route()
{
    $db = (new Database())->connect();
    $uri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];

    $taskController = new TaskController($db);
    $projectController = new ProjectController($db);

    switch (true) {
        // --- Tasks ---
        case $uri === '/api/tasks/range' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->getForRange($data);
            break;

        case $uri === '/api/tasks/create' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->create($data);
            break;

        case $uri === '/api/tasks/done' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->markDone($data['id']);
            break;

        case $uri === '/api/tasks/undone' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->markUndone($data['id']);
            break;

        case $uri === '/api/tasks/decay' && $method === 'GET':
            $taskController->decayUnfinished();
            break;

        case $uri === '/api/tasks/archive' && $method === 'GET':
            $data = json_decode(file_get_contents('php://input'), true);
            $taskController->archiveTasks();
            break;

        // --- Projects ---
        case $uri === '/api/projects' && $method === 'GET':
            $projectController->get();
            break;

        case $uri === '/api/projects/create' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $projectController->create($data);
            break;

        case $uri === '/api/projects/update' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $projectController->update($data);
            break;

        case $uri === '/api/projects/date-range' && $method === 'GET':
            $projectController->dateRange();
            break;

        case preg_match('#^/api/projects/(\d+)/dates$#', $uri, $matches) && $method === 'GET':
            $projectController->segmentDates((int) $matches[1]);
            break;

        // --- Default ---
        default:
            http_response_code(404);
            echo 'Not Found';
            break;
    }
}
