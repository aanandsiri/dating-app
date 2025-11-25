<?php
// logout.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Optional: unset specific keys (not strictly necessary if you destroy)
unset($_SESSION['user_id'], $_SESSION['last_seen_id'], $_SESSION['csrf']);

// Destroy session completely
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();

// Redirect to login (or register)
header('Location: login.php');
exit;
