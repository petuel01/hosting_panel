<?php
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$file = $data['file'] ?? '';
$content = $data['content'] ?? '';

$baseDir = "/home/users/";
$userDir = realpath($baseDir . $username);
$filePath = realpath($userDir . DIRECTORY_SEPARATOR . $file);

if (strpos($filePath, $userDir) !== 0 || !is_file($filePath)) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden or file not found.']);
    exit;
}

if (file_put_contents($filePath, $content) !== false) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to save file.']);
}
?>