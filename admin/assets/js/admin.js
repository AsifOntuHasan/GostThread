function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 3000);
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
            if (el) {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function moderateComment(id, action) {
    fetch('api/moderate.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, type: 'comment', action })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            const el = document.getElementById(`comment-${id}`);
            if (el) {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function deletePost(id) {
    if (!confirm('Are you sure you want to delete this post permanently?')) return;
    
    fetch('api/moderate.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, type: 'post', action: 'delete' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Post deleted permanently', 'success');
            const el = document.getElementById(`post-${id}`);
            if (el) {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }
        } else {
            showToast(data.message, 'error');
        }
    });
}

function deleteComment(id) {
    if (!confirm('Are you sure you want to delete this comment permanently?')) return;
    
    fetch('api/moderate.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, type: 'comment', action: 'delete' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Comment deleted permanently', 'success');
            const el = document.getElementById(`comment-${id}`);
            if (el) {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }
        } else {
            showToast(data.message, 'error');
        }
    });
}

function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});
