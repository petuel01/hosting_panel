<?php
$username = $_POST['username'] ?? '';
$file = $_FILES['file'] ?? null;

$baseDir = "/home/users/";
$userDir = realpath($baseDir . $username);

if (!$file || !$username || !is_dir($userDir)) {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$targetPath = $userDir . DIRECTORY_SEPARATOR . basename($file['name']);
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to upload file.']);
}
?>