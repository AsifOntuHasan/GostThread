<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/ContentModerator.php";

header('Content-Type: application/json');

$user_hash = md5($_SERVER['REMOTE_ADDR'] . session_id());
$post_id = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

if ($content === '') {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

if (isset($_SESSION['last_comment_time'])) {
    if (time() - $_SESSION['last_comment_time'] < 5) {
        $remaining = 5 - (time() - $_SESSION['last_comment_time']);
        echo json_encode(['success' => false, 'message' => "Please wait $remaining seconds"]);
        exit;
    }
}

$stmt = $conn->prepare("SELECT id, status FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

if ($post['status'] !== 'approved') {
    echo json_encode(['success' => false, 'message' => 'Cannot comment on this post']);
    exit;
}

$moderator = new ContentModerator($conn);
$moderation = $moderator->moderateContent($content);

$status = $moderation['status'];
$flagged_words = !empty($moderation['flagged_words']) ? json_encode($moderation['flagged_words']) : null;

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_hash, content, status, flagged_words) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $post_id, $user_hash, $content, $status, $flagged_words);
$stmt->execute();

if ($status === 'approved') {
    $update = $conn->prepare("UPDATE posts SET comment_count = comment_count + 1 WHERE id = ?");
    $update->bind_param("i", $post_id);
    $update->execute();
}

$_SESSION['last_comment_time'] = time();

echo json_encode([
    'success' => true,
    'message' => $status === 'pending' ? 'Comment submitted for review' : 'Comment added',
    'status' => $status,
    'comment' => [
        'content' => htmlspecialchars($content),
        'time_ago' => 'Just now'
    ]
]);
