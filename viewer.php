<?php
header('Content-Type: text/plain');

require_once __DIR__ . '/config.php';

// Sanitize input
$id = isset($_GET['id']) ? substr(preg_replace('/[^a-zA-Z0-9]/', '', $_GET['id']), 0, 64) : '';

if (empty($id)) {
    echo 0;
    exit;
}

$conn = rktv_get_db();

if (!$conn) {
    // Connection failed — return 0 silently (no 500)
    echo 0;
    exit;
}

// Upsert this viewer
$stmt = $conn->prepare("
    INSERT INTO viewers (viewer_id, last_seen)
    VALUES (?, NOW())
    ON DUPLICATE KEY UPDATE last_seen = NOW()
");

if ($stmt) {
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();
}

// Remove stale viewers (inactive > 30 seconds)
$conn->query("
    DELETE FROM viewers
    WHERE last_seen < NOW() - INTERVAL 30 SECOND
");

// Count active viewers
$result = $conn->query("SELECT COUNT(*) AS total FROM viewers");

if ($result) {
    $row = $result->fetch_assoc();
    echo (int) $row['total'];
} else {
    echo 0;
}

$conn->close();
