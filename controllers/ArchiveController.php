<?php
require_once __DIR__ . '/../models/Archive.php';
require_once __DIR__ . '/../core/Response.php';

class ArchiveController
{
    private $archive;

    public function __construct($db)
    {
        $this->archive = new Archive($db);
    }

    public function get()
    {
        $archives = $this->archive->getAll();
        Response::json($archives);
    }

    public function getByTaskId($taskId)
    {
        $archive = $this->archive->getByTaskId($taskId);
        Response::json($archive);
    }

    public function delete($id)
    {
        if ($this->archive->delete($id)) {
            Response::json(['success' => true]);
        } else {
            Response::json(['error' => 'Not possible to delete the Archive'], 400);
        }
    }
}
