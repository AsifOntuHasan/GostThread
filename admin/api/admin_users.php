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

switch ($action) {
    case 'create':
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'moderator';
        
        if (!$username || !$password) {
            echo json_encode(['success' => false, 'message' => 'Username and password required']);
            exit;
        }
        
        $valid_roles = ['super_admin', 'moderator'];
        if (!in_array($role, $valid_roles)) {
            $role = 'moderator';
        }
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hash, $role);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Admin created']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Username may already exist']);
        }
        break;
        
    case 'update':
        $id = (int)($input['id'] ?? 0);
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'moderator';
        
        if (!$id || !$username) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }
        
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin_users SET username = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $hash, $role, $id);
        } else {
            $stmt = $conn->prepare("UPDATE admin_users SET username = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $role, $id);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Admin updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update']);
        }
        break;
        
    case 'toggle':
        $id = (int)($input['id'] ?? 0);
        $is_active = (int)($input['is_active'] ?? 0);
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE admin_users SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_active, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update']);
        }
        break;
        
    case 'delete':
        $id = (int)($input['id'] ?? 0);
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }
        
        if ($id == $_SESSION['admin_id']) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete yourself']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Admin deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
