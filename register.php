<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php'; // Ensure this file defines and initializes $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $username = htmlspecialchars(trim($_POST['username'])); // Sanitize username
    if (empty($username)) {
        die("Username is required.");
    }

    $password = $_POST['password'];
    if (strlen($password) < 8) {
        die("Password must be at least 8 characters long.");
    }

    // Generate a unique container name
    $container_name = 'container_' . uniqid();

    try {
        // Ensure $pdo is defined and connected
        if (!isset($pdo)) {
            throw new Exception("Database connection is not initialized.");
        }

        // Check if the Ubuntu image is available
        $image_check_output = shell_exec("lxc image list ubuntu:22.04 --format=json 2>&1");
        if (strpos($image_check_output, 'not found') !== false) {
            throw new Exception("The specified Ubuntu image (ubuntu:22.04) is not available on the server.");
        }

        // Insert user into the database
        $stmt = $pdo->prepare("INSERT INTO users (username, password, container_name) VALUES (?, ?, ?)");
        $stmt->execute([$username, password_hash($password, PASSWORD_BCRYPT), $container_name]);

        // Create a 2GB storage volume for the container
        $volume_name = $container_name . "_volume";
        $volume_output = shell_exec("lxc storage volume create default $volume_name size=2GB 2>&1");
        if (strpos($volume_output, 'Error') !== false) {
            throw new Exception("Failed to create a 2GB storage volume for the container: $volume_output");
        }

        // Launch the LXC container with the created volume
        $container_name_safe = escapeshellarg($container_name);
        $launch_output = shell_exec("lxc launch ubuntu:20.04 $container_name_safe -s default -d 2>&1");

        if (strpos($launch_output, 'Error') !== false) {
            throw new Exception("Failed to launch the LXC container: $launch_output");
        }

        echo "Registration successful! Your container is being created with a 2GB volume.";
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - LXD Hosting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
<div class="container py-5">
    <h2 class="text-center mb-4">Create Hosting Account</h2>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-success w-100">Register</button>
                <div class="text-center mt-3">
                    <a href="index.php" class="text-light">Already have an account?</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>