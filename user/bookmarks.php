<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookmarks - GhostThread</title>
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
            padding-bottom: 100px;
        }
        header {
            text-align: center;
            padding: 20px 0;
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
        .page-title {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #ffa502, var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .posts-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 30px;
        }
        .post {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .post:hover {
            border-color: rgba(108, 99, 255, 0.3);
        }
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .post-author {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .post-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .post-meta .name {
            font-weight: 600;
            font-size: 14px;
        }
        .post-meta .time {
            font-size: 12px;
            color: var(--text-muted);
        }
        .bookmark-badge {
            background: rgba(255, 165, 2, 0.2);
            color: #ffa502;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
        }
        .post-content {
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 15px;
            word-wrap: break-word;
        }
        .post-image {
            width: 100%;
            border-radius: 15px;
            margin-bottom: 15px;
            cursor: pointer;
        }
        .post-stats {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 12px;
        }
        .post-actions {
            display: flex;
            gap: 5px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }
        .action-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 14px;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .action-btn.liked { color: var(--danger); }
        .action-btn.remove-bookmark { color: var(--danger); }
        .action-btn.remove-bookmark:hover {
            background: rgba(255, 71, 87, 0.2);
        }
        .empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }
        .empty i {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        .empty h3 {
            font-size: 18px;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }
        .loading {
            display: flex;
            justify-content: center;
            padding: 40px;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
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
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(20, 20, 40, 0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border);
            padding: 10px 20px;
            display: flex;
            justify-content: space-around;
            z-index: 100;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 11px;
            padding: 5px 15px;
            border-radius: 10px;
            cursor: pointer;
        }
        .nav-item.active { color: var(--accent); }
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
        
        <header>
            <h1 class="page-title">
                <i class="fas fa-bookmark"></i> Saved Posts
            </h1>
        </header>
        
        <div class="posts-container" id="postsContainer">
            <div class="loading">
                <div class="spinner"></div>
            </div>
        </div>
    </div>
    
    <nav class="bottom-nav">
        <a class="nav-item" href="index.php">
            <i class="fas fa-home"></i>
            Home
        </a>
        <a class="nav-item active">
            <i class="fas fa-bookmark"></i>
            Saved
        </a>
    </nav>
    
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
        
        const material = new THREE.PointsMaterial({ size: 0.1, color: 0xffa502, transparent: true, opacity: 0.6 });
        const points = new THREE.Points(geometry, material);
        scene.add(points);
        
        function animate() {
            requestAnimationFrame(animate);
            points.rotation.y += 0.0003;
            renderer.render(scene, camera);
        }
        animate();
        
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
        
        document.addEventListener('DOMContentLoaded', loadBookmarks);
        
        function loadBookmarks() {
            const container = document.getElementById('postsContainer');
            
            fetch('api/get_bookmarks.php')
                .then(res => res.json())
                .then(data => {
                    container.innerHTML = '';
                    
                    if (data.posts.length === 0) {
                        container.innerHTML = `
                            <div class="empty">
                                <i class="fas fa-bookmark"></i>
                                <h3>No saved posts</h3>
                                <p>Bookmark posts to save them for later</p>
                            </div>
                        `;
                    } else {
                        data.posts.forEach(post => {
                            container.appendChild(createPostElement(post));
                        });
                    }
                })
                .catch(() => {
                    container.innerHTML = `
                        <div class="empty">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Error loading bookmarks</h3>
                        </div>
                    `;
                });
        }
        
        function createPostElement(post) {
            const div = document.createElement('div');
            div.className = 'post';
            div.id = `post-${post.id}`;
            
            const imageHtml = post.media_path ? `
                <img src="${post.media_path}" alt="" class="post-image" onclick="openImageModal(this.src)">
            ` : '';
            
            div.innerHTML = `
                <div class="post-header">
                    <div class="post-author">
                        <div class="post-avatar">👻</div>
                        <div class="post-meta">
                            <span class="name">Anonymous Ghost</span>
                            <span class="time">${post.time_ago}</span>
                        </div>
                    </div>
                    <span class="bookmark-badge">
                        <i class="fas fa-bookmark"></i> Saved ${post.bookmarked_time}
                    </span>
                </div>
                
                <p class="post-content">${escapeHtml(post.content)}</p>
                ${imageHtml}
                
                <div class="post-stats">
                    <span><strong>${post.like_count}</strong> likes</span>
                    <span><strong>${post.comment_count}</strong> comments</span>
                    <span><strong>${post.share_count}</strong> shares</span>
                </div>
                
                <div class="post-actions">
                    <button class="action-btn ${post.liked ? 'liked' : ''}" onclick="toggleLike(${post.id}, this)">
                        <i class="${post.liked ? 'fas' : 'far'} fa-heart"></i>
                        <span>Like</span>
                    </button>
                    <button class="action-btn" onclick="toggleBookmark(${post.id}, this)">
                        <i class="fas fa-bookmark"></i>
                        <span>Saved</span>
                    </button>
                </div>
            `;
            
            return div;
        }
        
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
                if (data.success && data.action === 'removed') {
                    const post = document.getElementById(`post-${postId}`);
                    post.style.opacity = '0';
                    setTimeout(() => {
                        post.remove();
                        showToast('Bookmark removed', 'success');
                        if (document.querySelectorAll('.post').length === 0) {
                            document.getElementById('postsContainer').innerHTML = `
                                <div class="empty">
                                    <i class="fas fa-bookmark"></i>
                                    <h3>No saved posts</h3>
                                    <p>Bookmark posts to save them for later</p>
                                </div>
                            `;
                        }
                    }, 300);
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
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
