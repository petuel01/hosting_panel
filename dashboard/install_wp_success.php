<?php
session_start();

$domain = $_GET['domain'];
$wp_username = $_GET['wp_username'];
$wp_password = $_GET['wp_password'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>WordPress Installed</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h3>WordPress Installed Successfully</h3>
                    </div>
                    <div class="card-body text-center">
                        <p>Your WordPress site has been installed successfully!</p>
                        <a href="https://<?= htmlspecialchars($domain) ?>" class="btn btn-primary">Visit Site</a>
                        <a href="https://<?= htmlspecialchars($domain) ?>/wp-admin" class="btn btn-warning">Go to Admin</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>