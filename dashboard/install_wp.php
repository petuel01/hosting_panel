<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit;
}

$username = $_SESSION['username'];
$domain = $_POST['domain'] ?? '';
$wp_username = $_POST['wp_username'] ?? '';
$wp_password = $_POST['wp_password'] ?? '';

// Validate input
if (empty($domain) || empty($wp_username) || empty($wp_password)) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}

// Directory for WordPress sites
$wordpressDir = "/home/users/" . $username . "/wordpress_sites";
if (!is_dir($wordpressDir)) {
    mkdir($wordpressDir, 0755, true);
}

// Check if the site already exists
$siteDir = $wordpressDir . '/' . parse_url($domain, PHP_URL_HOST);
if (is_dir($siteDir)) {
    echo json_encode(['success' => false, 'error' => 'A site with this domain already exists.']);
    exit;
}

// Run the bash script to install WordPress
$script = "/path/to/wordpress_installer.sh";
$command = escapeshellcmd("sudo bash $script $domain $wp_username $wp_password $siteDir");
$output = shell_exec($command);

if ($output) {
    echo json_encode(['success' => true, 'message' => 'WordPress site created successfully.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to create WordPress site.']);
}
?>