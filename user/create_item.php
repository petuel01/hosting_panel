<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit;
}

$username = $_SESSION['username'];
$path = isset($_POST['path']) ? $_POST['path'] : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$type = isset($_POST['type']) ? $_POST['type'] : '';

// Validate input
if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'Name cannot be empty.']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $name)) {
    echo json_encode(['success' => false, 'error' => 'Invalid name. Only letters, numbers, spaces, underscores, and dashes are allowed.']);
    exit;
}

// Construct the full path
$base_dir = "/home/users/" . $username;
$target_dir = rtrim($base_dir . "/" . $path, '/');
$target_path = $target_dir . "/" . $name;

// Ensure the target directory exists
if (!is_dir($target_dir)) {
    echo json_encode(['success' => false, 'error' => 'Target directory does not exist.']);
    exit;
}

// Check if the item already exists
if (file_exists($target_path)) {
    echo json_encode(['success' => false, 'error' => 'A file or folder with this name already exists.']);
    exit;
}

// Create the file or folder
if ($type === 'folder') {
    if (mkdir($target_path, 0755)) {
        echo json_encode(['success' => true, 'message' => 'Folder created successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create folder.']);
    }
} elseif ($type === 'file') {
    if (file_put_contents($target_path, '') !== false) {
        echo json_encode(['success' => true, 'message' => 'File created successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create file.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid type.']);
}