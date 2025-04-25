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
    $password = $_POST['password']; // Use the same password as the user login

    // Call the `user.sh` script
    $script_path = realpath('../user.sh');
    $command = escapeshellcmd("sudo bash $script_path $linux_username $password $allocated_space");
    exec($command, $output, $return_var);

    if ($return_var === 0) {
        // Update the database with Linux username and allocated space
        $stmt = $pdo->prepare("UPDATE users SET linux_username = ?, allocated_space = ? WHERE id = ?");
        $stmt->execute([$linux_username, $allocated_space, $user['id']]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create hosting account.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Hosting Account</title>
    <script>
        function createHostingAccount() {
            const progressBar = document.getElementById('progress-bar');
            progressBar.style.width = '0%';
            progressBar.innerText = '0%';

            fetch('create_hosting.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password: '<?= $user['password'] ?>' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    progressBar.style.width = '100%';
                    progressBar.innerText = '100%';
                    alert('Hosting account created successfully!');
                    window.location.href = '/dashboard.php';
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
<body>
    <h1>Create Hosting Account</h1>
    <div style="width: 100%; background-color: #f3f3f3; border: 1px solid #ccc;">
        <div id="progress-bar" style="width: 0%; height: 30px; background-color: #4caf50; text-align: center; color: white;">0%</div>
    </div>
    <button onclick="createHostingAccount()">Create Hosting Account</button>
</body>
</html>