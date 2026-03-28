<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$config_path = __DIR__ . '/../user/config/db.php';
require_once $config_path;
$admin_role = $_SESSION['admin_role'];

$posts = $conn->query("SELECT * FROM posts WHERE status = 'pending' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Posts - GhostThread Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0a0a1a; color: #ffffff; min-height: 100vh; }
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #12122a; border-right: 1px solid rgba(255, 255, 255, 0.1); padding: 20px; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-logo { text-align: center; padding: 20px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 20px; }
        .sidebar-logo .logo { font-size: 40px; filter: drop-shadow(0 0 15px rgba(108, 99, 255, 0.5)); }
        .sidebar-logo h1 { font-size: 18px; background: linear-gradient(135deg, #6c63ff, #ff6b9d); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-top: 10px; }
        .nav-section { margin-bottom: 20px; }
        .nav-section-title { font-size: 11px; text-transform: uppercase; color: rgba(255, 255, 255, 0.4); padding: 10px 15px; letter-spacing: 1px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: rgba(255, 255, 255, 0.7); text-decoration: none; border-radius: 10px; margin-bottom: 4px; transition: all 0.3s ease; cursor: pointer; }
        .nav-item:hover { background: rgba(108, 99, 255, 0.15); color: white; }
        .nav-item.active { background: rgba(108, 99, 255, 0.2); color: white; }
        .nav-item i { width: 20px; text-align: center; }
        .nav-item .badge { margin-left: auto; background: #ff4757; color: white; font-size: 11px; padding: 3px 8px; border-radius: 20px; font-weight: 600; }
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 28px; font-weight: 700; }
        .header-info { display: flex; align-items: center; gap: 15px; }
        .admin-badge { background: rgba(108, 99, 255, 0.2); padding: 8px 15px; border-radius: 20px; font-size: 13px; color: #8b5cf6; }
        .logout-btn { background: rgba(255, 71, 87, 0.2); border: none; color: #ff4757; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .logout-btn:hover { background: rgba(255, 71, 87, 0.3); }
        .content-section { background: #1a1a3e; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 25px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-header h2 { font-size: 18px; font-weight: 600; }
        .review-list { display: flex; flex-direction: column; gap: 15px; }
        .review-item { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px; transition: all 0.3s ease; }
        .review-item:hover { border-color: rgba(108, 99, 255, 0.3); }
        .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .review-type { background: rgba(255, 165, 2, 0.2); color: #ffa502; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .review-time { color: rgba(255, 255, 255, 0.5); font-size: 12px; }
        .review-content { color: rgba(255, 255, 255, 0.9); font-size: 15px; line-height: 1.6; margin-bottom: 15px; word-wrap: break-word; }
        .flagged-words { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
        .flagged-word { background: rgba(255, 71, 87, 0.2); color: #ff6b9d; padding: 4px 12px; border-radius: 15px; font-size: 12px; }
        .review-actions { display: flex; gap: 10px; }
        .action-btn { padding: 10px 20px; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.3s ease; }
        .action-btn.approve { background: rgba(46, 213, 115, 0.2); color: #2ed573; }
        .action-btn.approve:hover { background: rgba(46, 213, 115, 0.3); }
        .action-btn.reject { background: rgba(255, 71, 87, 0.2); color: #ff4757; }
        .action-btn.reject:hover { background: rgba(255, 71, 87, 0.3); }
        .action-btn.delete { background: rgba(255, 71, 87, 0.3); color: white; }
        .empty-state { text-align: center; padding: 40px; color: rgba(255, 255, 255, 0.5); }
        .empty-state i { font-size: 50px; margin-bottom: 15px; opacity: 0.3; }
        .toast { position: fixed; bottom: 30px; right: 30px; padding: 15px 25px; border-radius: 12px; color: white; font-weight: 500; z-index: 1000; animation: slideIn 0.3s ease; display: none; }
        .toast.success { background: linear-gradient(135deg, #2ed573, #7bed9f); }
        .toast.error { background: linear-gradient(135deg, #ff4757, #ff6b9d); }
        @keyframes slideIn { from { opacity: 0; transform: translateX(50px); } to { opacity: 1; transform: translateX(0); } }
        @media (max-width: 900px) { .sidebar { width: 80px; } .sidebar-logo h1, .nav-section-title, .nav-item span, .nav-item .badge { display: none; } .nav-item { justify-content: center; } .main-content { margin-left: 80px; } }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="logo">👻</div>
                <h1>GhostThread</h1>
            </div>
            <nav class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="dashboard.php" class="nav-item"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
            </nav>
            <nav class="nav-section">
                <div class="nav-section-title">Moderation</div>
                <a href="pending_posts.php" class="nav-item active"><i class="fas fa-clock"></i><span>Pending Posts</span></a>
                <a href="pending_comments.php" class="nav-item"><i class="fas fa-comment-alt"></i><span>Pending Comments</span></a>
                <a href="all_posts.php" class="nav-item"><i class="fas fa-file-alt"></i><span>All Posts</span></a>
                <a href="all_comments.php" class="nav-item"><i class="fas fa-comments"></i><span>All Comments</span></a>
            </nav>
            <?php if ($admin_role === 'super_admin'): ?>
            <nav class="nav-section">
                <div class="nav-section-title">Settings</div>
                <a href="moderation_words.php" class="nav-item"><i class="fas fa-filter"></i><span>Moderation Words</span></a>
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users-cog"></i><span>Admin Users</span></a>
                <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
            </nav>
            <?php endif; ?>
            <nav class="nav-section">
                <div class="nav-section-title">Logs</div>
                <a href="logs.php" class="nav-item"><i class="fas fa-history"></i><span>Moderation Logs</span></a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-clock" style="color: #ffa502;"></i> Pending Posts</h1>
                <div class="header-info">
                    <div class="admin-badge"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="content-section">
                <div class="section-header">
                    <h2>Awaiting Review (<?php echo $posts->num_rows; ?>)</h2>
                </div>
                
                <?php if ($posts->num_rows > 0): ?>
                <div class="review-list">
                    <?php while ($post = $posts->fetch_assoc()): ?>
                        <?php $flagged = $post['flagged_words'] ? json_decode($post['flagged_words']) : []; ?>
                        <div class="review-item" id="post-<?php echo $post['id']; ?>">
                            <div class="review-header">
                                <div class="review-meta">
                                    <span class="review-type">Post #<?php echo $post['id']; ?></span>
                                    <span class="review-time"><?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></span>
                                </div>
                            </div>
                            <p class="review-content"><?php echo htmlspecialchars($post['content']); ?></p>
                            <?php if (!empty($flagged)): ?>
                            <div class="flagged-words">
                                <?php foreach ($flagged as $word): ?>
                                <span class="flagged-word"><?php echo htmlspecialchars($word); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <div class="review-actions">
                                <button class="action-btn approve" onclick="moderatePost(<?php echo $post['id']; ?>, 'approve')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="action-btn reject" onclick="moderatePost(<?php echo $post['id']; ?>, 'reject')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                                <button class="action-btn delete" onclick="deletePost(<?php echo $post['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>No pending posts to review!</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <div class="toast" id="toast"></div>
    
    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type}`;
            toast.style.display = 'block';
            setTimeout(() => toast.style.display = 'none', 3000);
        }
        
        function moderatePost(id, action) {
            fetch('api/moderate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, type: 'post', action })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    const el = document.getElementById(`post-${id}`);
                    if (el) { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }
                } else { showToast(data.message, 'error'); }
            });
        }
        
        function deletePost(id) {
            if (!confirm('Delete this post permanently?')) return;
            fetch('api/moderate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, type: 'post', action: 'delete' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Post deleted', 'success');
                    const el = document.getElementById(`post-${id}`);
                    if (el) { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }
                }
            });
        }
    </script>
</body>
</html>
