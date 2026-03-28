<?php
session_start();
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

$user_hash = md5($_SERVER['REMOTE_ADDR'] . session_id());

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sort = isset($_GET['sort']) && $_GET['sort'] === 'random' ? 'random' : 'recent';
$per_page = 15;
$offset = ($page - 1) * $per_page;

$order_by = $sort === 'random' ? 'RAND()' : 'p.created_at DESC';

$sql = "
SELECT 
    p.id,
    p.content,
    p.media_path,
    p.media_type,
    p.location,
    p.created_at,
    p.like_count,
    p.comment_count,
    p.share_count,
    CASE WHEN l.post_id IS NOT NULL THEN 1 ELSE 0 END AS liked,
    CASE WHEN b.post_id IS NOT NULL THEN 1 ELSE 0 END AS bookmarked
FROM posts p
LEFT JOIN likes l ON p.id = l.post_id AND l.user_hash = ?
LEFT JOIN bookmarks b ON p.id = b.post_id AND b.user_hash = ?
WHERE p.status = 'approved'
ORDER BY $order_by
LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $user_hash, $user_hash, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $row['liked'] = (bool)$row['liked'];
    $row['bookmarked'] = (bool)$row['bookmarked'];
    $row['time_ago'] = timeAgo($row['created_at']);
    
    $cstmt = $conn->prepare("
        SELECT content, created_at 
        FROM comments 
        WHERE post_id = ? AND status = 'approved' 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $cstmt->bind_param("i", $row['id']);
    $cstmt->execute();
    $comments = $cstmt->get_result();
    
    $row['comments'] = [];
    while ($c = $comments->fetch_assoc()) {
        $row['comments'][] = [
            'content' => htmlspecialchars($c['content']),
            'time_ago' => timeAgo($c['created_at'])
        ];
    }
    $row['comments'] = array_reverse($row['comments']);
    
    $posts[] = $row;
}

$count_stmt = $conn->query("SELECT COUNT(*) as total FROM posts WHERE status = 'approved'");
$total = $count_stmt->fetch_assoc()['total'];

echo json_encode([
    'success' => true,
    'posts' => $posts,
    'page' => $page,
    'total_pages' => ceil($total / $per_page),
    'total_posts' => (int)$total
]);

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j', $time);
}
