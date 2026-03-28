<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$config_path = __DIR__ . '/../user/config/db.php';
require_once $config_path;
$admin_role = $_SESSION['admin_role'];
$posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Posts - GhostThread Admin</title>
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
        .search-box { position: relative; margin-bottom: 20px; }
        .search-box input { width: 100%; padding: 12px 15px 12px 45px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; color: white; font-size: 14px; outline: none; }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.4); }
        .posts-table { width: 100%; border-collapse: collapse; }
        .posts-table th { text-align: left; padding: 15px; color: rgba(255, 255, 255, 0.6); font-size: 12px; text-transform: uppercase; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .posts-table td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-size: 14px; }
        .posts-table tr:hover { background: rgba(255, 255, 255, 0.03); }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .status-approved { background: rgba(46, 213, 115, 0.2); color: #2ed573; }
        .status-pending { background: rgba(255, 165, 2, 0.2); color: #ffa502; }
        .status-rejected { background: rgba(255, 71, 87, 0.2); color: #ff4757; }
        .action-btn { padding: 6px 12px; border: none; border-radius: 6px; font-size: 12px; cursor: pointer; margin-right: 5px; transition: all 0.3s ease; }
        .action-btn.approve { background: rgba(46, 213, 115, 0.2); color: #2ed573; }
        .action-btn.delete { background: rgba(255, 71, 87, 0.2); color: #ff4757; }
        .action-btn:hover { transform: translateY(-1px); }
        .toast { position: fixed; bottom: 30px; right: 30px; padding: 15px 25px; border-radius: 12px; color: white; display: none; }
        .toast.success { background: linear-gradient(135deg, #2ed573, #7bed9f); }
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
                <a href="all_posts.php" class="nav-item active"><i class="fas fa-file-alt"></i><span>All Posts</span></a>
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
                <h1><i class="fas fa-file-alt" style="color: #6c63ff;"></i> All Posts</h1>
                <div class="header-info">
                    <div class="admin-badge"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="content-section">
                <div class="section-header"><h2>All Posts (<?php echo $posts->num_rows; ?>)</h2></div>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search posts..." onkeyup="filterTable()">
                </div>
                
                <?php if ($posts->num_rows > 0): ?>
                <table class="posts-table" id="postsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Content</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($post = $posts->fetch_assoc()): ?>
                        <tr id="post-<?php echo $post['id']; ?>">
                            <td>#<?php echo $post['id']; ?></td>
                            <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($post['content']); ?></td>
                            <td><span class="status-badge status-<?php echo $post['status']; ?>"><?php echo $post['status']; ?></span></td>
                            <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                            <td>
                                <?php if ($post['status'] === 'pending'): ?>
                                <button class="action-btn approve" onclick="moderatePost(<?php echo $post['id']; ?>, 'approve')"><i class="fas fa-check"></i></button>
                                <?php endif; ?>
                                <button class="action-btn delete" onclick="deletePost(<?php echo $post['id']; ?>)"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align: center; color: rgba(255,255,255,0.5); padding: 40px;">No posts yet</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <div class="toast" id="toast"></div>
    
    <script>
        function showToast(m) { const t = document.getElementById('toast'); t.textContent = m; t.className = 'toast success'; t.style.display = 'block'; setTimeout(() => t.style.display = 'none', 3000); }
        function filterTable() { var i = document.getElementById('searchInput').value.toLowerCase(); var tr = document.getElementsByTagName('tr'); for (var j = 1; j < tr.length; j++) { var td = tr[j].getElementsByTagName('td')[1]; if (td) { var text = td.textContent || td.innerText; tr[j].style.display = text.toLowerCase().indexOf(i) > -1 ? '' : 'none'; } } }
        function moderatePost(id, action) { fetch('api/moderate.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id, type: 'post', action}) }).then(r=>r.json()).then(d=>{ if(d.success){ showToast(d.message); location.reload(); } }); }
        function deletePost(id) { if(!confirm('Delete permanently?'))return; fetch('api/moderate.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,type:'post',action:'delete'})}).then(r=>r.json()).then(d=>{ if(d.success){ const e=document.getElementById(`post-${id}`); if(e){e.style.opacity='0';setTimeout(()=>e.remove(),300);} } }); }
    </script>
</body>
</html>
