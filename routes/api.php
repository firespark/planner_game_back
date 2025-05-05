<?php

function route($uri, $method, $taskController, $slotController)
{
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
            $slotId = (int)$matches[1];
            $taskController->getTasks($slotId);
            break;

        // --- Slots ---
        case $uri === '/api/slots' && $method === 'GET':
            $slotController->index();
            break;

        case $uri === '/api/slots/create' && $method === 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $slotController->create($data);
            break;

        // --- Default ---
        default:
            http_response_code(404);
            echo 'Not Found';
            break;
    }
}
