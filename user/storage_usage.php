<?php
$username = $_GET['username'] ?? '';

$baseDir = "/home/users/";
$userDir = realpath($baseDir . $username);

if (!is_dir($userDir)) {
    echo json_encode(['error' => 'User directory not found.']);
    exit;
}

$totalSpace = 2000000; // Example: 2GB in KB
$usedSpace = shell_exec("du -sk $userDir | cut -f1");

echo json_encode([
    'total' => $totalSpace,
    'used' => (int)$usedSpace
]);
?>