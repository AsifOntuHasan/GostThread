<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/ContentModerator.php";

header('Content-Type: application/json');

$user_hash = md5($_SERVER['REMOTE_ADDR'] . session_id());
$content = trim($_POST['content'] ?? '');
$media_path = trim($_POST['media_path'] ?? '');
$location = trim($_POST['location'] ?? '');

if ($content === '' && $media_path === '') {
    echo json_encode(['success' => false, 'message' => 'Please write something or add an image']);
    exit;
}

if (isset($_SESSION['last_post_time'])) {
    if (time() - $_SESSION['last_post_time'] < 15) {
        $remaining = 15 - (time() - $_SESSION['last_post_time']);
        echo json_encode(['success' => false, 'message' => "Please wait $remaining seconds before posting again"]);
        exit;
    }
}

$hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM posts WHERE user_hash = ? AND created_at > ?");
$stmt->bind_param("ss", $user_hash, $hour_ago);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['count'] >= 10) {
    echo json_encode(['success' => false, 'message' => 'Hourly limit reached. Please try again later.']);
    exit;
}

$moderator = new ContentModerator($conn);
$moderation = $moderator->moderateContent($content);

$status = $moderation['status'];
$flagged_words = !empty($moderation['flagged_words']) ? json_encode($moderation['flagged_words']) : null;

$stmt = $conn->prepare("INSERT INTO posts (user_hash, content, media_path, location, status, flagged_words) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $user_hash, $content, $media_path, $location, $status, $flagged_words);
$stmt->execute();

$post_id = $conn->insert_id;

if ($status === 'pending') {
    echo json_encode([
        'success' => true,
        'message' => 'Post submitted for review. It will appear once approved by admin.',
        'status' => 'pending',
        'post_id' => $post_id
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'Posted successfully!',
        'status' => 'approved',
        'post_id' => $post_id
    ]);
}

$_SESSION['last_post_time'] = time();
