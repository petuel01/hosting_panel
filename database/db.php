<?php
$host = 'localhost';
$dbname = 'lxd_hosting';
$user = 'root'; // Replace with your MySQL username
$password = 'Petzeus@123'; // Replace with your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>