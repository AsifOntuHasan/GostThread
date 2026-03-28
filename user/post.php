<?php
session_start();
require_once __DIR__ . "/config/db.php";

$post_id = (int)($_GET['id'] ?? 0);
$random = isset($_GET['random']);

if ($random) {
    $stmt = $conn->query("SELECT id FROM posts WHERE status = 'approved' ORDER BY RAND() LIMIT 1");
    $result = $stmt->fetch_assoc();
    if ($result) {
        $post_id = $result['id'];
    } else {
        header('Location: index.php');
        exit;
    }
}

if (!$post_id) {
    header('Location: index.php');
    exit;
}

$user_hash = md5($_SERVER['REMOTE_ADDR'] . session_id());

$stmt = $conn->prepare("
    SELECT p.*,
           CASE WHEN l.post_id IS NOT NULL THEN 1 ELSE 0 END AS liked,
           CASE WHEN b.post_id IS NOT NULL THEN 1 ELSE 0 END AS bookmarked
    FROM posts p
    LEFT JOIN likes l ON p.id = l.post_id AND l.user_hash = ?
    LEFT JOIN bookmarks b ON p.id = b.post_id AND b.user_hash = ?
    WHERE p.id = ? AND p.status = 'approved'
");
$stmt->bind_param("ssi", $user_hash, $user_hash, $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    header('Location: index.php');
    exit;
}

$post['liked'] = (bool)$post['liked'];
$post['bookmarked'] = (bool)$post['bookmarked'];

$cstmt = $conn->prepare("SELECT content, created_at FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC");
$cstmt->bind_param("i", $post_id);
$cstmt->execute();
$comments = $cstmt->get_result();

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j', $time);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post - GhostThread</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg-primary: #0a0a1a;
            --bg-secondary: #1a1a3e;
            --bg-card: rgba(255, 255, 255, 0.05);
            --accent: #6c63ff;
            --accent-secondary: #ff6b9d;
            --accent-tertiary: #c44cff;
            --text: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --text-muted: rgba(255, 255, 255, 0.4);
            --border: rgba(255, 255, 255, 0.1);
            --success: #2ed573;
            --danger: #ff4757;
        }
        body {
            background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary), #0d0d2b);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: var(--text);
        }
        #bg-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        .container {
            position: relative;
            z-index: 1;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            padding: 10px 20px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 30px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            background: rgba(108, 99, 255, 0.2);
            color: var(--text);
        }
        .post {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 25px;
        }
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .post-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .post-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .post-meta .name {
            font-weight: 600;
            font-size: 16px;
            display: block;
        }
        .post-meta .time {
            font-size: 13px;
            color: var(--text-muted);
        }
        .post-content {
            font-size: 17px;
            line-height: 1.8;
            margin-bottom: 20px;
            word-wrap: break-word;
        }
        .post-image {
            width: 100%;
            border-radius: 15px;
            margin-bottom: 20px;
            cursor: pointer;
        }
        .post-stats {
            display: flex;
            gap: 25px;
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        .post-stats span {
            cursor: pointer;
        }
        .post-stats span:hover {
            color: var(--accent);
        }
        .post-actions {
            display: flex;
            gap: 5px;
        }
        .action-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 15px;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .action-btn.liked { color: var(--danger); }
        .action-btn.bookmarked { color: #ffa502; }
        .comments-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        .comments-section h3 {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .comment {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
        }
        .comment-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-secondary), var(--accent-tertiary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .comment-body {
            flex: 1;
            background: rgba(255, 255, 255, 0.03);
            padding: 10px 15px;
            border-radius: 15px;
        }
        .comment-text {
            font-size: 14px;
            color: var(--text-secondary);
        }
        .comment-time {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 5px;
        }
        .comment-input-row {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .comment-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 25px;
            padding: 12px 18px;
            color: var(--text);
            font-size: 14px;
            outline: none;
        }
        .comment-input:focus {
            border-color: var(--accent);
        }
        .comment-input::placeholder {
            color: var(--text-muted);
        }
        .send-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-tertiary));
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .image-modal.active { display: flex; }
        .image-modal img { max-width: 90%; max-height: 90%; border-radius: 10px; }
        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            z-index: 2000;
            opacity: 0;
            transition: all 0.4s ease;
        }
        .toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        .toast.success { background: linear-gradient(135deg, var(--success), #7bed9f); }
        .toast.error { background: linear-gradient(135deg, var(--danger), var(--accent-secondary)); }
    </style>
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    
    <div class="image-modal" id="imageModal" onclick="closeImageModal()">
        <img id="modalImage" src="" alt="">
    </div>
    
    <div class="container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Feed
        </a>
        
        <div class="post" id="post-<?php echo $post['id']; ?>">
            <div class="post-header">
                <div class="post-author">
                    <div class="post-avatar">👻</div>
                    <div class="post-meta">
                        <span class="name">Anonymous Ghost</span>
                        <span class="time"><?php echo timeAgo($post['created_at']); ?></span>
                    </div>
                </div>
            </div>
            
            <p class="post-content"><?php echo htmlspecialchars($post['content']); ?></p>
            
            <?php if ($post['media_path']): ?>
            <img src="<?php echo htmlspecialchars($post['media_path']); ?>" alt="" class="post-image" onclick="openImageModal(this.src)">
            <?php endif; ?>
            
            <div class="post-stats">
                <span><strong><?php echo $post['like_count']; ?></strong> likes</span>
                <span><strong><?php echo $post['comment_count']; ?></strong> comments</span>
                <span><strong><?php echo $post['share_count']; ?></strong> shares</span>
            </div>
            
            <div class="post-actions">
                <button class="action-btn <?php echo $post['liked'] ? 'liked' : ''; ?>" onclick="toggleLike(<?php echo $post['id']; ?>, this)">
                    <i class="<?php echo $post['liked'] ? 'fas' : 'far'; ?> fa-heart"></i>
                    <span>Like</span>
                </button>
                <button class="action-btn <?php echo $post['bookmarked'] ? 'bookmarked' : ''; ?>" onclick="toggleBookmark(<?php echo $post['id']; ?>, this)">
                    <i class="<?php echo $post['bookmarked'] ? 'fas' : 'far'; ?> fa-bookmark"></i>
                    <span>Save</span>
                </button>
                <button class="action-btn" onclick="sharePost(<?php echo $post['id']; ?>)">
                    <i class="fas fa-share"></i>
                    <span>Share</span>
                </button>
            </div>
            
            <div class="comments-section">
                <h3>Comments (<?php echo $comments->num_rows; ?>)</h3>
                
                <?php while ($comment = $comments->fetch_assoc()): ?>
                <div class="comment">
                    <div class="comment-avatar">👻</div>
                    <div class="comment-body">
                        <div class="comment-text"><?php echo htmlspecialchars($comment['content']); ?></div>
                        <div class="comment-time"><?php echo timeAgo($comment['created_at']); ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <div class="comment-input-row">
                    <input type="text" class="comment-input" id="commentInput" placeholder="Write a comment..." onkeypress="if(event.key==='Enter') addComment(<?php echo $post['id']; ?>)">
                    <button class="send-btn" onclick="addComment(<?php echo $post['id']; ?>)">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="toast" id="toast"></div>
    
    <script>
        const canvas = document.getElementById('bg-canvas');
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        camera.position.z = 30;
        
        const geometry = new THREE.BufferGeometry();
        const count = 1000;
        const positions = new Float32Array(count * 3);
        for (let i = 0; i < count * 3; i++) positions[i] = (Math.random() - 0.5) * 80;
        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        
        const material = new THREE.PointsMaterial({ size: 0.1, color: 0x6c63ff, transparent: true, opacity: 0.6 });
        const points = new THREE.Points(geometry, material);
        scene.add(points);
        
        function animate() {
            requestAnimationFrame(animate);
            points.rotation.y += 0.0003;
            renderer.render(scene, camera);
        }
        animate();
        
        function toggleLike(postId, btn) {
            fetch('api/toggle_like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const icon = btn.querySelector('i');
                    if (data.action === 'liked') {
                        btn.classList.add('liked');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        btn.classList.remove('liked');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                }
            });
        }
        
        function toggleBookmark(postId, btn) {
            fetch('api/bookmark.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const icon = btn.querySelector('i');
                    if (data.action === 'added') {
                        btn.classList.add('bookmarked');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        showToast('Post saved!', 'success');
                    } else {
                        btn.classList.remove('bookmarked');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                }
            });
        }
        
        function sharePost(postId) {
            fetch('api/share.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&platform=ghostthread`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Link copied!', 'success');
                    navigator.clipboard.writeText(window.location.href);
                }
            });
        }
        
        function addComment(postId) {
            const input = document.getElementById('commentInput');
            const content = input.value.trim();
            
            if (!content) return;
            
            fetch('api/add_comment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&content=${encodeURIComponent(content)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    input.value = '';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message, 'error');
                }
            });
        }
        
        function openImageModal(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModal').classList.add('active');
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').classList.remove('active');
        }
        
        function showToast(message, type = '') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type} show`;
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
    </script>
</body>
</html>
