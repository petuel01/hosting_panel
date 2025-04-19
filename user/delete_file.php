<?php
$username = $_POST['username'] ?? '';
$file = $_POST['file'] ?? '';

$baseDir = "/home/users/";
$userDir = realpath($baseDir . $username);
$filePath = realpath($userDir . DIRECTORY_SEPARATOR . $file);

if (strpos($filePath, $userDir) !== 0 || !is_file($filePath)) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden or file not found"]);
    exit;
}

unlink($filePath);
echo json_encode(["success" => true]);
?>
