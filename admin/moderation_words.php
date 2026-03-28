<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') { header('Location: index.php'); exit; }

$config_path = __DIR__ . '/../user/config/db.php';
require_once $config_path;

$categories = [
    'terrorist' => ['label' => 'Terrorist Content', 'icon' => 'fa-exclamation-triangle', 'color' => '#ff4757'],
    'sexual' => ['label' => 'Sexual Content', 'icon' => 'fa-ghost', 'color' => '#ff6b9d'],
    'cyberbullying' => ['label' => 'Cyberbullying', 'icon' => 'fa-bullhorn', 'color' => '#ffa502']
];

$words = [];
foreach (array_keys($categories) as $cat) {
    $words[$cat] = $conn->query("SELECT * FROM moderation_words WHERE category = '$cat' ORDER BY word ASC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderation Words - GhostThread Admin</title>
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
        .category-section { background: #1a1a3e; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 25px; margin-bottom: 25px; }
        .category-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .category-icon { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .category-header h2 { font-size: 18px; font-weight: 600; }
        .category-header span { color: rgba(255,255,255,0.5); font-size: 13px; }
        .add-word-form { display: flex; gap: 10px; margin-bottom: 20px; }
        .add-word-form input { flex: 1; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: white; font-size: 14px; }
        .add-word-form input:focus { outline: none; border-color: #6c63ff; }
        .add-word-form button { padding: 12px 24px; background: #6c63ff; border: none; border-radius: 10px; color: white; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .add-word-form button:hover { background: #5a52d5; }
        .word-list { display: flex; flex-wrap: wrap; gap: 10px; }
        .word-tag { display: flex; align-items: center; gap: 8px; padding: 8px 14px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; font-size: 13px; }
        .word-tag button { background: none; border: none; color: rgba(255,255,255,0.4); cursor: pointer; padding: 0; font-size: 14px; }
        .word-tag button:hover { color: #ff4757; }
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
                <a href="moderation_words.php" class="nav-item active"><i class="fas fa-filter"></i><span>Moderation Words</span></a>
                <a href="admin_users.php" class="nav-item"><i class="fas fa-users-cog"></i><span>Admin Users</span></a>
                <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
            </nav>
            <nav class="nav-section">
                <div class="nav-section-title">Logs</div>
                <a href="logs.php" class="nav-item"><i class="fas fa-history"></i><span>Moderation Logs</span></a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-filter" style="color: #6c63ff;"></i> Moderation Words</h1>
                <div class="header-info">
                    <div class="admin-badge"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <?php foreach ($categories as $cat_key => $cat): ?>
            <div class="category-section">
                <div class="category-header">
                    <div class="category-icon" style="background: <?php echo $cat['color']; ?>20; color: <?php echo $cat['color']; ?>;">
                        <i class="fas <?php echo $cat['icon']; ?>"></i>
                    </div>
                    <div>
                        <h2><?php echo $cat['label']; ?></h2>
                        <span><?php echo count($words[$cat_key]); ?> words</span>
                    </div>
                </div>
                
                <form class="add-word-form" onsubmit="addWord(event, '<?php echo $cat_key; ?>')">
                    <input type="text" name="word" placeholder="Add new word..." required>
                    <button type="submit"><i class="fas fa-plus"></i> Add</button>
                </form>
                
                <div class="word-list" id="words-<?php echo $cat_key; ?>">
                    <?php foreach ($words[$cat_key] as $w): ?>
                    <div class="word-tag" id="word-<?php echo $w['id']; ?>">
                        <span><?php echo htmlspecialchars($w['word']); ?></span>
                        <button onclick="deleteWord(<?php echo $w['id']; ?>)"><i class="fas fa-times"></i></button>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($words[$cat_key])): ?>
                    <span style="color: rgba(255,255,255,0.4); font-size: 13px;">No words added yet</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </main>
    </div>
    
    <div class="toast" id="toast"></div>
    
    <script>
        function showToast(m, t) { const toast = document.getElementById('toast'); toast.textContent = m; toast.className = `toast ${t}`; toast.style.display = 'block'; setTimeout(() => toast.style.display = 'none', 3000); }
        
        function addWord(e, category) {
            e.preventDefault();
            const input = e.target.querySelector('input');
            const word = input.value.trim();
            if (!word) return;
            
            fetch('api/moderation_words.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', word, category })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    showToast('Word added', 'success');
                    const container = document.getElementById(`words-${category}`);
                    const empty = container.querySelector('span');
                    if (empty) empty.remove();
                    
                    const tag = document.createElement('div');
                    tag.className = 'word-tag';
                    tag.id = `word-${d.id}`;
                    tag.innerHTML = `<span>${word}</span><button onclick="deleteWord(${d.id})"><i class="fas fa-times"></i></button>`;
                    container.appendChild(tag);
                    input.value = '';
                } else {
                    showToast(d.message, 'error');
                }
            });
        }
        
        function deleteWord(id) {
            if (!confirm('Delete this word?')) return;
            fetch('api/moderation_words.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    showToast('Word deleted', 'success');
                    const el = document.getElementById(`word-${id}`);
                    if (el) el.remove();
                }
            });
        }
    </script>
</body>
</html>
