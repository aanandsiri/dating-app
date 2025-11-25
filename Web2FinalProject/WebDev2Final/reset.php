<?php
// reset.php — robust demo reset with FK-safe truncates and optional debug
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

// -------- Settings --------
$ALLOW_DEBUG_GET = true; // set false before submission
$debug = $ALLOW_DEBUG_GET && isset($_GET['debug']);

// POST-only + CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$debug) {
    http_response_code(405);
    exit('Method not allowed.');
}
if (!$debug) {
    $csrf = $_POST['csrf'] ?? '';
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
        http_response_code(403);
        exit('CSRF validation failed.');
    }
}

// Small helpers
function tableExists(PDO $conn, string $name): bool {
    $q = $conn->prepare("SHOW TABLES LIKE ?");
    $q->execute([$name]);
    return (bool)$q->fetchColumn();
}
function tryExec(PDO $conn, string $sql): void {
    $conn->exec($sql);
}

try {
    if ($debug) { ini_set('display_errors', '1'); error_reporting(E_ALL); }

    // Start TX
    $conn->beginTransaction();

    // Disable FK checks to avoid constraint issues during truncate/delete
    tryExec($conn, "SET FOREIGN_KEY_CHECKS=0");

    // Gracefully clear child tables if they exist
    if (tableExists($conn, 'likes'))    { tryExec($conn, "TRUNCATE TABLE likes"); }
    if (tableExists($conn, 'dislikes')) { tryExec($conn, "TRUNCATE TABLE dislikes"); }
    if (tableExists($conn, 'matches'))  { tryExec($conn, "TRUNCATE TABLE matches"); }
    if (tableExists($conn, 'messages')) { tryExec($conn, "TRUNCATE TABLE messages"); } // optional

    // Clear users (TRUNCATE resets AUTO_INCREMENT automatically)
    if (tableExists($conn, 'users')) {
        tryExec($conn, "TRUNCATE TABLE users");
    } else {
        // Create users table if missing (saves you from crashes)
        tryExec($conn, "
            CREATE TABLE users (
              id INT AUTO_INCREMENT PRIMARY KEY,
              name VARCHAR(100) NOT NULL,
              age INT NULL,
              photo VARCHAR(255) DEFAULT NULL,
              distance_km INT DEFAULT 0,
              bio VARCHAR(255) DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // Reseed demo users
    $seed = $conn->prepare("
        INSERT INTO users (name, age, photo, distance_km, bio) VALUES
        ('Amy', 25, 'img/1.jpg', 2, 'Love Dogs'),
        ('Susan', 27, 'img/2.jpg', 3, 'Finding someone who can go on library dates with me'),
        ('Shizuka', 24, 'img/3.jpg', 5, 'Chili-pot date'),
        ('Diana', 26, 'img/4.jpg', 7, 'Just looking for someone fun'),
        ('Eve', 22, 'img/5.jpg', 8, 'Surprise me'),
        ('Alexandra', 29, 'img/6.jpg', 4, 'Too lazy to go out'),
        ('Grace', 23, 'img/8.jpg', 6, ':)'),
        ('Hank', 30, 'img/7.jpg', 9, 'Focusing on myself')
    ");
    $seed->execute();

    // Re-enable FK checks
    tryExec($conn, "SET FOREIGN_KEY_CHECKS=1");

    // Commit
    $conn->commit();

    // Demo session user
    $_SESSION['user_id'] = 1;

    if ($debug) {
        echo "Reset OK (debug mode).";
    } else {
        header('Location: index.php');
    }
    exit;

} catch (PDOException $e) {
    if ($conn->inTransaction()) { $conn->rollBack(); }
    // Re-enable FK checks just in case
    try { tryExec($conn, "SET FOREIGN_KEY_CHECKS=1"); } catch (\Throwable $ignore) {}

    http_response_code(500);
    if ($debug) {
        // Show exact error while debugging
        exit('Database error during reset: ' . $e->getMessage());
    }
    exit('Database error during reset.');
}
