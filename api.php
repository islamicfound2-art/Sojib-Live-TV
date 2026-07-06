<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';

$conn = rktv_get_db();

if (!$conn) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'db_unavailable', 'channels' => []]);
    exit;
}

$where = ['is_active = 1'];
$params = [];
$types = '';

// Optional category filter
if (isset($_GET['cat']) && $_GET['cat'] !== '' && $_GET['cat'] !== 'all') {
    $allowed = ['sports', 'news', 'entertainment', 'movies', 'kids', 'others'];
    $cat = $_GET['cat'];
    if (in_array($cat, $allowed, true)) {
        $where[] = 'category = ?';
        $params[] = $cat;
        $types .= 's';
    }
}

// Optional popular-only filter
if (isset($_GET['popular']) && $_GET['popular'] === '1') {
    $where[] = 'is_popular = 1';
}

$sql = "SELECT id, name, url, logo, category, is_popular, sort_order
        FROM channels
        WHERE " . implode(' AND ', $where) . "
        ORDER BY sort_order ASC, name ASC";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'query_failed', 'channels' => []]);
    $conn->close();
    exit;
}

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$channels = [];
while ($row = $result->fetch_assoc()) {
    $channels[] = [
        'id'         => (int) $row['id'],
        'name'       => $row['name'],
        'url'        => $row['url'],
        'logo'       => $row['logo'],
        'category'   => $row['category'],
        'is_popular' => (bool) $row['is_popular'],
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['ok' => true, 'channels' => $channels]);
