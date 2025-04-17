
<?php
$host = 'localhost';
$db = 'hosting_panel';
$user = 'root';
$pass = 'Petzeus@123';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
