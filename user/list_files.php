<?php
header('Content-Type: application/json');

$username = $_GET['username'] ?? '';
$path = $_GET['path'] ?? '';

if (!preg_match('/^[a-z_][a-z0-9_-]*$/', $username)) {
    echo json_encode(['error' => 'Invalid username format.']);
    exit;
}

$baseDir = "/home/users/$username";
$targetDir = realpath($baseDir . DIRECTORY_SEPARATOR . $path);

// Ensure the target directory is within the user's base directory
if (strpos($targetDir, realpath($baseDir)) !== 0 || !is_dir($targetDir)) {
    echo json_encode(['error' => 'Invalid directory path.']);
    exit;
}

$items = array_diff(scandir($targetDir), ['.', '..']);
$result = [];

// Add a "Parent Directory" link if not in the base directory
if ($targetDir !== realpath($baseDir)) {
    $result[] = [
        'name' => '..',
        'type' => 'folder',
        'size' => '-',
        'modified' => '-'
    ];
}

foreach ($items as $item) {
    $filepath = $targetDir . DIRECTORY_SEPARATOR . $item;
    $is_dir = is_dir($filepath);
    $result[] = [
        'name' => $item,
        'type' => $is_dir ? 'folder' : 'file',
        'size' => $is_dir ? '-' : round(filesize($filepath) / 1024, 2) . ' KB',
        'modified' => date("Y-m-d H:i:s", filemtime($filepath))
    ];
}

echo json_encode($result);
?>