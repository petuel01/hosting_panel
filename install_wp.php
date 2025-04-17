<?php
require 'config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

try {
    // Fetch the user's container name
    $stmt = $pdo->prepare("SELECT container_name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found.");
    }

    $container_name = escapeshellarg($user['container_name']);
    $port = rand(8000, 9000); // Assign a random port

    // Install Nginx, PHP, and MySQL in the container
    shell_exec("lxc exec $container_name -- apt-get update");
    shell_exec("lxc exec $container_name -- apt-get install -y nginx php-fpm php-mysql mysql-client");

    // Download and configure WordPress
    shell_exec("lxc exec $container_name -- wget https://wordpress.org/latest.tar.gz -P /var/www/html");
    shell_exec("lxc exec $container_name -- tar -xzf /var/www/html/latest.tar.gz -C /var/www/html");
    shell_exec("lxc exec $container_name -- rm /var/www/html/latest.tar.gz");

    // Configure Nginx for WordPress
    $nginx_config = "
server {
    listen $port;
    server_name localhost;

    root /var/www/html/wordpress;
    index index.php index.html index.htm;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock; # Adjust PHP version
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
";
    file_put_contents("/etc/nginx/sites-available/$container_name", $nginx_config);
    shell_exec("lxc file push /etc/nginx/sites-available/$container_name $container_name/etc/nginx/sites-available/wordpress");
    shell_exec("lxc exec $container_name -- ln -s /etc/nginx/sites-available/wordpress /etc/nginx/sites-enabled/");
    shell_exec("lxc exec $container_name -- nginx -s reload");

    // Expose the container's port
    shell_exec("lxc config device add $container_name http proxy listen=tcp:0.0.0.0:$port connect=tcp:127.0.0.1:80");

    echo "WordPress installed successfully! Access it at http://<server-ip>:$port";
} catch (Exception $e) {
    error_log("Error during WordPress installation: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>