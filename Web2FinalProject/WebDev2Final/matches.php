<?php
$pageTitle = "Your Matches";
require 'header.php';
require 'db.php';

if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$uid = (int)$_SESSION['user_id'];

// Ensure matches table exists (safe guard)
$conn->exec("CREATE TABLE IF NOT EXISTS matches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user1_id INT NOT NULL,
  user2_id INT NOT NULL,
  matched_at DATETIME NOT NULL,
  UNIQUE KEY ux_match_pair (user1_id, user2_id)
)");

$stm = $conn->prepare("
  SELECT u.id,u.name,u.age,u.photo,u.distance_km,u.bio,m.matched_at
  FROM matches m
  JOIN users u ON (u.id = CASE WHEN m.user1_id = :u THEN m.user2_id ELSE m.user1_id END)
  WHERE (m.user1_id = :u OR m.user2_id = :u)
  ORDER BY m.matched_at DESC
");
$stm->execute(['u'=>$uid]);
$matches = $stm->fetchAll();

function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<div class="container py-5">
  <h2 class="mb-4">Your Matches</h2>
  <?php if (!$matches): ?>
    <p class="text-muted">No matches yet. Keep swiping on the <a href="index.php">Home</a> page!</p>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach($matches as $m): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card h-100 text-center p-3">
            <img src="<?php echo e($m['photo']); ?>" alt="" class="rounded-circle mx-auto" style="width:100px;height:100px;object-fit:cover">
            <h5 class="mt-3"><?php echo e($m['name']); ?><?php echo $m['age']? ', '.(int)$m['age']:''; ?></h5>
            <p class="small mb-1">📍 <?php echo e($m['distance_km']); ?> km away</p>
            <?php if ($m['bio']): ?><p class="small">"<?php echo e($m['bio']); ?>"</p><?php endif; ?>
            <p class="text-success small mb-0">Matched on <?php echo e(date('Y-m-d H:i', strtotime($m['matched_at']))); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require 'footer.php'; ?>
