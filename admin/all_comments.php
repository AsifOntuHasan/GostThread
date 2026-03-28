<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$config_path = __DIR__ . '/../user/config/db.php';
require_once $config_path;
$admin_role = $_SESSION['admin_role'];

$comments = $conn->query("SELECT c.*, p.content as post_content FROM comments c LEFT JOIN posts p ON c.post_id = p.id ORDER BY c.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Comments - GhostThread Admin</title>
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
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 28px; font-weight: 700; }
        .header-info { display: flex; align-items: center; gap: 15px; }
        .admin-badge { background: rgba(108, 99, 255, 0.2); padding: 8px 15px; border-radius: 20px; font-size: 13px; color: #8b5cf6; }
        .logout-btn { background: rgba(255, 71, 87, 0.2); border: none; color: #ff4757; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .content-section { background: #1a1a3e; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 25px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-header h2 { font-size: 18px; font-weight: 600; }
        .filters { display: flex; gap: 10px; margin-bottom: 20px; }
        .filter-btn { padding: 8px 16px; border: 1px solid rgba(255,255,255,0.1); background: transparent; color: rgba(255,255,255,0.6); border-radius: 8px; cursor: pointer; font-size: 13px; }
        .filter-btn:hover, .filter-btn.active { background: rgba(108,99,255,0.2); border-color: #6c63ff; color: white; }
        .comment-list { display: flex; flex-direction: column; gap: 15px; }
        .comment-item { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px; }
        .comment-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .status-badge.approved { background: rgba(46, 213, 115, 0.2); color: #2ed573; }
        .status-badge.pending { background: rgba(255, 165, 2, 0.2); color: #ffa502; }
        .status-badge.rejected { background: rgba(255, 71, 87, 0.2); color: #ff4757; }
        .comment-time { color: rgba(255, 255, 255, 0.5); font-size: 12px; }
        .post-preview { background: rgba(255, 255, 255, 0.05); border-left: 3px solid #6c63ff; padding: 10px 15px; margin-bottom: 12px; border-radius: 0 10px 10px 0; font-size: 13px; color: rgba(255, 255, 255, 0.7); }
        .comment-content { color: rgba(255, 255, 255, 0.9); font-size: 15px; line-height: 1.6; margin-bottom: 15px; }
        .flagged-words { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
        .flagged-word { background: rgba(255, 71, 87, 0.2); color: #ff6b9d; padding: 4px 12px; border-radius: 15px; font-size: 12px; }
        .comment-actions { display: flex; gap: 10px; }
        .action-btn { padding: 10px 20px; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.3s; }
        .action-btn.approve { background: rgba(46, 213, 115, 0.2); color: #2ed573; }
        .action-btn.reject { background: rgba(255, 71, 87, 0.2); color: #ff4757; }
        .action-btn.delete { background: rgba(255, 71, 87, 0.3); color: white; }
        .empty-state { text-align: center; padding: 40px; color: rgba(255, 255, 255, 0.5); }
        .empty-state i { font-size: 50px; margin-bottom: 15px; opacity: 0.3; }
        .toast { position: fixed; bottom: 30px; right: 30px; padding: 15px 25px; border-radius: 12px; color: white; font-weight: 500; display: none; z-index: 1000; }
        .toast.success { background: linear-gradient(135deg, #2ed573, #7bed9f); }
        .toast.error { background: linear-gradient(135deg, #ff4757, #ff6b9d); }
        @media (max-width: 900px) { .sidebar { width: 80px; } .sidebar-logo h1, .nav-section-title, .nav-item span { display: none; } .nav-item { justify-content: center; } .main-content { margin-left: 80px; } }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-logo"><div class="logo">👻</div><h1>GhostThread</h1></div>
            <nav class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="dashboard.php" class="nav-item"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
            </nav>
            <nav class="nav-section">
                <div class="nav-section-title">Moderation</div>
                <a href="pending_posts.php" class="nav-item"><i class="fas fa-clock"></i><span>Pending Posts</span></a>
                <a href="pending_comments.php" class="nav-item"><i class="fas fa-comment-alt"></i><span>Pending Comments</span></a>
                <a href="all_posts.php" class="nav-item"><i class="fas fa-file-alt"></i><span>All Posts</span></a>
                <a href="all_comments.php" class="nav-item active"><i class="fas fa-comments"></i><span>All Comments</span></a>
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
                <h1><i class="fas fa-comments" style="color: #6c63ff;"></i> All Comments</h1>
                <div class="header-info">
                    <div class="admin-badge"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="content-section">
                <div class="section-header"><h2>All Comments (<?php echo $comments->num_rows; ?>)</h2></div>
                
                <?php if ($comments->num_rows > 0): ?>
                <div class="filters">
                    <button class="filter-btn active" onclick="filterComments('all')">All</button>
                    <button class="filter-btn" onclick="filterComments('approved')">Approved</button>
                    <button class="filter-btn" onclick="filterComments('pending')">Pending</button>
                    <button class="filter-btn" onclick="filterComments('rejected')">Rejected</button>
                </div>
                
                <div class="comment-list" id="commentList">
                    <?php while ($comment = $comments->fetch_assoc()): ?>
                        <?php $flagged = $comment['flagged_words'] ? json_decode($comment['flagged_words']) : []; ?>
                        <div class="comment-item" data-status="<?php echo $comment['status']; ?>" id="comment-<?php echo $comment['id']; ?>">
                            <div class="comment-header">
                                <div>
                                    <span class="status-badge <?php echo $comment['status']; ?>"><?php echo ucfirst($comment['status']); ?></span>
                                    <span style="color: rgba(255,255,255,0.5); font-size:12px; margin-left:10px;">#<?php echo $comment['id']; ?></span>
                                </div>
                                <span class="comment-time"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                            </div>
                            <div class="post-preview"><strong>On Post:</strong> <?php echo htmlspecialchars(substr($comment['post_content'] ?? 'N/A', 0, 100)); ?>...</div>
                            <p class="comment-content"><?php echo htmlspecialchars($comment['content']); ?></p>
                            <?php if (!empty($flagged)): ?>
                            <div class="flagged-words"><?php foreach ($flagged as $word): ?><span class="flagged-word"><?php echo htmlspecialchars($word); ?></span><?php endforeach; ?></div>
                            <?php endif; ?>
                            <div class="comment-actions">
                                <?php if ($comment['status'] !== 'approved'): ?>
                                <button class="action-btn approve" onclick="moderateComment(<?php echo $comment['id']; ?>, 'approve')"><i class="fas fa-check"></i> Approve</button>
                                <?php endif; ?>
                                <?php if ($comment['status'] !== 'rejected'): ?>
                                <button class="action-btn reject" onclick="moderateComment(<?php echo $comment['id']; ?>, 'reject')"><i class="fas fa-times"></i> Reject</button>
                                <?php endif; ?>
                                <button class="action-btn delete" onclick="deleteComment(<?php echo $comment['id']; ?>)"><i class="fas fa-trash"></i> Delete</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="empty-state"><i class="fas fa-comments"></i><p>No comments yet!</p></div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <div class="toast" id="toast"></div>
    
    <script>
        function showToast(m, t) { const toast = document.getElementById('toast'); toast.textContent = m; toast.className = `toast ${t}`; toast.style.display = 'block'; setTimeout(() => toast.style.display = 'none', 3000); }
        
        function filterComments(status) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');
            document.querySelectorAll('.comment-item').forEach(item => {
                item.style.display = status === 'all' || item.dataset.status === status ? 'flex' : 'none';
            });
        }
        
        function moderateComment(id, action) { fetch('api/moderate.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id, type: 'comment', action}) }).then(r=>r.json()).then(d=>{ if(d.success){ showToast(d.message,'success'); location.reload(); } else showToast(d.message,'error'); }); }
        function deleteComment(id) { if(!confirm('Delete permanently?'))return; fetch('api/moderate.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,type:'comment',action:'delete'})}).then(r=>r.json()).then(d=>{ if(d.success){showToast('Deleted','success');const e=document.getElementById(`comment-${id}`);if(e){e.style.opacity='0';setTimeout(()=>e.remove(),300);}} }); }
    </script>
</body>
</html>
