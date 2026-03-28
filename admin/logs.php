<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$config_path = __DIR__ . '/../user/config/db.php';
require_once $config_path;

$logs = $conn->query("SELECT l.*, a.username FROM moderation_logs l LEFT JOIN admin_users a ON l.admin_id = a.id ORDER BY l.created_at DESC LIMIT 100");
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as c FROM moderation_logs")->fetch_assoc()['c'],
    'today' => $conn->query("SELECT COUNT(*) as c FROM moderation_logs WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'],
    'approved' => $conn->query("SELECT COUNT(*) as c FROM moderation_logs WHERE action_type = 'approve'")->fetch_assoc()['c'],
    'rejected' => $conn->query("SELECT COUNT(*) as c FROM moderation_logs WHERE action_type = 'reject'")->fetch_assoc()['c'],
    'deleted' => $conn->query("SELECT COUNT(*) as c FROM moderation_logs WHERE action_type = 'delete'")->fetch_assoc()['c']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderation Logs - GhostThread Admin</title>
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
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .stat-box { background: #1a1a3e; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 20px; text-align: center; }
        .stat-box h3 { font-size: 28px; margin-bottom: 5px; }
        .stat-box p { color: rgba(255,255,255,0.6); font-size: 12px; }
        .stat-box.approve h3 { color: #2ed573; }
        .stat-box.reject h3 { color: #ff4757; }
        .stat-box.delete h3 { color: #ffa502; }
        .content-section { background: #1a1a3e; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 25px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-header h2 { font-size: 18px; font-weight: 600; }
        .log-table { width: 100%; border-collapse: collapse; }
        .log-table th { text-align: left; padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.5); font-size: 12px; text-transform: uppercase; }
        .log-table td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .log-table tr:hover { background: rgba(255,255,255,0.02); }
        .action-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .action-badge.approve { background: rgba(46, 213, 115, 0.2); color: #2ed573; }
        .action-badge.reject { background: rgba(255, 71, 87, 0.2); color: #ff4757; }
        .action-badge.delete { background: rgba(255, 165, 2, 0.2); color: #ffa502; }
        .type-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; background: rgba(108,99,255,0.2); color: #6c63ff; }
        .admin-name { color: #8b5cf6; font-weight: 500; }
        .empty-state { text-align: center; padding: 40px; color: rgba(255, 255, 255, 0.5); }
        .empty-state i { font-size: 50px; margin-bottom: 15px; opacity: 0.3; }
        @media (max-width: 900px) { .sidebar { width: 80px; } .sidebar-logo h1, .nav-section-title, .nav-item span { display: none; } .nav-item { justify-content: center; } .main-content { margin-left: 80px; } .log-table { display: block; overflow-x: auto; } }
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
                <a href="all_comments.php" class="nav-item"><i class="fas fa-comments"></i><span>All Comments</span></a>
            </nav>
            <?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
            <nav class="nav-section">
                <div class="nav-section-title">Settings</div>
                <a href="moderation_words.php" class="nav-item"><i class="fas fa-filter"></i><span>Moderation Words</span></a>
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users-cog"></i><span>Admin Users</span></a>
                <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
            </nav>
            <?php endif; ?>
            <nav class="nav-section">
                <div class="nav-section-title">Logs</div>
                <a href="logs.php" class="nav-item active"><i class="fas fa-history"></i><span>Moderation Logs</span></a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-history" style="color: #6c63ff;"></i> Moderation Logs</h1>
                <div class="header-info">
                    <div class="admin-badge"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="stats-row">
                <div class="stat-box">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Actions</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo $stats['today']; ?></h3>
                    <p>Today</p>
                </div>
                <div class="stat-box approve">
                    <h3><?php echo $stats['approved']; ?></h3>
                    <p>Approved</p>
                </div>
                <div class="stat-box reject">
                    <h3><?php echo $stats['rejected']; ?></h3>
                    <p>Rejected</p>
                </div>
                <div class="stat-box delete">
                    <h3><?php echo $stats['deleted']; ?></h3>
                    <p>Deleted</p>
                </div>
            </div>
            
            <div class="content-section">
                <div class="section-header"><h2>Recent Activity</h2></div>
                
                <?php if ($logs->num_rows > 0): ?>
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Type</th>
                            <th>Target ID</th>
                            <th>Admin</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                        <tr>
                            <td><span class="action-badge <?php echo $log['action_type']; ?>"><?php echo ucfirst($log['action_type']); ?></span></td>
                            <td><span class="type-badge"><?php echo ucfirst($log['target_type']); ?></span></td>
                            <td>#<?php echo $log['target_id']; ?></td>
                            <td><span class="admin-name"><?php echo htmlspecialchars($log['username']); ?></span></td>
                            <td style="color: rgba(255,255,255,0.5); font-size: 13px;"><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state"><i class="fas fa-clipboard-list"></i><p>No moderation logs yet!</p></div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
