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
                            <a href="/user/index.html" class="btn btn-primary">Manage Files</a>
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