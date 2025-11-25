<?php
// db.php
declare(strict_types=1);

$host = '127.0.0.1';
$port = '3307';
$db   = 'mytestdb';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("DB connection error");
}
