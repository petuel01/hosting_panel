
<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>LXD Hosting - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
<div class="container py-5">
    <h2 class="text-center mb-4">Login to Your Hosting Panel</h2>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <form method="post" action="login.php">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Login</button>
                <div class="text-center mt-3">
                    <a href="register.php" class="text-light">Create an account</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
