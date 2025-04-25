<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Install WordPress</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Install WordPress</h3>
                    </div>
                    <div class="card-body">
                        <form id="installWpForm" method="POST" action="install_wp_process.php">
                            <div class="mb-3">
                                <label for="domain" class="form-label">Domain or Subdomain</label>
                                <input type="text" id="domain" name="domain" class="form-control" placeholder="example.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="site_name" class="form-label">Site Name</label>
                                <input type="text" id="site_name" name="site_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="wp_username" class="form-label">WordPress Username</label>
                                <input type="text" id="wp_username" name="wp_username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="wp_password" class="form-label">WordPress Password</label>
                                <input type="password" id="wp_password" name="wp_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="wp_email" class="form-label">WordPress Email</label>
                                <input type="email" id="wp_email" name="wp_email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <input type="checkbox" id="force_reinstall" name="force_reinstall" value="true">
                                <label for="force_reinstall" class="form-label">Force Reinstallation (overwrite existing site)</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Proceed with Installation</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>