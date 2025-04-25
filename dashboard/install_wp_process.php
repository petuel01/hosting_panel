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
$db_name = $_POST['db_name'];
$db_user = $_POST['db_user'];
$db_password = $_POST['db_password'];
$wp_username = $_POST['wp_username'];
$wp_password = $_POST['wp_password'];
$wp_email = $_POST['wp_email'];

// Directory for the WordPress site
$wordpressDir = "/home/users/" . $_SESSION['linux_username'] . "/$site_name";
if (!is_dir($wordpressDir)) {
    mkdir($wordpressDir, 0755, true);
}

// Check if the site already exists
if (is_dir($wordpressDir)) {
    echo json_encode(['success' => false, 'error' => 'A site with this name already exists.']);
    exit;
}

// MySQL root password
$mysqlRootPassword = 'Petzeus@123';

// Step 1: Install Dependencies (Skip if already installed)
exec("dpkg -l | grep -E 'nginx|mysql-server|php-fpm|php-mysql'", $output, $return_var);
if ($return_var !== 0) {
    exec("sudo apt update && sudo apt install -y nginx mysql-server php-fpm php-mysql php-curl php-gd php-mbstring php-xml php-zip unzip curl wget", $output, $return_var);
    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to install dependencies.']);
        exit;
    }
}

// Step 2: Setup MySQL (Skip if database already exists)
exec("sudo mysql -u root -p$mysqlRootPassword -e 'SHOW DATABASES LIKE \"$db_name\";'", $output, $return_var);
if (empty($output)) {
    $mysqlCommands = <<<EOF
CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$db_user'@'localhost' IDENTIFIED BY '$db_password';
GRANT ALL PRIVILEGES ON `$db_name`.* TO '$db_user'@'localhost';
FLUSH PRIVILEGES;
EOF;

    exec("echo \"$mysqlCommands\" | sudo mysql -u root -p$mysqlRootPassword", $output, $return_var);
    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to set up MySQL database and user.']);
        exit;
    }
}

// Step 3: Download and Configure WordPress (Skip if already downloaded)
if (!is_dir($wordpressDir)) {
    exec("sudo mkdir -p $wordpressDir && cd $wordpressDir && sudo wget -q https://wordpress.org/latest.tar.gz && sudo tar -xzf latest.tar.gz --strip-components=1 && sudo rm latest.tar.gz", $output, $return_var);
    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to download and configure WordPress.']);
        exit;
    }
    exec("sudo chown -R www-data:www-data $wordpressDir && sudo find $wordpressDir -type d -exec chmod 755 {} \\; && sudo find $wordpressDir -type f -exec chmod 644 {} \\;", $output, $return_var);
    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to set permissions for WordPress files.']);
        exit;
    }
}

// Step 4: Configure Nginx (Skip if configuration already exists)
$nginxConfigPath = "/etc/nginx/sites-available/$domain";
if (!file_exists($nginxConfigPath)) {
    $nginxConfig = <<<NGINX
server {
    listen 80;
    server_name $domain;
    root $wordpressDir;
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
        echo json_encode(['success' => false, 'error' => 'Failed to create Nginx configuration.']);
        exit;
    }
    exec("sudo ln -sf $nginxConfigPath /etc/nginx/sites-enabled/ && sudo nginx -t && sudo systemctl reload nginx", $output, $return_var);
    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to reload Nginx.']);
        exit;
    }
}

// Step 5: Install WP-CLI and Configure WordPress (Skip if already configured)
exec("which wp", $output, $return_var);
if ($return_var !== 0) {
    exec("curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x wp-cli.phar && sudo mv wp-cli.phar /usr/local/bin/wp", $output, $return_var);
    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to install WP-CLI.']);
        exit;
    }
}

exec("cd $wordpressDir && sudo -u www-data wp core is-installed", $output, $return_var);
if ($return_var !== 0) {
    exec("cd $wordpressDir && sudo -u www-data wp core config --dbname=$db_name --dbuser=$db_user --dbpass=$db_password --dbhost=localhost --dbprefix=wp_", $output, $return_var);
    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to configure WordPress.']);
        exit;
    }

    exec("cd $wordpressDir && sudo -u www-data wp core install --url=http://$domain --title=\"$site_name\" --admin_user=$wp_username --admin_password=$wp_password --admin_email=$wp_email", $output, $return_var);
    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to install WordPress.']);
        exit;
    }
}

// Save the site details in the database
$stmt = $pdo->prepare("INSERT INTO wordpress_sites (user_id, domain, site_name, db_name, db_user, db_password, wp_username, wp_password, wp_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $domain, $site_name, $db_name, $db_user, $db_password, $wp_username, $wp_password, $wp_email]);

echo json_encode(['success' => true, 'message' => 'WordPress site created successfully.']);
?>