<?php
$pageTitle = "Login";
require 'header.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $email = trim($_POST['email'] ?? '');
  $name  = trim($_POST['name'] ?? '');
  if ($email === '' || $name === '') {
    $error = "Please fill in all fields.";
  } else {
    // Simple demo auth: ensure a user exists or create a minimal one
    $stmt = $conn->prepare("SELECT id FROM users WHERE name = ? LIMIT 1");
    $stmt->execute([$name]);
    $uid = $stmt->fetchColumn();
    if (!$uid) {
      // create a simple user (demo)
      $ins = $conn->prepare("INSERT INTO users (name, age, photo, distance_km, bio) VALUES (?, 25, 'img/1.jpg', 1, 'New user')");
      $ins->execute([$name]);
      $uid = (int)$conn->lastInsertId();
    }
   // after successful login / user creation:
$_SESSION['user_id'] = (int)$uid;

// ✅ reset feed pointer so Home starts from the first unseen profile
$_SESSION['last_seen_id'] = 0;

// ✅ (optional but recommended) new CSRF token for this session
$_SESSION['csrf'] = bin2hex(random_bytes(16));

// ✅ (optional) harden session
session_regenerate_id(true);

header('Location: index.php');
exit;

  }
}
?>
<div class="container py-5">
  <h2 class="mb-4">Login</h2>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
  <?php endif; ?>
  <form method="post" id="loginForm" novalidate>
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input name="name" class="form-control" required>
      <div class="invalid-feedback">Name is required.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input name="email" type="email" class="form-control" required>
      <div class="invalid-feedback">Valid email required.</div>
    </div>
    <button class="btn btn-danger">Login</button>
  </form>
</div>
<script>
document.getElementById('loginForm').addEventListener('submit', (e)=>{
  const f=e.target;
  if(!f.checkValidity()){ e.preventDefault(); f.classList.add('was-validated'); }
},{capture:true});
</script>
<?php require 'footer.php'; ?>
