<?php
require_once '../database/db.php';

/**
 * Validate the username format.
 *
 * @param string $username
 * @return bool
 */
function validateUsername(string $username): bool {
    return preg_match('/^[a-z_][a-z0-9_-]*$/', $username);
}

/**
 * Hash the password securely.
 *
 * @param string $password
 * @return string
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Save user details to the database.
 *
 * @param PDO $pdo
 * @param string $username
 * @param string $hashed_password
 * @param string $email
 * @return bool
 */
function saveUserToDatabase(PDO $pdo, string $username, string $hashed_password, string $email): bool {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $hashed_password, $email]);
}

// Main logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    // Validate input
    if (!validateUsername($username)) {
        die('Invalid username format.');
    }

    if (empty($password) || empty($email)) {
        die('Password and email are required.');
    }

    // Hash the password
    $hashed_password = hashPassword($password);

    // Save user to the database
    if (!saveUserToDatabase($pdo, $username, $hashed_password, $email)) {
        die('Failed to save user to the database.');
    }

    // Start a session and redirect to the dashboard
    session_start();
    $_SESSION['user_id'] = $pdo->lastInsertId();
    header('Location: /dashboard/dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Register</h3>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small>Already have an account? <a href="login.php">Login</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>