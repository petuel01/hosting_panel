<?php
header('Content-Type: application/json');

$username = $_GET['username'] ?? '';

if (!preg_match('/^[a-z_][a-z0-9_-]*$/', $username)) {
    echo json_encode([]);
    exit;
}

$user_dir = "/home/$username";

if (!is_dir($user_dir)) {
    echo json_encode([]);
    exit;
}

$files = array_diff(scandir($user_dir), ['.', '..']);
$result = [];

foreach ($files as $file) {
    $filepath = "$user_dir/$file";
    if (is_file($filepath)) {
        $result[] = [
            'name' => $file,
            'size' => round(filesize($filepath) / 1024, 2),
            'modified' => date("Y-m-d H:i:s", filemtime($filepath))
        ];
    }
}

echo json_encode($result);
?>
