<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

$username = $_POST['username'] ?? '';
$path = $_POST['path'] ?? '';
$file = $_FILES['file'] ?? null;

// Base directory for user files
$baseDir = "/home/users/";

// Validate the username format
if (!preg_match('/^[a-z_][a-z0-9_-]*$/', $username)) {
    echo json_encode(['error' => 'Invalid username format.']);
    exit;
}

// Resolve the user's directory
$userDir = realpath($baseDir . $username);
if (!$userDir || strpos($userDir, realpath($baseDir)) !== 0) {
    echo json_encode(['error' => 'User directory does not exist.']);
    exit;
}

// Resolve the target directory (including subdirectories if provided)
$targetDir = realpath($userDir . DIRECTORY_SEPARATOR . $path);
if (!$targetDir || strpos($targetDir, $userDir) !== 0 || !is_dir($targetDir)) {
    echo json_encode(['error' => 'Invalid target directory.']);
    exit;
}

// Validate the uploaded file
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'File upload failed.']);
    exit;
}

// Validate the file type (only allow images)
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
$fileMimeType = mime_content_type($file['tmp_name']);
if (!in_array($fileMimeType, $allowedMimeTypes)) {
    echo json_encode(['error' => 'Invalid file type. Only JPEG, PNG, and GIF images are allowed.']);
    exit;
}

// Move the uploaded file to the target directory
$targetPath = $targetDir . DIRECTORY_SEPARATOR . basename($file['name']);
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => true, 'message' => 'File uploaded successfully.']);
} else {
    echo json_encode(['error' => 'Failed to save the uploaded file.']);
}
?>