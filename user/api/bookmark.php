<?php
session_start();
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

$user_hash = md5($_SERVER['REMOTE_ADDR'] . session_id());
$post_id = (int)($_POST['post_id'] ?? 0);

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

$check = $conn->prepare("SELECT id FROM bookmarks WHERE post_id = ? AND user_hash = ?");
$check->bind_param("is", $post_id, $user_hash);
$check->execute();
$exists = $check->get_result()->num_rows > 0;

if ($exists) {
    $delete = $conn->prepare("DELETE FROM bookmarks WHERE post_id = ? AND user_hash = ?");
    $delete->bind_param("is", $post_id, $user_hash);
    $delete->execute();
    
    echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Bookmark removed']);
} else {
    $insert = $conn->prepare("INSERT INTO bookmarks (post_id, user_hash) VALUES (?, ?)");
    $insert->bind_param("is", $post_id, $user_hash);
    $insert->execute();
    
    echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Bookmarked']);
}
