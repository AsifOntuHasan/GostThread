<?php
session_start();

header('Content-Type: application/json');

$upload_dir = __DIR__ . '/../assets/uploads/posts/';
$max_size = 5 * 1024 * 1024;

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error']);
    exit;
}

if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB allowed']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

$extension = match($mime_type) {
    'image/jpeg' => '.jpg',
    'image/png' => '.png',
    'image/gif' => '.gif',
    'image/webp' => '.webp',
    default => '.jpg'
};

$user_hash = md5($_SERVER['REMOTE_ADDR'] . session_id());
$filename = $user_hash . '_' . time() . '_' . uniqid() . $extension;
$target_path = $upload_dir . $filename;

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    $image_url = 'assets/uploads/posts/' . $filename;
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded',
        'image_url' => $image_url
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
}
