<?php
// register.php – create account with required fields + photo upload
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'db.php';
$pageTitle = "Create Account";
include 'header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Collect & trim
  $name  = trim($_POST['name'] ?? '');
  $age   = isset($_POST['age']) ? (int)$_POST['age'] : 0;
  $dist  = isset($_POST['distance_km']) ? (int)$_POST['distance_km'] : 0;
  $bio   = trim($_POST['bio'] ?? '');

  // Validate required fields
  if ($name === '') $errors[] = 'Name is required';
  if ($age < 18 || $age > 99) $errors[] = 'Age must be between 18 and 99';
  if ($bio === '') $errors[] = 'Bio is required';

  // Validate photo upload
  if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Profile photo is required';
  } else {
    $f        = $_FILES['photo'];
    $sizeOk   = ($f['size'] <= 3 * 1024 * 1024); // 3MB
    $ext      = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $extOk    = in_array($ext, ['jpg', 'jpeg', 'png'], true);
    $mime     = mime_content_type($f['tmp_name']) ?: '';
    $mimeOk   = in_array($mime, ['image/jpeg', 'image/png'], true);

    if (!$sizeOk) $errors[] = 'Photo must be ≤ 3MB';
    if (!$extOk || !$mimeOk) $errors[] = 'Photo must be JPG or PNG';
  }

  if (!$errors) {
    // Save photo to /uploads with unique name
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $newName = 'u_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target  = $uploadDir . '/' . $newName;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
      $errors[] = 'Failed to save uploaded photo';
    } else {
      // Store relative path for <img src>
      $photoPath = 'uploads/' . $newName;

      // Insert user
      $stmt = $conn->prepare("
        INSERT INTO users (name, age, photo, distance_km, bio)
        VALUES (?, ?, ?, ?, ?)
      ");
      $stmt->execute([$name, $age, $photoPath, $dist, $bio]);

      $_SESSION['user_id'] = (int)$conn->lastInsertId();
      $success = true;
      header('Location: index.php');
      exit;
    }
  }
}
?>

<div class="container py-5">
  <h2 class="mb-4">Create Your Account</h2>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?php echo htmlspecialchars($e, ENT_QUOTES); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" id="regForm" class="needs-validation" novalidate>
    <div class="mb-3">
      <label class="form-label">Name*</label>
      <input name="name" class="form-control" required>
      <div class="invalid-feedback">Please enter your name</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Age* (18–99)</label>
      <input name="age" type="number" min="18" max="99" class="form-control" required>
      <div class="invalid-feedback">Please enter a valid age (18–99)</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Distance (km)</label>
      <input name="distance_km" type="number" min="0" class="form-control" value="5">
    </div>
    <div class="mb-3">
      <label class="form-label">Short bio*</label>
      <textarea name="bio" class="form-control" rows="3" maxlength="255" required></textarea>
      <div class="invalid-feedback">Please add a short bio</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Profile photo* (JPG/PNG, ≤ 3MB)</label>
      <input name="photo" type="file" accept=".jpg,.jpeg,.png" class="form-control" required>
      <div class="invalid-feedback">Please upload a JPG/PNG photo</div>
    </div>

    <button class="btn btn-danger">Create Account</button>
  </form>
</div>

<script>
document.getElementById('regForm').addEventListener('submit', (e)=>{
  const f=e.target;
  if (!f.checkValidity()) { e.preventDefault(); f.classList.add('was-validated'); }
}, {capture:true});
</script>

<?php include 'footer.php'; ?>
