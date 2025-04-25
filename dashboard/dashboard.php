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

// Directory to store WordPress sites
$wordpressDir = "/home/users/" . $user['linux_username'] . "/wordpress_sites";

// Ensure the directory exists
if (!is_dir($wordpressDir)) {
    mkdir($wordpressDir, 0755, true);
}

// Count the number of WordPress installations
$wordpressSites = array_filter(glob($wordpressDir . '/*'), 'is_dir');
$totalSites = count($wordpressSites);
$maxSites = 5;
$remainingSites = $maxSites - $totalSites;
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
                            <a href="/user/index.php" class="btn btn-primary">Manage Files</a>
                            <hr>
                            <h6>WordPress Sites</h6>
                            <p>Total Sites Created: <?= $totalSites ?>/<?= $maxSites ?></p>
                            <?php if ($remainingSites > 0): ?>
                                <button class="btn btn-warning" onclick="showCreateSiteModal()">Create New Site</button>
                            <?php else: ?>
                                <p class="text-danger">You have reached the maximum number of WordPress sites.</p>
                            <?php endif; ?>
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

    <!-- Modal for creating a new WordPress site -->
    <div class="modal" id="createSiteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New WordPress Site</h5>
                    <button type="button" class="btn-close" onclick="hideCreateSiteModal()"></button>
                </div>
                <div class="modal-body">
                    <form id="createSiteForm">
                        <div class="mb-3">
                            <label for="domain" class="form-label">Domain Name</label>
                            <input type="text" id="domain" name="domain" class="form-control" placeholder="example.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="wp_username" class="form-label">WordPress Username</label>
                            <input type="text" id="wp_username" name="wp_username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="wp_password" class="form-label">WordPress Password</label>
                            <input type="password" id="wp_password" name="wp_password" class="form-control" required>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="createSite()">Create Site</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showCreateSiteModal() {
            document.getElementById('createSiteModal').style.display = 'block';
        }

        function hideCreateSiteModal() {
            document.getElementById('createSiteModal').style.display = 'none';
        }

        function createSite() {
            const form = document.getElementById('createSiteForm');
            const formData = new FormData(form);

            fetch('install_wp.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        alert(response.message || "WordPress site created successfully!");
                        hideCreateSiteModal();
                        location.reload();
                    } else {
                        alert(response.error || "Failed to create WordPress site.");
                    }
                })
                .catch(err => alert("Error creating WordPress site"));
        }
    </script>
</body>
</html>