
<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $container = $_POST['container'];
    $port = rand(8000, 9000);

    shell_exec("lxc exec $container -- apt update");
    shell_exec("lxc exec $container -- apt install apache2 mysql-server php php-mysql libapache2-mod-php -y");
    shell_exec("lxc exec $container -- systemctl enable apache2");
    shell_exec("lxc exec $container -- systemctl start apache2");
    shell_exec("lxc exec $container -- bash -c 'cd /var/www/html && curl -O https://wordpress.org/latest.tar.gz'");
    shell_exec("lxc exec $container -- bash -c 'cd /var/www/html && tar -xvzf latest.tar.gz && mv wordpress/* .'");
    shell_exec("lxc config device add $container http proxy listen=tcp:0.0.0.0:$port connect=tcp:127.0.0.1:80");

    echo "<script>alert('WordPress installed. Access it at your-server-ip:$port'); window.location.href='dashboard.php';</script>";
}
?>
