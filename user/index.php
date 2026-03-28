<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GhostThread - Anonymous Social</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --bg-primary: #0a0a1a;
            --bg-secondary: #1a1a3e;
            --bg-card: rgba(255, 255, 255, 0.05);
            --bg-card-hover: rgba(255, 255, 255, 0.08);
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
            background: var(--bg-primary);
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
        
        .app-container {
            position: relative;
            z-index: 1;
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            min-height: 100vh;
        }
        
        /* Left Sidebar */
        .left-sidebar {
            width: 280px;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid var(--border);
        }
        
        .logo-section {
            text-align: center;
            padding: 20px 0 40px;
        }
        
        .logo {
            font-size: 50px;
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(108, 99, 255, 0.6));
            display: block;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50% { transform: translateY(-10px) rotate(2deg); }
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--accent), var(--accent-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-top: 10px;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 15px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }
        
        .nav-item:hover {
            background: var(--bg-card);
            color: var(--text);
        }
        
        .nav-item.active {
            background: rgba(108, 99, 255, 0.2);
            color: var(--accent);
        }
        
        .nav-item i {
            width: 24px;
            font-size: 20px;
        }
        
        .nav-divider {
            height: 1px;
            background: var(--border);
            margin: 25px 0;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }
        
        .tagline {
            color: var(--text-muted);
            font-size: 13px;
            text-align: center;
            line-height: 1.6;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            margin-right: 320px;
            padding: 30px;
            min-height: 100vh;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-title i {
            color: var(--accent);
        }
        
        /* Create Post */
        .create-post {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .create-input-row {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }
        
        .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .create-textarea {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 12px 15px;
            color: var(--text);
            font-size: 15px;
            resize: none;
            outline: none;
            min-height: 45px;
            max-height: 200px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        .create-textarea:focus {
            border-color: var(--accent);
            min-height: 100px;
        }
        
        .create-textarea::placeholder {
            color: var(--text-muted);
        }
        
        .create-preview {
            display: none;
            margin-top: 15px;
            position: relative;
        }
        
        .create-preview.active {
            display: block;
        }
        
        .create-preview img {
            width: 100%;
            border-radius: 15px;
            max-height: 300px;
            object-fit: cover;
        }
        
        .remove-preview {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.7);
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }
        
        .create-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }
        
        .create-tools {
            display: flex;
            gap: 10px;
        }
        
        .tool-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .tool-btn:hover {
            background: rgba(108, 99, 255, 0.2);
            color: var(--accent);
        }
        
        .tool-btn input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .char-count {
            color: var(--text-muted);
            font-size: 13px;
            margin-right: 15px;
        }
        
        .post-btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, var(--accent), var(--accent-tertiary));
            border: none;
            border-radius: 25px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .post-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(108, 99, 255, 0.4);
        }
        
        .post-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Posts */
        .posts-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
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
        
        .post-meta span {
            display: block;
        }
        
        .post-meta .name {
            font-weight: 600;
            font-size: 14px;
        }
        
        .post-meta .time {
            font-size: 12px;
            color: var(--text-muted);
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
            transition: transform 0.3s ease;
        }
        
        .post-image:hover {
            transform: scale(1.01);
        }
        
        .post-stats {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 12px;
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
            color: var(--text);
        }
        
        .action-btn i {
            font-size: 18px;
        }
        
        .action-btn.liked {
            color: var(--danger);
        }
        
        .action-btn.liked i {
            animation: heartBeat 0.3s ease;
        }
        
        @keyframes heartBeat {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        
        .action-btn.bookmarked {
            color: #ffa502;
        }
        
        /* Comments */
        .comments-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }
        
        .comments-list {
            margin-bottom: 15px;
        }
        
        .comment {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }
        
        .comment-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-secondary), var(--accent-tertiary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }
        
        .comment-body {
            flex: 1;
            background: rgba(255, 255, 255, 0.03);
            padding: 8px 12px;
            border-radius: 12px;
        }
        
        .comment-text {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .comment-time {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        
        .view-more {
            color: var(--accent);
            font-size: 13px;
            cursor: pointer;
            margin-bottom: 12px;
        }
        
        .view-more:hover {
            text-decoration: underline;
        }
        
        .comment-input-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .comment-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 10px 15px;
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-tertiary));
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Share Menu */
        .share-menu {
            display: none;
            position: absolute;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 8px;
            min-width: 180px;
            z-index: 100;
        }
        
        .share-menu.active {
            display: block;
        }
        
        .share-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: var(--text-secondary);
            cursor: pointer;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .share-item:hover {
            background: rgba(108, 99, 255, 0.2);
            color: var(--text);
        }
        
        /* Right Sidebar */
        .right-sidebar {
            width: 320px;
            padding: 30px 20px;
            position: fixed;
            right: 0;
            height: 100vh;
            overflow-y: auto;
            border-left: 1px solid var(--border);
        }
        
        .sidebar-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .sidebar-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-card h3 i {
            color: var(--accent);
        }
        
        .trending-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .trending-item:last-child {
            border-bottom: none;
        }
        
        .trending-item:hover {
            padding-left: 10px;
        }
        
        .trending-tag {
            font-weight: 600;
            font-size: 14px;
            color: var(--accent);
        }
        
        .trending-count {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: rgba(108, 99, 255, 0.1);
            border-radius: 12px;
        }
        
        .stat-item h4 {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent);
        }
        
        .stat-item p {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            flex-shrink: 0;
        }
        
        .activity-text {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.4;
        }
        
        .activity-text strong {
            color: var(--text);
        }
        
        /* Loading */
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
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Empty */
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
        
        /* Image Modal */
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
        
        .image-modal.active {
            display: flex;
        }
        
        .image-modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
        }
        
        /* Toast */
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
        
        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        
        .toast.success {
            background: linear-gradient(135deg, var(--success), #7bed9f);
        }
        
        .toast.error {
            background: linear-gradient(135deg, var(--danger), var(--accent-secondary));
        }
        
        /* Load More */
        .load-more {
            text-align: center;
            padding: 20px;
        }
        
        .load-more-btn {
            padding: 12px 40px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 30px;
            color: var(--text-secondary);
            font-size: 14px;
            cursor: pointer;
        }
        
        .load-more-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Mobile */
        @media (max-width: 1200px) {
            .right-sidebar {
                display: none;
            }
            .main-content {
                margin-right: 0;
            }
        }
        
        @media (max-width: 900px) {
            .left-sidebar {
                width: 80px;
                padding: 20px 10px;
            }
            .logo-section {
                padding: 10px 0;
            }
            .logo {
                font-size: 30px;
            }
            .logo-text, .tagline, .nav-item span, .sidebar-footer {
                display: none;
            }
            .nav-item {
                justify-content: center;
                padding: 15px;
            }
            .main-content {
                margin-left: 80px;
            }
            .nav-divider {
                margin: 15px 0;
            }
        }
        
        @media (max-width: 900px) {
            .right-sidebar {
                display: none;
            }
            .main-content {
                margin-right: 0;
            }
        }
        
        @media (max-width: 600px) {
            .left-sidebar {
                width: 100%;
                height: auto;
                position: fixed;
                bottom: 0;
                top: auto;
                border-right: none;
                border-top: 1px solid var(--border);
                background: rgba(20, 20, 40, 0.95);
                backdrop-filter: blur(20px);
                padding: 10px 20px;
                z-index: 100;
            }
            .logo-section, .nav-divider, .sidebar-footer {
                display: none !important;
            }
            .nav-menu {
                display: flex !important;
                justify-content: space-around;
                list-style: none;
                margin: 0;
                padding: 0;
            }
            .nav-menu li {
                flex: 1;
            }
            .nav-item {
                flex-direction: column;
                gap: 4px;
                padding: 8px;
                font-size: 10px;
                text-align: center;
                border-radius: 10px;
            }
            .nav-item i {
                font-size: 20px;
            }
            .nav-item span {
                display: block;
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
                padding-bottom: 100px;
            }
        }
    </style>
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    
    <div class="image-modal" id="imageModal" onclick="closeImageModal()">
        <img id="modalImage" src="" alt="">
    </div>
    
    <div class="app-container">
        <!-- Left Sidebar -->
        <aside class="left-sidebar">
            <div class="logo-section">
                <span class="logo">👻</span>
                <div class="logo-text">GhostThread</div>
            </div>
            
            <ul class="nav-menu">
                <li>
                    <a class="nav-item active" onclick="navigateTo('home')">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a class="nav-item" onclick="navigateTo('explore')">
                        <i class="fas fa-compass"></i>
                        <span>Explore</span>
                    </a>
                </li>
                <li>
                    <a class="nav-item" onclick="location.href='bookmarks.php'">
                        <i class="fas fa-bookmark"></i>
                        <span>Bookmarks</span>
                    </a>
                </li>
                <li>
                    <a class="nav-item" onclick="location.href='post.php?random=1'">
                        <i class="fas fa-random"></i>
                        <span>Random</span>
                    </a>
                </li>
            </ul>
            
            <div class="nav-divider"></div>
            
            <div class="sidebar-footer">
                <p class="tagline">No Login. No Profile.<br>Just Conversation.</p>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <h1 class="page-title" id="pageTitle">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </h1>
            
            <div class="create-post">
                <div class="create-input-row">
                    <div class="avatar">👻</div>
                    <textarea class="create-textarea" id="postContent" placeholder="What's on your mind?" maxlength="500" oninput="updateCharCount()"></textarea>
                </div>
                
                <div class="create-preview" id="createPreview">
                    <img id="previewImg" src="" alt="">
                    <button class="remove-preview" onclick="removeImage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="create-actions">
                    <div class="create-tools">
                        <label class="tool-btn" title="Add Photo">
                            <i class="fas fa-image"></i>
                            <input type="file" accept="image/*" onchange="handleImageSelect(this)">
                        </label>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <span class="char-count" id="charCount">0/500</span>
                        <button class="post-btn" id="postBtn" onclick="submitPost()">
                            <i class="fas fa-paper-plane"></i> Post
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="posts-container" id="postsContainer">
                <div class="loading">
                    <div class="spinner"></div>
                </div>
            </div>
            
            <div class="load-more" id="loadMoreSection" style="display: none;">
                <button class="load-more-btn" id="loadMoreBtn">Load More Posts</button>
            </div>
        </main>
        
        <!-- Right Sidebar -->
        <aside class="right-sidebar">
            <div class="sidebar-card">
                <h3><i class="fas fa-chart-bar"></i> Community Stats</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <h4 id="totalPosts">0</h4>
                        <p>Total Posts</p>
                    </div>
                    <div class="stat-item">
                        <h4 id="totalComments">0</h4>
                        <p>Comments</p>
                    </div>
                    <div class="stat-item">
                        <h4 id="totalLikes">0</h4>
                        <p>Likes Given</p>
                    </div>
                    <div class="stat-item">
                        <h4 id="totalShares">0</h4>
                        <p>Shares</p>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-card">
                <h3><i class="fas fa-bolt"></i> Activity</h3>
                <div id="recentActivity">
                    <div class="activity-item">
                        <div class="activity-dot"></div>
                        <div class="activity-text">Loading activity...</div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
    
    <div class="toast" id="toast"></div>
    
    <script>
        // Three.js Background
        const canvas = document.getElementById('bg-canvas');
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
        
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        camera.position.z = 30;
        
        const geometry = new THREE.BufferGeometry();
        const count = 1500;
        const positions = new Float32Array(count * 3);
        
        for (let i = 0; i < count * 3; i++) {
            positions[i] = (Math.random() - 0.5) * 80;
        }
        
        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        
        const material = new THREE.PointsMaterial({
            size: 0.1,
            color: 0x6c63ff,
            transparent: true,
            opacity: 0.6
        });
        
        const points = new THREE.Points(geometry, material);
        scene.add(points);
        
        let mouseX = 0, mouseY = 0;
        document.addEventListener('mousemove', (e) => {
            mouseX = (e.clientX / window.innerWidth) * 2 - 1;
            mouseY = -(e.clientY / window.innerHeight) * 2 + 1;
        });
        
        function animate() {
            requestAnimationFrame(animate);
            points.rotation.y += 0.0003;
            camera.position.x += (mouseX * 2 - camera.position.x) * 0.02;
            camera.position.y += (mouseY * 2 - camera.position.y) * 0.02;
            camera.lookAt(scene.position);
            renderer.render(scene, camera);
        }
        animate();
        
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
        
        // Navigation
        function navigateTo(page) {
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            event.target.closest('.nav-item').classList.add('active');
            
            const title = document.getElementById('pageTitle');
            const icons = { home: 'fa-home', explore: 'fa-compass', bookmarks: 'fa-bookmark' };
            
            if (page === 'home') {
                title.innerHTML = `<i class="fas ${icons.home}"></i><span>Home</span>`;
                loadPosts();
            } else if (page === 'explore') {
                title.innerHTML = `<i class="fas ${icons.explore}"></i><span>Explore</span>`;
                loadPosts(true);
            }
        }
        
        function searchTag(tag) {
            document.getElementById('postContent').value = `#${tag} `;
            document.getElementById('postContent').focus();
            updateCharCount();
        }
        
        // Stats
        function loadStats() {
            fetch('api/stats.php')
                .then(r => r.json())
                .then(d => {
                    document.getElementById('totalPosts').textContent = d.total_posts || 0;
                    document.getElementById('totalComments').textContent = d.total_comments || 0;
                    document.getElementById('totalLikes').textContent = d.total_likes || 0;
                    document.getElementById('totalShares').textContent = d.total_shares || 0;
                    
                    // Load activity
                    const activityContainer = document.getElementById('recentActivity');
                    if (d.recent_activity && d.recent_activity.length > 0) {
                        activityContainer.innerHTML = d.recent_activity.map(a => `
                            <div class="activity-item">
                                <div class="activity-dot" style="background: ${a.type === 'post' ? '#6c63ff' : a.type === 'comment' ? '#ff6b9d' : '#2ed573'}"></div>
                                <div class="activity-text">${a.text}</div>
                            </div>
                        `).join('');
                    } else {
                        activityContainer.innerHTML = '<div class="activity-item"><div class="activity-dot"></div><div class="activity-text">No recent activity</div></div>';
                    }
                });
        }
        loadStats();
        setInterval(loadStats, 30000);
        
        // App State
        let currentPage = 1;
        let isLoading = false;
        let hasMore = true;
        let selectedImagePath = '';
        let allPosts = [];
        let exploreMode = false;
        
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadPosts();
            
            document.getElementById('loadMoreBtn').addEventListener('click', () => {
                if (!isLoading && hasMore) {
                    currentPage++;
                    loadPosts(true);
                }
            });
        });
        
        function updateCharCount() {
            const content = document.getElementById('postContent').value;
            document.getElementById('charCount').textContent = `${content.length}/500`;
        }
        
        function handleImageSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const formData = new FormData();
                formData.append('image', file);
                
                fetch('api/upload_image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        selectedImagePath = data.image_url;
                        document.getElementById('previewImg').src = data.image_url;
                        document.getElementById('createPreview').classList.add('active');
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(() => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        selectedImagePath = e.target.result;
                        document.getElementById('previewImg').src = e.target.result;
                        document.getElementById('createPreview').classList.add('active');
                    };
                    reader.readAsDataURL(file);
                });
            }
        }
        
        function removeImage() {
            selectedImagePath = '';
            document.getElementById('createPreview').classList.remove('active');
            document.getElementById('previewImg').src = '';
            document.querySelector('.tool-btn input').value = '';
        }
        
        function submitPost() {
            const content = document.getElementById('postContent').value.trim();
            
            if (!content && !selectedImagePath) {
                showToast('Write something or add an image!', 'error');
                return;
            }
            
            const btn = document.getElementById('postBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch('api/create_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `content=${encodeURIComponent(content)}&media_path=${encodeURIComponent(selectedImagePath)}`
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Post';
                
                if (data.success) {
                    showToast(data.message, 'success');
                    document.getElementById('postContent').value = '';
                    document.getElementById('charCount').textContent = '0/500';
                    removeImage();
                    currentPage = 1;
                    allPosts = [];
                    loadPosts();
                    loadStats();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Post';
                showToast('Error creating post', 'error');
            });
        }
        
        function loadPosts(explore = false) {
            if (isLoading) return;
            isLoading = true;
            exploreMode = explore;
            
            const container = document.getElementById('postsContainer');
            if (!explore) {
                container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
            }
            
            const url = explore ? `api/fetch_posts.php?page=${currentPage}&sort=random` : `api/fetch_posts.php?page=${currentPage}`;
            
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (!explore || currentPage === 1) {
                        container.innerHTML = '';
                        allPosts = [];
                    }
                    
                    if (data.posts.length === 0 && currentPage === 1) {
                        container.innerHTML = `
                            <div class="empty">
                                <i class="fas fa-ghost"></i>
                                <h3>No posts yet</h3>
                                <p>Be the first to share something anonymous!</p>
                            </div>
                        `;
                        document.getElementById('loadMoreSection').style.display = 'none';
                    } else {
                        data.posts.forEach(post => {
                            allPosts.push(post);
                            container.appendChild(createPostElement(post));
                        });
                        
                        hasMore = currentPage < data.total_pages;
                        document.getElementById('loadMoreSection').style.display = hasMore ? 'block' : 'none';
                    }
                    
                    isLoading = false;
                })
                .catch(err => {
                    container.innerHTML = `
                        <div class="empty">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Error loading posts</h3>
                            <p>Please refresh the page</p>
                        </div>
                    `;
                    isLoading = false;
                });
        }
        
        function createPostElement(post) {
            const div = document.createElement('div');
            div.className = 'post';
            div.id = `post-${post.id}`;
            
            const comments = post.comments.map(c => `
                <div class="comment">
                    <div class="comment-avatar">👻</div>
                    <div class="comment-body">
                        <div class="comment-text">${escapeHtml(c.content)}</div>
                        <div class="comment-time">${c.time_ago}</div>
                    </div>
                </div>
            `).join('');
            
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
                    <a href="post.php?id=${post.id}" class="action-btn" style="flex:0; padding:8px;">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
                
                <p class="post-content">${escapeHtml(post.content)}</p>
                ${imageHtml}
                
                <div class="post-stats">
                    <span onclick="toggleLike(${post.id}, this)">
                        <strong>${post.like_count}</strong> likes
                    </span>
                    <span onclick="showComments(${post.id})">
                        <strong>${post.comment_count}</strong> comments
                    </span>
                    <span>
                        <strong>${post.share_count}</strong> shares
                    </span>
                </div>
                
                <div class="post-actions">
                    <button class="action-btn ${post.liked ? 'liked' : ''}" onclick="toggleLike(${post.id}, this)" data-post-id="${post.id}">
                        <i class="${post.liked ? 'fas' : 'far'} fa-heart"></i>
                        <span>Like</span>
                    </button>
                    <button class="action-btn" onclick="showComments(${post.id})">
                        <i class="far fa-comment"></i>
                        <span>Comment</span>
                    </button>
                    <button class="action-btn ${post.bookmarked ? 'bookmarked' : ''}" onclick="toggleBookmark(${post.id}, this)" data-post-id="${post.id}">
                        <i class="${post.bookmarked ? 'fas' : 'far'} fa-bookmark"></i>
                        <span>Save</span>
                    </button>
                    <button class="action-btn" onclick="showShareMenu(${post.id}, event)">
                        <i class="fas fa-share"></i>
                        <span>Share</span>
                    </button>
                </div>
                
                <div class="comments-section" id="comments-${post.id}" style="display: none;">
                    <div class="comments-list">
                        ${comments}
                    </div>
                    ${post.comment_count > 3 ? `<div class="view-more" onclick="loadMoreComments(${post.id})">View all ${post.comment_count} comments</div>` : ''}
                    <div class="comment-input-row">
                        <input type="text" class="comment-input" id="comment-input-${post.id}" placeholder="Write a comment..." onkeypress="if(event.key==='Enter') addComment(${post.id})">
                        <button class="send-btn" onclick="addComment(${post.id})">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
                
                <div class="share-menu" id="share-menu-${post.id}">
                    <div class="share-item" onclick="sharePost(${post.id}, 'ghostthread')">
                        <i class="fas fa-ghost"></i> Share on GhostThread
                    </div>
                    <div class="share-item" onclick="copyLink(${post.id})">
                        <i class="fas fa-link"></i> Copy Link
                    </div>
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
                    
                    const postEl = document.getElementById(`post-${postId}`);
                    const stats = postEl.querySelector('.post-stats');
                    stats.innerHTML = stats.innerHTML.replace(/<strong>(\d+)<\/strong>/, (match, num) => {
                        return `<strong>${data.like_count}</strong>`;
                    });
                    loadStats();
                }
            })
            .catch(() => showToast('Error updating like', 'error'));
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
            })
            .catch(() => showToast('Error saving post', 'error'));
        }
        
        function showComments(postId) {
            const section = document.getElementById(`comments-${postId}`);
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
            if (section.style.display === 'block') {
                section.querySelector('.comment-input').focus();
            }
        }
        
        function addComment(postId) {
            const input = document.getElementById(`comment-input-${postId}`);
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
                    
                    if (data.status === 'approved') {
                        const section = document.getElementById(`comments-${postId}`);
                        const list = section.querySelector('.comments-list');
                        const newComment = document.createElement('div');
                        newComment.className = 'comment';
                        newComment.innerHTML = `
                            <div class="comment-avatar">👻</div>
                            <div class="comment-body">
                                <div class="comment-text">${escapeHtml(data.comment.content)}</div>
                                <div class="comment-time">Just now</div>
                            </div>
                        `;
                        list.appendChild(newComment);
                        
                        const postEl = document.getElementById(`post-${postId}`);
                        const stats = postEl.querySelector('.post-stats');
                        const commentSpan = stats.querySelectorAll('span')[1];
                        const currentCount = parseInt(commentSpan.querySelector('strong').textContent);
                        commentSpan.innerHTML = `<strong>${currentCount + 1}</strong> comments`;
                        loadStats();
                    }
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(() => showToast('Error adding comment', 'error'));
        }
        
        function showShareMenu(postId, event) {
            event.stopPropagation();
            document.querySelectorAll('.share-menu').forEach(m => m.classList.remove('active'));
            const menu = document.getElementById(`share-menu-${postId}`);
            menu.classList.add('active');
            
            setTimeout(() => {
                document.addEventListener('click', function closeMenu() {
                    menu.classList.remove('active');
                    document.removeEventListener('click', closeMenu);
                });
            }, 0);
        }
        
        function sharePost(postId, platform) {
            fetch('api/share.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&platform=${platform}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Post shared!', 'success');
                    const postEl = document.getElementById(`post-${postId}`);
                    const stats = postEl.querySelector('.post-stats');
                    const shareSpan = stats.querySelectorAll('span')[2];
                    shareSpan.innerHTML = `<strong>${data.share_count}</strong> shares`;
                    loadStats();
                }
            });
        }
        
        function copyLink(postId) {
            const link = `${window.location.origin}/ghostthread/user/post.php?id=${postId}`;
            navigator.clipboard.writeText(link).then(() => {
                showToast('Link copied!', 'success');
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
            toast.className = `toast ${type}`;
            toast.classList.add('show');
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
