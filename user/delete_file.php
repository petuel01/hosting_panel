<?php
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$file = $data['file'] ?? '';

$baseDir = "/home/users/";
$userDir = realpath($baseDir . $username);
$filePath = realpath($userDir . DIRECTORY_SEPARATOR . $file);

if (strpos($filePath, $userDir) !== 0 || (!is_file($filePath) && !is_dir($filePath))) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden or file/folder not found"]);
    exit;
}

if (is_dir($filePath)) {
    rmdir($filePath);
} else {
    unlink($filePath);
}

echo json_encode(["success" => true]);
?>