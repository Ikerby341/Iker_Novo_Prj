<?php
// Simple router: si es passa ?action=github_login|github_callback, deleguem al controller corresponent
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'github_login') {
        require_once __DIR__ . '/../app/Controller/github_login.php';
        exit;
    } elseif ($action === 'github_callback') {
        require_once __DIR__ . '/../app/Controller/github_callback.php';
        exit;
    }
}

include_once 'Vista/vista.php';
?>