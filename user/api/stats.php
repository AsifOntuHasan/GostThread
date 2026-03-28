<?php
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

$result = [
    'total_posts' => 0,
    'total_comments' => 0,
    'total_likes' => 0,
    'total_shares' => 0,
    'recent_activity' => []
];

$posts = $conn->query("SELECT COUNT(*) as c FROM posts WHERE status = 'approved'");
if ($posts) $result['total_posts'] = $posts->fetch_assoc()['c'];

$comments = $conn->query("SELECT COUNT(*) as c FROM comments WHERE status = 'approved'");
if ($comments) $result['total_comments'] = $comments->fetch_assoc()['c'];

$likes = $conn->query("SELECT COUNT(*) as c FROM likes");
if ($likes) $result['total_likes'] = $likes->fetch_assoc()['c'];

$shares = $conn->query("SELECT COUNT(*) as c FROM shares");
if ($shares) $result['total_shares'] = $shares->fetch_assoc()['c'];

// Get recent activity
$recent = $conn->query("
    (SELECT 'post' as type, id, content, created_at FROM posts WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'comment' as type, id, content, created_at FROM comments WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'like' as type, id, '' as content, created_at FROM likes ORDER BY created_at DESC LIMIT 2)
    ORDER BY created_at DESC LIMIT 8
");

$activity = [];
while ($row = $recent->fetch_assoc()) {
    $time = timeAgo($row['created_at']);
    if ($row['type'] === 'post') {
        $preview = strlen($row['content']) > 40 ? substr($row['content'], 0, 40) . '...' : $row['content'];
        $activity[] = ['type' => 'post', 'text' => "<strong>New post:</strong> $preview <span style='color:var(--text-muted)'>$time</span>"];
    } elseif ($row['type'] === 'comment') {
        $preview = strlen($row['content']) > 35 ? substr($row['content'], 0, 35) . '...' : $row['content'];
        $activity[] = ['type' => 'comment', 'text' => "<strong>New comment:</strong> $preview <span style='color:var(--text-muted)'>$time</span>"];
    } else {
        $activity[] = ['type' => 'like', 'text' => "<strong>Post liked</strong> <span style='color:var(--text-muted)'>$time</span>"];
    }
}
$result['recent_activity'] = $activity;

echo json_encode($result);

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j', $time);
}
