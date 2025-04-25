<?php
require_once '../database/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Directory to store WordPress sites
$wordpressDir = "/home/users/" . $user['linux_username'] . "/wordpress_sites";

// Ensure the directory exists
if (!is_dir($wordpressDir)) {
    mkdir($wordpressDir, 0755, true);
}

// Count the number of WordPress installations
$wordpressSites = array_filter(glob($wordpressDir . '/*'), 'is_dir');
$totalSites = count($wordpressSites);
$maxSites = 5;
$remainingSites = $maxSites - $totalSites;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Dashboard</h3>
                    </div>
                    <div class="card-body">
                        <h5>Welcome, <?= htmlspecialchars($user['username']) ?></h5>
                        <?php if ($user['linux_username']): ?>
                            <p class="text-success">Hosting Account: Created</p>
                            <p>Allocated Space: <?= $user['allocated_space'] / 1024 ?> MB</p>
                            <a href="/user/index.php" class="btn btn-primary">Manage Files</a>
                            <hr>
                            <h6>WordPress Sites</h6>
                            <p>Total Sites Created: <?= $totalSites ?>/<?= $maxSites ?></p>
                            <?php if ($remainingSites > 0): ?>
                                <a href="install_wp_ui.php" class="btn btn-warning">Create New Site</a>
                            <?php else: ?>
                                <p class="text-danger">You have reached the maximum number of WordPress sites.</p>
                            <?php endif; ?>
                            <hr>
                            <h6>Existing WordPress Sites</h6>
                            <?php if ($totalSites > 0): ?>
                                <ul class="list-group">
                                    <?php foreach ($wordpressSites as $site): ?>
                                        <li class="list-group-item">
                                            <?= htmlspecialchars(basename($site)) ?>
                                            <a href="http://<?= htmlspecialchars(basename($site)) ?>" class="btn btn-sm btn-primary float-end" target="_blank">Visit Site</a>
                                            <a href="http://<?= htmlspecialchars(basename($site)) ?>/wp-admin" class="btn btn-sm btn-warning float-end me-2" target="_blank">Admin</a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No WordPress sites created yet.</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-danger">Hosting Account: Not Created</p>
                            <a href="create_hosting.php" class="btn btn-success">Create Hosting Account</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="/auth/logout.php" class="btn btn-danger">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>