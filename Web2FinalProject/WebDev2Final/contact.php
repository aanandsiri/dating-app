<?php
$pageTitle = "Contact";
require 'header.php';
require 'db.php';

// Ensure messages table exists
$conn->exec("CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL
)");

if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
  header('Content-Type: application/json; charset=utf-8');
  $name = trim($_POST['name'] ?? '');
  $email= trim($_POST['email'] ?? '');
  $msg  = trim($_POST['message'] ?? '');
  if ($name==='' || $email==='' || $msg==='') {
    http_response_code(400);
    echo json_encode(['ok'=>false,'message'=>'All fields are required']);
    exit;
  }
  $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
  $ins = $conn->prepare("INSERT INTO messages (user_id,name,email,message,created_at) VALUES (?,?,?,?,NOW())");
  $ins->execute([$uid,$name,$email,$msg]);
  echo json_encode(['ok'=>true,'message'=>'Thanks! Your message has been sent.']);
  exit;
}
?>
<div class="container py-5">
  <h2 class="mb-4">Contact Us</h2>
  <form id="contactForm" class="needs-validation" novalidate>
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input name="name" class="form-control" required>
      <div class="invalid-feedback">Name is required.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input name="email" type="email" class="form-control" required>
      <div class="invalid-feedback">Valid email is required.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Message</label>
      <textarea name="message" class="form-control" rows="4" required></textarea>
      <div class="invalid-feedback">Message cannot be empty.</div>
    </div>
    <button class="btn btn-danger" type="submit">Send</button>
    <div id="contactAlert" class="mt-3"></div>
  </form>
</div>
<?php require 'footer.php'; ?>
