<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
$user = $_SESSION['user'];

// Fetch the allocated space for the user's container
$container_name_safe = escapeshellarg($user['container_name']);
$volume_info = shell_exec("lxc storage volume show default {$container_name_safe}_volume 2>&1");

// Extract the allocated size from the volume information
$allocated_space = "Unknown"; // Default value if size is not found
if (strpos($volume_info, 'size:') !== false) {
    preg_match('/size:\s*(\S+)/', $volume_info, $matches);
    if (isset($matches[1])) {
        $allocated_space = $matches[1];
    }
}

// Fetch the used space for the user's container
$disk_usage_output = shell_exec("lxc exec {$user['container_name']} -- df -h / 2>&1");
$used_space = "Unknown"; // Default value if usage is not found
if (strpos($disk_usage_output, '/dev/') !== false) {
    preg_match('/\d+G/', $disk_usage_output, $matches);
    if (isset($matches[0])) {
        $used_space = $matches[0];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
<div class="container py-5">
    <h2 class="text-center">Welcome, <?= htmlspecialchars($user['username']) ?></h2>
    <p class="text-center">Container: <?= htmlspecialchars($user['container_name']) ?></p>
    <p class="text-center">Allocated Space: <?= htmlspecialchars($allocated_space) ?></p>
    <p class="text-center">Used Space: <?= htmlspecialchars($used_space) ?></p>
    <div class="text-center mt-4">
        <form action="install_wp.php" method="post">
            <input type="hidden" name="container" value="<?= htmlspecialchars($user['container_name']) ?>">
            <button class="btn btn-warning">Install WordPress</button>
        </form>
    </div>
    <div class="text-center mt-3">
        <a href="logout.php" class="btn btn-secondary">Logout</a>
    </div>
</div>
</body>
</html>