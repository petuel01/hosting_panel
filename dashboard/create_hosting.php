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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allocated_space = 2000000; // Default 2GB in KB
    $linux_username = $user['username'];
    $password = bin2hex(random_bytes(8)); // Generate a random password
    $user_dir_base = "/home/users";
    $user_dir = "$user_dir_base/$linux_username";
    $log_file = __DIR__ . '/create_hosting.log'; // Log file in the same directory as the script

    // Validate username format
    if (!preg_match('/^[a-z_][a-z0-9_-]*$/', $linux_username)) {
        echo json_encode(['success' => false, 'error' => 'Invalid username format.']);
        exit;
    }

    // Check if the Linux user already exists
    $check_user_cmd = "id -u $linux_username > /dev/null 2>&1";
    exec($check_user_cmd, $output, $return_var);
    if ($return_var === 0) {
        $user_exists = true;
    } else {
        $user_exists = false;
    }

    // Create the Linux user if it doesn't exist
    if (!$user_exists) {
        $create_user_cmd = "sudo useradd -m -d $user_dir -s /bin/bash $linux_username";
        exec($create_user_cmd . " 2>&1", $output, $return_var);

        // Log the output for debugging
        file_put_contents($log_file, "Command: $create_user_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

        if ($return_var !== 0) {
            echo json_encode(['success' => false, 'error' => 'Failed to create Linux user.']);
            exit;
        }
    }

    // Set the user's password
    $set_password_cmd = "echo '$linux_username:$password' | sudo chpasswd";
    exec($set_password_cmd . " 2>&1", $output, $return_var);

    // Log the output for debugging
    file_put_contents($log_file, "Command: $set_password_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to set user password. Check logs for details.']);
        exit;
    }

    // Create the user's directory if it doesn't exist
    if (!is_dir($user_dir)) {
        if (!mkdir($user_dir, 0755, true)) {
            echo json_encode(['success' => false, 'error' => 'Failed to create user directory.']);
            exit;
        }
    }

    // Set ownership and permissions for the directory
    $chown_cmd = "sudo chown $linux_username:$linux_username $user_dir";
    exec($chown_cmd . " 2>&1", $output, $return_var);

    // Log the output for debugging
    file_put_contents($log_file, "Command: $chown_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to set ownership for user directory. Check logs for details.']);
        exit;
    }

    $chmod_cmd = "sudo chmod 755 $user_dir";
    exec($chmod_cmd . " 2>&1", $output, $return_var);

    // Log the output for debugging
    file_put_contents($log_file, "Command: $chmod_cmd\nOutput: " . implode("\n", $output) . "\nReturn Code: $return_var\n", FILE_APPEND);

    if ($return_var !== 0) {
        echo json_encode(['success' => false, 'error' => 'Failed to set permissions for user directory. Check logs for details.']);
        exit;
    }

    // Update the database with Linux username and allocated space
    $stmt = $pdo->prepare("UPDATE users SET linux_username = ?, allocated_space = ? WHERE id = ?");
    $stmt->execute([$linux_username, $allocated_space, $user['id']]);

    // Return the generated password to the frontend
    echo json_encode(['success' => true, 'password' => $password]);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Hosting Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function createHostingAccount() {
            const progressBar = document.getElementById('progress-bar');
            progressBar.style.width = '0%';
            progressBar.innerText = '0%';

            fetch('create_hosting.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    progressBar.style.width = '100%';
                    progressBar.innerText = '100%';
                    document.getElementById('password-display').innerText = `Generated Password: ${data.password}`;
                    document.getElementById('continue-button').style.display = 'block';
                    document.getElementById('file-manager-button').style.display = 'block';
                } else {
                    alert(data.error || 'An error occurred.');
                }
            })
            .catch(error => {
                alert('An error occurred: ' + error.message);
            });
        }
    </script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Create Hosting Account</h3>
                    </div>
                    <div class="card-body">
                        <div style="width: 100%; background-color: #f3f3f3; border: 1px solid #ccc;">
                            <div id="progress-bar" style="width: 0%; height: 30px; background-color: #4caf50; text-align: center; color: white;">0%</div>
                        </div>
                        <button class="btn btn-success mt-3 w-100" onclick="createHostingAccount()">Create Hosting Account</button>
                        <p id="password-display" class="mt-3 text-center text-danger"></p>
                        <a href="/wordpress_install.php" id="continue-button" class="btn btn-primary mt-3 w-100" style="display: none;">Continue to WordPress Installation</a>
                        <a href="/user/index.html" id="file-manager-button" class="btn btn-secondary mt-3 w-100" style="display: none;">Open File Manager</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>