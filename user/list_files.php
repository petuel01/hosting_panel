<?php
header('Content-Type: application/json');

$username = $_GET['username'] ?? '';

if (!preg_match('/^[a-z_][a-z0-9_-]*$/', $username)) {
    echo json_encode(['error' => 'Invalid username format.']);
    exit;
}

$user_dir = "/home/users/$username";

if (!is_dir($user_dir)) {
    echo json_encode(['error' => "Directory for user '$username' does not exist."]);
    exit;
}

$items = array_diff(scandir($user_dir), ['.', '..']);
$result = [];

foreach ($items as $item) {
    $filepath = "$user_dir/$item";
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