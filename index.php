<?php
require_once __DIR__ . './config/Database.php';
require_once __DIR__ . './controllers/TaskController.php';

$db = (new Database())->connect();
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$taskController = new TaskController($db);

if ($uri === '/api/tasks/create' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $taskController->create($data);
}
elseif ($uri === '/api/tasks/done' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $taskController->markDone($data['id']);
}
elseif (preg_match('#^/api/tasks/(\d+)$#', $uri, $matches) && $method === 'GET') {
    $slotId = (int) $matches[1];
    $taskController->getTasks($slotId);
}
else {
    http_response_code(404);
    echo 'Not Found';
}
