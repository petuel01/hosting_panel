
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
<div class="container py-5">
    <h2 class="text-center">Welcome, <?= $user['username'] ?></h2>
    <p class="text-center">Container: <?= $user['container_name'] ?></p>
    <div class="text-center mt-4">
        <form action="install_wp.php" method="post">
            <input type="hidden" name="container" value="<?= $user['container_name'] ?>">
            <button class="btn btn-warning">Install WordPress</button>
        </form>
    </div>
    <div class="text-center mt-3">
        <a href="logout.php" class="btn btn-secondary">Logout</a>
    </div>
</div>
</body>
</html>
