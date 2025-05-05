<?php
require_once __DIR__ . './config/Database.php';
require_once __DIR__ . './controllers/TaskController.php';
require_once __DIR__ . './controllers/SlotController.php';
require_once __DIR__ . './controllers/SettingController.php';
require_once __DIR__ . './routes/api.php';

$db = (new Database())->connect();
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$taskController = new TaskController($db);
$slotController = new SlotController($db);
$settingController = new SettingController($db);

route($uri, $method, $taskController, $slotController, $settingController);
