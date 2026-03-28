<?php
session_start();
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

$user_hash = md5($_SERVER['REMOTE_ADDR'] . session_id());

$stmt = $conn->prepare("
    SELECT p.*, b.created_at as bookmarked_at,
           CASE WHEN l.post_id IS NOT NULL THEN 1 ELSE 0 END AS liked
    FROM bookmarks b
    JOIN posts p ON b.post_id = p.id
    LEFT JOIN likes l ON p.id = l.post_id AND l.user_hash = ?
    WHERE b.user_hash = ? AND p.status = 'approved'
    ORDER BY b.created_at DESC
");

$stmt->bind_param("ss", $user_hash, $user_hash);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = [
        'id' => $row['id'],
        'content' => htmlspecialchars($row['content']),
        'media_path' => $row['media_path'],
        'like_count' => $row['like_count'],
        'comment_count' => $row['comment_count'],
        'share_count' => $row['share_count'],
        'liked' => (bool)$row['liked'],
        'time_ago' => timeAgo($row['created_at']),
        'bookmarked_time' => timeAgo($row['bookmarked_at'])
    ];
}

echo json_encode(['success' => true, 'posts' => $posts]);

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j', $time);
}
