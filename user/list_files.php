<?php
// Example: ?username=john

// Get username from query parameter
$username = $_GET['username'] ?? '';
$baseDir = "/home/users/";
$userDir = realpath($baseDir . $username);  // Prevent path traversal

// Validate
if (!$username || !is_dir($userDir)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid user or directory not found"]);
    exit;
}

// List files
$files = scandir($userDir);
$result = [];

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;

    $path = $userDir . DIRECTORY_SEPARATOR . $file;
    $result[] = [
        'name' => $file,
        'size_kb' => round(filesize($path) / 1024, 2),
        'is_dir' => is_dir($path)
    ];
}

// Output as JSON
header('Content-Type: application/json');
echo json_encode($result);
?>
