<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once '../database/db.php';

$user_id = $_SESSION['user_id'];
$domain = $_POST['domain'];
$wp_username = $_POST['wp_username'];
$wp_password = $_POST['wp_password'];
$wp_email = $_POST['wp_email'];

// Use WordPress username and password as database credentials
$db_name = 'lxd_hosting';
$db_user = $wp_username;
$db_password = $wp_password;

// Directory for the WordPress site inside the user's folder
$linux_username = $_SESSION['linux_username']; // Ensure this is set in the session
$wordpressDir = "/home/users/" . $linux_username . "/wordpress_sites";
$log_file = __DIR__ . '/install_wp_process.log'; // Log file for debugging

// Ensure the parent directory exists and is writable
if (!is_dir($wordpressDir)) {
    $create_dir_cmd = "sudo mkdir -p $wordpressDir";
    exec($create_dir_cmd . " 2>&1", $output, $return_var);

    // Log the output for debugging
    file_put_contents($log_file, "Command: $create_dir_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to create WordPress directory. Check logs for details.']);
        exit;
    }

    $chown_cmd = "sudo chown -R www-data:www-data $wordpressDir";
    exec($chown_cmd . " 2>&1", $output, $return_var);

    // Log the output for debugging
    file_put_contents($log_file, "Command: $chown_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to set ownership for WordPress directory. Check logs for details.']);
        exit;
    }

    $chmod_cmd = "sudo chmod -R 755 $wordpressDir";
    exec($chmod_cmd . " 2>&1", $output, $return_var);

    // Log the output for debugging
    file_put_contents($log_file, "Command: $chmod_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to set permissions for WordPress directory. Check logs for details.']);
        exit;
    }
}

// Use a fixed folder name for the WordPress site
$siteDir = $wordpressDir . '/site1';
if (is_dir($siteDir)) {
    // Check if reinstallation is requested
    if (isset($_POST['force_reinstall']) && $_POST['force_reinstall'] === 'true') {
        $remove_dir_cmd = "sudo rm -rf $siteDir";
        exec($remove_dir_cmd . " 2>&1", $output, $return_var);

        // Log the output for debugging
        file_put_contents($log_file, "Command: $remove_dir_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

        if ($return_var !== 0) {
            echo json_encode(['success' => false, 'error' => 'Failed to remove existing site directory. Check logs for details.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'A site with this name already exists.']);
        exit;
    }
}

$create_site_dir_cmd = "sudo mkdir -p $siteDir";
exec($create_site_dir_cmd . " 2>&1", $output, $return_var);

// Log the output for debugging
file_put_contents($log_file, "Command: $create_site_dir_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

if ($return_var !== 0) {
    echo json_encode(['success' => false, 'error' => 'Failed to create site directory. Check logs for details.']);
    exit;
}

$chown_site_cmd = "sudo chown -R www-data:www-data $siteDir";
exec($chown_site_cmd . " 2>&1", $output, $return_var);

// Log the output for debugging
file_put_contents($log_file, "Command: $chown_site_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

if ($return_var !== 0) {
    echo json_encode(['success' => false, 'error' => 'Failed to set ownership for site directory. Check logs for details.']);
    exit;
}

$chmod_site_cmd = "sudo chmod -R 755 $siteDir";
exec($chmod_site_cmd . " 2>&1", $output, $return_var);

// Log the output for debugging
file_put_contents($log_file, "Command: $chmod_site_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

if ($return_var !== 0) {
    echo json_encode(['success' => false, 'error' => 'Failed to set permissions for site directory. Check logs for details.']);
    exit;
}

// Step 1: Download and Configure WordPress
$download_wp_cmd = "cd $siteDir && sudo wget -q https://wordpress.org/latest.tar.gz && sudo tar -xzf latest.tar.gz --strip-components=1 && sudo rm latest.tar.gz";
exec($download_wp_cmd . " 2>&1", $output, $return_var);

// Log the output for debugging
file_put_contents($log_file, "Command: $download_wp_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

if ($return_var !== 0) {
    echo json_encode(['success' => false, 'error' => 'Failed to download and configure WordPress. Check logs for details.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'WordPress site created successfully.']);
?>