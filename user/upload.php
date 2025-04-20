<?php
$username = $_POST['username'] ?? '';
$path = $_POST['path'] ?? '';
$file = $_FILES['file'] ?? null;

$baseDir = "/home/users/";
$userDir = realpath($baseDir . $username);
$targetDir = realpath($userDir . DIRECTORY_SEPARATOR . $path);

if (!$file || !$username || !is_dir($targetDir)) {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$targetPath = $targetDir . DIRECTORY_SEPARATOR . basename($file['name']);
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to upload file.']);
}
?>