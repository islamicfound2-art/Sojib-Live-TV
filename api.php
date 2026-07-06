<?php

require_once __DIR__ . '/auth.php';
rktv_require_admin(true);

header('Content-Type: application/json; charset=utf-8');

$conn = rktv_get_db();
if (!$conn) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'db_unavailable']);
    exit;
}

$ALLOWED_CATEGORIES = ['sports', 'news', 'entertainment', 'movies', 'kids', 'others'];

$method = $_SERVER['REQUEST_METHOD'];

// ── LIST ────────────────────────────────────────────────
if ($method === 'GET') {
    $result = $conn->query("SELECT * FROM channels ORDER BY sort_order ASC, name ASC");
    $channels = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $row['is_popular'] = (bool) $row['is_popular'];
        $row['is_active'] = (bool) $row['is_active'];
        $row['sort_order'] = (int) $row['sort_order'];
        $channels[] = $row;
    }
    echo json_encode(['ok' => true, 'channels' => $channels]);
    $conn->close();
    exit;
}

// ── WRITE ACTIONS ───────────────────────────────────────
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $logo = trim($_POST['logo'] ?? '');
        $category = $_POST['category'] ?? 'others';
        $isPopular = isset($_POST['is_popular']) && $_POST['is_popular'] === '1' ? 1 : 0;
        $isActive = isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0;
        $sortOrder = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;

        if ($name === '' || $url === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'name_and_url_required']);
            exit;
        }
        if (!in_array($category, $ALLOWED_CATEGORIES, true)) {
            $category = 'others';
        }
        if ($logo === '') {
            $logo = null;
        }

        if ($action === 'create') {
            $stmt = $conn->prepare("
                INSERT INTO channels (name, url, logo, category, is_popular, is_active, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ssssiii', $name, $url, $logo, $category, $isPopular, $isActive, $sortOrder);
            $stmt->execute();
            $newId = $conn->insert_id;
            $stmt->close();
            echo json_encode(['ok' => true, 'id' => $newId]);
        } else {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(422);
                echo json_encode(['ok' => false, 'error' => 'invalid_id']);
                exit;
            }
            $stmt = $conn->prepare("
                UPDATE channels
                SET name = ?, url = ?, logo = ?, category = ?, is_popular = ?, is_active = ?, sort_order = ?
                WHERE id = ?
            ");
            $stmt->bind_param('ssssiiii', $name, $url, $logo, $category, $isPopular, $isActive, $sortOrder, $id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['ok' => true]);
        }
        $conn->close();
        exit;
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'invalid_id']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM channels WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['ok' => true]);
        $conn->close();
        exit;
    }

    if ($action === 'toggle_popular' || $action === 'toggle_active') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'invalid_id']);
            exit;
        }
        $field = $action === 'toggle_popular' ? 'is_popular' : 'is_active';
        $stmt = $conn->prepare("UPDATE channels SET $field = NOT $field WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['ok' => true]);
        $conn->close();
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'unknown_action']);
    $conn->close();
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
