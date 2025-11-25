<?php
// reaction.php — final (uses CSRF, idempotent, JSON responses)
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

// For debugging during setup; set to '0' before submission
ini_set('display_errors','1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $data): void {
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(405, ['ok'=>false, 'message'=>'Method not allowed']);
}

$action    = $_POST['action']     ?? '';
$profileId = isset($_POST['profile_id']) ? (int)$_POST['profile_id'] : 0;
$csrf      = $_POST['csrf']       ?? '';

// Ensure a logged-in user (your app sets this in login/index)
if (empty($_SESSION['user_id'])) {
  respond(403, ['ok'=>false, 'message'=>'Not logged in']);
}
$userId = (int)$_SESSION['user_id'];

// CSRF check (token set in index.php as $_SESSION['csrf'])
if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
  respond(403, ['ok'=>false, 'message'=>'CSRF validation failed']);
}

// Validate inputs
if (!in_array($action, ['like','dislike'], true) || $profileId <= 0) {
  respond(400, ['ok'=>false, 'message'=>'Invalid input']);
}
if ($profileId === $userId) {
  respond(400, ['ok'=>false, 'message'=>'You cannot react to your own profile']);
}

// Ensure both users exist
$chk = $conn->prepare("SELECT COUNT(*) FROM users WHERE id IN (?, ?)");
$chk->execute([$userId, $profileId]);
if ((int)$chk->fetchColumn() !== 2) {
  respond(400, ['ok'=>false, 'message'=>'User or target profile does not exist']);
}

try {
  if ($action === 'like') {
    // Insert like (ignore duplicates), remove opposite state
    $conn->prepare("INSERT IGNORE INTO likes (liker_id, liked_id, liked_at) VALUES (?, ?, NOW())")
         ->execute([$userId, $profileId]);
    $conn->prepare("DELETE FROM dislikes WHERE disliker_id = ? AND disliked_id = ?")
         ->execute([$userId, $profileId]);

    // Check mutual like
    $mutual = $conn->prepare("SELECT 1 FROM likes WHERE liker_id = ? AND liked_id = ? LIMIT 1");
    $mutual->execute([$profileId, $userId]);
    $isMutual = (bool)$mutual->fetchColumn();

    if ($isMutual) {
      // Ensure matches table exists (safety)
      $conn->exec("CREATE TABLE IF NOT EXISTS matches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user1_id INT NOT NULL,
        user2_id INT NOT NULL,
        matched_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY ux_match_pair (user1_id, user2_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

      $a = min($userId, $profileId);
      $b = max($userId, $profileId);
      $conn->prepare("INSERT IGNORE INTO matches (user1_id, user2_id, matched_at) VALUES (?, ?, NOW())")
           ->execute([$a, $b]);
// After successful insert in reaction.php, before respond(...)
$_SESSION['last_seen_id'] = $profileId;

      respond(200, ['ok'=>true, 'message'=>"It's a match! 🎉", 'match'=>true]);
    }
// After successful insert in reaction.php, before respond(...)
$_SESSION['last_seen_id'] = $profileId;

    respond(200, ['ok'=>true, 'message'=>'Like saved', 'match'=>false]);
  }

  // Dislike flow
  $conn->prepare("INSERT IGNORE INTO dislikes (disliker_id, disliked_id, disliked_at) VALUES (?, ?, NOW())")
       ->execute([$userId, $profileId]);
  $conn->prepare("DELETE FROM likes WHERE liker_id = ? AND liked_id = ?")
       ->execute([$userId, $profileId]);
// After successful insert in reaction.php, before respond(...)
$_SESSION['last_seen_id'] = $profileId;

  respond(200, ['ok'=>true, 'message'=>'Dislike saved']);

} catch (PDOException $e) {
  // During development we return the SQL error to help fix quickly.
  // For production, log server-side and return a generic message.
  respond(500, ['ok'=>false, 'message'=>'DB error: '.$e->getMessage()]);
}
