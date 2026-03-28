<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') { header('Location: index.php'); exit; }

$config_path = __DIR__ . '/../user/config/db.php';
require_once $config_path;

$settings = $conn->query("SELECT * FROM system_settings");
$settings_arr = [];
while ($s = $settings->fetch_assoc()) { $settings_arr[$s['setting_key']] = $s['setting_value']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - GhostThread Admin</title>
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
        .settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }
        .setting-card { background: #1a1a3e; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 25px; }
        .setting-card h3 { font-size: 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .setting-card h3 i { color: #6c63ff; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; color: rgba(255,255,255,0.7); }
        .form-group input, .form-group select { width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: white; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #6c63ff; }
        .form-group .hint { font-size: 11px; color: rgba(255,255,255,0.4); margin-top: 5px; }
        .toggle-group { display: flex; align-items: center; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .toggle-group:last-child { border-bottom: none; }
        .toggle-group label { margin-bottom: 0; }
        .toggle { width: 50px; height: 26px; background: rgba(255,255,255,0.1); border-radius: 13px; position: relative; cursor: pointer; transition: all 0.3s; }
        .toggle.active { background: #6c63ff; }
        .toggle::after { content: ''; width: 20px; height: 20px; background: white; border-radius: 50%; position: absolute; top: 3px; left: 3px; transition: all 0.3s; }
        .toggle.active::after { left: 27px; }
        .save-btn { background: #6c63ff; border: none; color: white; padding: 14px 30px; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 600; margin-top: 20px; }
        .save-btn:hover { background: #5a52d5; }
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
                <a href="all_comments.php" class="nav-item"><i class="fas fa-comments"></i><span>All Comments</span></a>
            </nav>
            <nav class="nav-section">
                <div class="nav-section-title">Settings</div>
                <a href="moderation_words.php" class="nav-item"><i class="fas fa-filter"></i><span>Moderation Words</span></a>
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users-cog"></i><span>Admin Users</span></a>
                <a href="settings.php" class="nav-item active"><i class="fas fa-cog"></i><span>Settings</span></a>
            </nav>
            <nav class="nav-section">
                <div class="nav-section-title">Logs</div>
                <a href="logs.php" class="nav-item"><i class="fas fa-history"></i><span>Moderation Logs</span></a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-cog" style="color: #6c63ff;"></i> Settings</h1>
                <div class="header-info">
                    <div class="admin-badge"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="settings-grid">
                <div class="setting-card">
                    <h3><i class="fas fa-globe"></i> Site Information</h3>
                    <div class="form-group">
                        <label>Site Name</label>
                        <input type="text" id="site_name" value="<?php echo htmlspecialchars($settings_arr['site_name'] ?? 'GhostThread'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Tagline</label>
                        <input type="text" id="site_tagline" value="<?php echo htmlspecialchars($settings_arr['site_tagline'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="setting-card">
                    <h3><i class="fas fa-shield-alt"></i> Moderation</h3>
                    <div class="toggle-group">
                        <label>Auto Moderation</label>
                        <div class="toggle <?php echo ($settings_arr['auto_moderation'] ?? 'true') === 'true' ? 'active' : ''; ?>" id="auto_moderation" onclick="toggleSetting(this)"></div>
                    </div>
                    <div class="form-group">
                        <label>Max Posts Per Hour</label>
                        <input type="number" id="max_posts_per_hour" value="<?php echo $settings_arr['max_posts_per_hour'] ?? 10; ?>" min="1">
                        <p class="hint">Limit posts per IP per hour</p>
                    </div>
                    <div class="form-group">
                        <label>Max Image Size (MB)</label>
                        <input type="number" id="max_image_size" value="<?php echo $settings_arr['max_image_size'] ?? 5; ?>" min="1" max="20">
                    </div>
                </div>
                
                <div class="setting-card">
                    <h3><i class="fas fa-palette"></i> Appearance</h3>
                    <div class="form-group">
                        <label>Primary Color</label>
                        <input type="color" id="primary_color" value="#6c63ff" style="height: 45px; padding: 5px;">
                    </div>
                    <div class="toggle-group">
                        <label>Particle Animation</label>
                        <div class="toggle active" id="particle_animation"></div>
                    </div>
                </div>
            </div>
            
            <button class="save-btn" onclick="saveSettings()"><i class="fas fa-save"></i> Save Settings</button>
        </main>
    </div>
    
    <div class="toast" id="toast"></div>
    
    <script>
        function showToast(m, t) { const toast = document.getElementById('toast'); toast.textContent = m; toast.className = `toast ${t}`; toast.style.display = 'block'; setTimeout(() => toast.style.display = 'none', 3000); }
        
        function toggleSetting(el) { el.classList.toggle('active'); }
        
        function saveSettings() {
            const data = {
                action: 'save',
                settings: {
                    site_name: document.getElementById('site_name').value,
                    site_tagline: document.getElementById('site_tagline').value,
                    auto_moderation: document.getElementById('auto_moderation').classList.contains('active') ? 'true' : 'false',
                    max_posts_per_hour: document.getElementById('max_posts_per_hour').value,
                    max_image_size: document.getElementById('max_image_size').value
                }
            };
            
            fetch('api/settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(d => { showToast(d.success ? 'Settings saved!' : d.message, d.success ? 'success' : 'error'); });
        }
    </script>
</body>
</html>
