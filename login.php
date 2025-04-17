<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        // Fetch user from the database
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Start session and regenerate session ID
            session_start();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];

            echo "Login successful!";
        } else {
            die("Invalid email or password.");
        }
    } catch (Exception $e) {
        error_log("Error during login: " . $e->getMessage());
        die("An error occurred. Please try again later.");
    }
}
?>