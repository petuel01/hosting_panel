<?php
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$file = $data['file'] ?? '';

$baseDir = "/home/users/";
$userDir = realpath($baseDir . $username);
$filePath = realpath($userDir . DIRECTORY_SEPARATOR . $file);

// Validate the file path
if (!$filePath || strpos($filePath, $userDir) !== 0 || (!is_file($filePath) && !is_dir($filePath))) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden or file/folder not found"]);
    exit;
}

// Delete the file or folder
if (is_dir($filePath)) {
    if (!rmdir($filePath)) {
        echo json_encode(["error" => "Failed to delete folder. Ensure it is empty."]);
        exit;
    }
} else {
    if (!unlink($filePath)) {
        echo json_encode(["error" => "Failed to delete file."]);
        exit;
    }
}

echo json_encode(["success" => true]);
?>