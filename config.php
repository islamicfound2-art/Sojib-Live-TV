<?php

// ── DATABASE ─────────────────────────────────────────
define('DB_HOST', '');
define('DB_USER', '');
define('DB_PASS', ''); 
define('DB_NAME', '');

define('ADMIN_PASSWORD_HASH', '$2a$12$iBd8PjRyoxbPzdvAHSfnYuO7EB3Ho7IULr4SED0acissIuyrqYEmW');

// ── SESSION ──────────────────────────────────────────
define('ADMIN_SESSION_NAME', 'rktv_admin_session');

function rktv_get_db() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        return null;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
