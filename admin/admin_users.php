<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') { header('Location: index.php'); exit; }

$config_path = __DIR__ . '/../user/config/db.php';
require_once $config_path;

$admins = $conn->query("SELECT id, username, role, is_active, created_at, last_login FROM admin_users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users - GhostThread Admin</title>
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
        .add-admin-btn { background: #6c63ff; border: none; color: white; padding: 12px 24px; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .add-admin-btn:hover { background: #5a52d5; }
        .content-section { background: #1a1a3e; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 25px; margin-bottom: 25px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-header h2 { font-size: 18px; font-weight: 600; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { text-align: left; padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.5); font-size: 12px; text-transform: uppercase; }
        .admin-table td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .admin-table tr:hover { background: rgba(255,255,255,0.02); }
        .role-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .role-badge.super_admin { background: rgba(108, 99, 255, 0.2); color: #6c63ff; }
        .role-badge.moderator { background: rgba(46, 213, 115, 0.2); color: #2ed573; }
        .status-badge { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .status-badge.active { background: #2ed573; }
        .status-badge.inactive { background: #ff4757; }
        .action-btns { display: flex; gap: 8px; }
        .action-btn { padding: 8px 14px; border: none; border-radius: 8px; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 5px; }
        .action-btn.edit { background: rgba(108, 99, 255, 0.2); color: #6c63ff; }
        .action-btn.toggle { background: rgba(255, 165, 2, 0.2); color: #ffa502; }
        .action-btn.delete { background: rgba(255, 71, 87, 0.2); color: #ff4757; }
        .action-btn:hover { opacity: 0.8; }
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: none; align-items: center; justify-content: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-content { background: #1a1a3e; border-radius: 16px; padding: 30px; width: 400px; max-width: 90%; }
        .modal-content h2 { margin-bottom: 20px; font-size: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; color: rgba(255,255,255,0.7); }
        .form-group input, .form-group select { width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: white; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #6c63ff; }
        .modal-btns { display: flex; gap: 10px; margin-top: 20px; }
        .modal-btns button { flex: 1; padding: 12px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .modal-btns .cancel { background: rgba(255,255,255,0.1); color: white; }
        .modal-btns .submit { background: #6c63ff; color: white; }
        .toast { position: fixed; bottom: 30px; right: 30px; padding: 15px 25px; border-radius: 12px; color: white; font-weight: 500; display: none; z-index: 1001; }
        .toast.success { background: linear-gradient(135deg, #2ed573, #7bed9f); }
        .toast.error { background: linear-gradient(135deg, #ff4757, #ff6b9d); }
        @media (max-width: 900px) { .sidebar { width: 80px; } .sidebar-logo h1, .nav-section-title, .nav-item span { display: none; } .nav-item { justify-content: center; } .main-content { margin-left: 80px; } .admin-table { display: block; overflow-x: auto; } }
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
            <nav class="nav-section">
                <div class="nav-section-title">Settings</div>
                <a href="moderation_words.php" class="nav-item"><i class="fas fa-filter"></i><span>Moderation Words</span></a>
                <a href="admin_users.php" class="nav-item active"><i class="fas fa-users-cog"></i><span>Admin Users</span></a>
                <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
            </nav>
            <nav class="nav-section">
                <div class="nav-section-title">Logs</div>
                <a href="logs.php" class="nav-item"><i class="fas fa-history"></i><span>Moderation Logs</span></a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-users-cog" style="color: #6c63ff;"></i> Admin Users</h1>
                <div class="header-info">
                    <div class="admin-badge"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="content-section">
                <div class="section-header">
                    <h2>All Admins</h2>
                    <button class="add-admin-btn" onclick="openModal()"><i class="fas fa-plus"></i> Add Admin</button>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($admin = $admins->fetch_assoc()): ?>
                        <tr id="admin-<?php echo $admin['id']; ?>">
                            <td><span class="status-badge <?php echo $admin['is_active'] ? 'active' : 'inactive'; ?>"></span></td>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($admin['username']); ?></td>
                            <td><span class="role-badge <?php echo $admin['role']; ?>"><?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?></span></td>
                            <td style="color: rgba(255,255,255,0.6); font-size: 13px;"><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                            <td style="color: rgba(255,255,255,0.6); font-size: 13px;"><?php echo $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never'; ?></td>
                            <td>
                                <div class="action-btns">
                                    <button class="action-btn edit" onclick="editAdmin(<?php echo htmlspecialchars(json_encode($admin)); ?>)"><i class="fas fa-edit"></i></button>
                                    <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                    <button class="action-btn toggle" onclick="toggleAdmin(<?php echo $admin['id']; ?>, <?php echo $admin['is_active'] ? 0 : 1; ?>)">
                                        <i class="fas fa-<?php echo $admin['is_active'] ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="deleteAdmin(<?php echo $admin['id']; ?>)"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <div class="modal" id="adminModal">
        <div class="modal-content">
            <h2 id="modalTitle">Add Admin</h2>
            <form id="adminForm" onsubmit="saveAdmin(event)">
                <input type="hidden" id="adminId" value="">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="username" required>
                </div>
                <div class="form-group">
                    <label>Password <span id="passwordHint" style="color:rgba(255,255,255,0.4);">(leave empty to keep current)</span></label>
                    <input type="password" id="password">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="role">
                        <option value="moderator">Moderator</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div class="modal-btns">
                    <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="toast" id="toast"></div>
    
    <script>
        function showToast(m, t) { const toast = document.getElementById('toast'); toast.textContent = m; toast.className = `toast ${t}`; toast.style.display = 'block'; setTimeout(() => toast.style.display = 'none', 3000); }
        
        function openModal(admin = null) {
            document.getElementById('adminModal').classList.add('active');
            document.getElementById('adminId').value = admin ? admin.id : '';
            document.getElementById('username').value = admin ? admin.username : '';
            document.getElementById('password').value = '';
            document.getElementById('password').required = !admin;
            document.getElementById('role').value = admin ? admin.role : 'moderator';
            document.getElementById('modalTitle').textContent = admin ? 'Edit Admin' : 'Add Admin';
            document.getElementById('passwordHint').style.display = admin ? 'inline' : 'none';
        }
        
        function closeModal() { document.getElementById('adminModal').classList.remove('active'); }
        function editAdmin(admin) { openModal(admin); }
        
        function saveAdmin(e) {
            e.preventDefault();
            const data = {
                action: document.getElementById('adminId').value ? 'update' : 'create',
                id: document.getElementById('adminId').value,
                username: document.getElementById('username').value,
                password: document.getElementById('password').value,
                role: document.getElementById('role').value
            };
            
            fetch('api/admin_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    showToast(d.message, 'success');
                    closeModal();
                    location.reload();
                } else {
                    showToast(d.message, 'error');
                }
            });
        }
        
        function toggleAdmin(id, active) {
            fetch('api/admin_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle', id, is_active: active })
            })
            .then(r => r.json())
            .then(d => { if (d.success) location.reload(); });
        }
        
        function deleteAdmin(id) {
            if (!confirm('Delete this admin?')) return;
            fetch('api/admin_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id })
            })
            .then(r => r.json())
            .then(d => { if (d.success) location.reload(); });
        }
    </script>
</body>
</html>
