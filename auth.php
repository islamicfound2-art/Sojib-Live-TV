<?php

require_once __DIR__ . '/../config.php';

session_name(ADMIN_SESSION_NAME);
session_start();

function rktv_require_admin($isApi = false)
{
    if (empty($_SESSION['rktv_admin_logged_in'])) {
        if ($isApi) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'unauthorized']);
        } else {
            header('Location: login.php');
        }
        exit;
    }
}
