<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address.");
    }

    $password = $_POST['password'];
    if (strlen($password) < 8) {
        die("Password must be at least 8 characters long.");
    }

    // Generate a unique container name
    $container_name = 'container_' . uniqid();

    try {
        // Insert user into the database
        $stmt = $pdo->prepare("INSERT INTO users (email, password, container_name) VALUES (?, ?, ?)");
        $stmt->execute([$email, password_hash($password, PASSWORD_BCRYPT), $container_name]);

        // Create the LXC container
        $container_name_safe = escapeshellarg($container_name);
        shell_exec("lxc launch ubuntu:20.04 $container_name_safe");

        echo "Registration successful! Your container is being created.";
    } catch (Exception $e) {
        error_log("Error during registration: " . $e->getMessage());
        die("An error occurred. Please try again later.");
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
