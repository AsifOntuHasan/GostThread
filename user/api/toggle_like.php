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

$stmt = $conn->prepare("SELECT id, like_count FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

$check = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_hash = ?");
$check->bind_param("is", $post_id, $user_hash);
$check->execute();
$exists = $check->get_result()->num_rows > 0;

if ($exists) {
    $delete = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_hash = ?");
    $delete->bind_param("is", $post_id, $user_hash);
    $delete->execute();
    
    $update = $conn->prepare("UPDATE posts SET like_count = GREATEST(like_count - 1, 0) WHERE id = ?");
    $update->bind_param("i", $post_id);
    $update->execute();
    
    $new_count = max(0, $post['like_count'] - 1);
    
    echo json_encode([
        'success' => true,
        'action' => 'unliked',
        'like_count' => $new_count
    ]);
} else {
    $insert = $conn->prepare("INSERT INTO likes (post_id, user_hash) VALUES (?, ?)");
    $insert->bind_param("is", $post_id, $user_hash);
    $insert->execute();
    
    $update = $conn->prepare("UPDATE posts SET like_count = like_count + 1 WHERE id = ?");
    $update->bind_param("i", $post_id);
    $update->execute();
    
    $new_count = $post['like_count'] + 1;
    
    echo json_encode([
        'success' => true,
        'action' => 'liked',
        'like_count' => $new_count
    ]);
}
