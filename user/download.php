<?php
$username = $_GET['username'] ?? '';
$file = $_GET['file'] ?? '';

$baseDir = "/home/users/";
$userDir = realpath($baseDir . $username);
$filePath = realpath($userDir . DIRECTORY_SEPARATOR . $file);

// Check file is under user directory (security)
if (strpos($filePath, $userDir) !== 0 || !is_file($filePath)) {
    http_response_code(403);
    die("Forbidden or file not found.");
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
readfile($filePath);
exit;
?>
