<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

require_once '../database/db.php';

$user_id = $_SESSION['user_id'];
$domain = $_POST['domain'];
$site_name = $_POST['site_name'];
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

// Ensure the parent directory exists and is writable
if (!is_dir($wordpressDir)) {
    exec("sudo mkdir -p $wordpressDir && sudo chown -R www-data:www-data $wordpressDir && sudo chmod -R 755 $wordpressDir", $output, $return_var);
    if ($return_var !== 0) {
        error_log("Failed to create or set permissions for WordPress directory: " . implode("\n", $output));
        echo json_encode(['success' => false, 'error' => 'Failed to create WordPress directory.']);
        exit;
    }
} else {
    // Ensure the directory is writable
    if (!is_writable($wordpressDir)) {
        exec("sudo chown -R www-data:www-data $wordpressDir && sudo chmod -R 755 $wordpressDir", $output, $return_var);
        if ($return_var !== 0) {
            error_log("Failed to set permissions for existing WordPress directory: " . implode("\n", $output));
            echo json_encode(['success' => false, 'error' => 'Failed to set permissions for WordPress directory.']);
            exit;
        }
    }
}

// Sanitize the site name to avoid invalid characters
$site_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $site_name);

// Create the site directory
$siteDir = $wordpressDir . '/' . $site_name;
if (is_dir($siteDir)) {
    // Check if reinstallation is requested
    if (isset($_POST['force_reinstall']) && $_POST['force_reinstall'] === 'true') {
        // Remove the existing directory
        $sanitizedSiteDir = escapeshellarg($siteDir);
        exec("sudo rm -rf $sanitizedSiteDir", $output, $return_var);
        if ($return_var !== 0) {
            error_log("Failed to remove existing site directory: " . implode("\n", $output));
            echo json_encode(['success' => false, 'error' => 'Failed to remove existing site directory.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'A site with this name already exists.']);
        exit;
    }
}

// Create the site directory
$sanitizedSiteDir = escapeshellarg($siteDir);
exec("sudo mkdir -p $sanitizedSiteDir && sudo chown -R www-data:www-data $sanitizedSiteDir && sudo chmod -R 755 $sanitizedSiteDir", $output, $return_var);
if ($return_var !== 0) {
    error_log("Failed to create site directory: " . implode("\n", $output));
    echo json_encode(['success' => false, 'error' => 'Failed to create site directory.']);
    exit;
}

// Step 1: Download and Configure WordPress
exec("cd $sanitizedSiteDir && sudo wget -q https://wordpress.org/latest.tar.gz && sudo tar -xzf latest.tar.gz --strip-components=1 && sudo rm latest.tar.gz", $output, $return_var);
if ($return_var !== 0) {
    error_log("Failed to download and configure WordPress: " . implode("\n", $output));
    echo json_encode(['success' => false, 'error' => 'Failed to download and configure WordPress.']);
    exit;
}

// Step 2: Set Permissions
exec("sudo chown -R www-data:www-data $sanitizedSiteDir && sudo find $sanitizedSiteDir -type d -exec chmod 755 {} \\; && sudo find $sanitizedSiteDir -type f -exec chmod 644 {} \\;", $output, $return_var);
if ($return_var !== 0) {
    error_log("Failed to set permissions for WordPress files: " . implode("\n", $output));
    echo json_encode(['success' => false, 'error' => 'Failed to set permissions for WordPress files.']);
    exit;
}

// Step 3: Configure Nginx
$nginxConfigPath = "/etc/nginx/sites-available/$domain";
if (!file_exists($nginxConfigPath)) {
    $nginxConfig = <<<NGINX
server {
    listen 80;
    server_name $domain;
    root $siteDir;
    index index.php index.html index.htm;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX;

    exec("echo \"$nginxConfig\" | sudo tee $nginxConfigPath > /dev/null", $output, $return_var);
    if ($return_var !== 0) {
        error_log("Failed to create Nginx configuration: " . implode("\n", $output));
        echo json_encode(['success' => false, 'error' => 'Failed to create Nginx configuration.']);
        exit;
    }
    exec("sudo ln -sf $nginxConfigPath /etc/nginx/sites-enabled/ && sudo nginx -t && sudo systemctl reload nginx", $output, $return_var);
    if ($return_var !== 0) {
        error_log("Failed to reload Nginx: " . implode("\n", $output));
        echo json_encode(['success' => false, 'error' => 'Failed to reload Nginx.']);
        exit;
    }
}

// Step 4: Install WP-CLI and Configure WordPress
exec("which wp", $output, $return_var);
if ($return_var !== 0) {
    exec("curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x wp-cli.phar && sudo mv wp-cli.phar /usr/local/bin/wp", $output, $return_var);
    if ($return_var !== 0) {
        error_log("Failed to install WP-CLI: " . implode("\n", $output));
        echo json_encode(['success' => false, 'error' => 'Failed to install WP-CLI.']);
        exit;
    }
}

exec("cd $sanitizedSiteDir && sudo -u www-data wp core config --dbname=$db_name --dbuser=$db_user --dbpass=$db_password --dbhost=localhost --dbprefix=wp_", $output, $return_var);
if ($return_var !== 0) {
    error_log("Failed to configure WordPress: " . implode("\n", $output));
    echo json_encode(['success' => false, 'error' => 'Failed to configure WordPress.']);
    exit;
}

exec("cd $sanitizedSiteDir && sudo -u www-data wp core install --url=http://$domain --title=\"$site_name\" --admin_user=$wp_username --admin_password=$wp_password --admin_email=$wp_email", $output, $return_var);
if ($return_var !== 0) {
    error_log("Failed to install WordPress: " . implode("\n", $output));
    echo json_encode(['success' => false, 'error' => 'Failed to install WordPress.']);
    exit;
}

// Save the site details in the database
$stmt = $pdo->prepare("INSERT INTO wordpress_sites (user_id, domain, site_name, db_name, db_user, db_password, wp_username, wp_password, wp_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $domain, $site_name, $db_name, $db_user, $db_password, $wp_username, $wp_password, $wp_email]);

echo json_encode(['success' => true, 'message' => 'WordPress site created successfully.']);
?>