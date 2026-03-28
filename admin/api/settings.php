<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$config_path = __DIR__ . '/../../../user/config/db.php';
require_once $config_path;

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action !== 'save') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

$settings = $input['settings'] ?? [];
$success = true;

foreach ($settings as $key => $value) {
    $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("sss", $key, $value, $value);
    if (!$stmt->execute()) {
        $success = false;
    }
}

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save some settings']);
}
