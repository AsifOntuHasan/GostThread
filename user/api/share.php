<?php
session_start();
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

$user_hash = md5($_SERVER['REMOTE_ADDR'] . session_id());
$post_id = (int)($_POST['post_id'] ?? 0);
$platform = $_POST['platform'] ?? 'ghostthread';

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

$stmt = $conn->prepare("SELECT id, share_count FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

$insert = $conn->prepare("INSERT INTO shares (post_id, user_hash, platform) VALUES (?, ?, ?)");
$insert->bind_param("iss", $post_id, $user_hash, $platform);
$insert->execute();

$update = $conn->prepare("UPDATE posts SET share_count = share_count + 1 WHERE id = ?");
$update->bind_param("i", $post_id);
$update->execute();

$new_count = $post['share_count'] + 1;

echo json_encode([
    'success' => true,
    'message' => 'Shared successfully',
    'share_count' => $new_count
]);
