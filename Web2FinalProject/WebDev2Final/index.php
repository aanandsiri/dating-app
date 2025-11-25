<?php
$pageTitle = "Home";
include 'header.php';
require 'db.php';
// CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];
?>
<script>
  window.MM = { csrf: <?php echo json_encode($csrf, JSON_UNESCAPED_SLASHES); ?> };
</script>
<?php

if (empty($_SESSION['user_id'])) { header('Location: register.php'); exit; }
$uid   = (int)$_SESSION['user_id'];
$last  = isset($_SESSION['last_seen_id']) ? (int)$_SESSION['last_seen_id'] : 0;

// CSRF token (already have this in your file; keep it)
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
$csrf = $_SESSION['csrf'];

// 1) Try to get the next unseen profile with id > last_seen_id
$sql = "
  SELECT id, name, age, photo, distance_km, bio
  FROM users
  WHERE id <> :uid
    AND id > :last
    AND photo IS NOT NULL AND photo <> ''
    AND bio   IS NOT NULL AND bio   <> ''
    AND age IS NOT NULL
    AND id NOT IN (
      SELECT liked_id    FROM likes    WHERE liker_id    = :uid
      UNION
      SELECT disliked_id FROM dislikes WHERE disliker_id = :uid
    )
  ORDER BY id ASC
  LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->execute(['uid'=>$uid,'last'=>$last]);
$row = $stmt->fetch();

// 2) If none found, wrap around from the beginning
if (!$row) {
  $sql = "
    SELECT id, name, age, photo, distance_km, bio
    FROM users
    WHERE id <> :uid
      AND photo IS NOT NULL AND photo <> ''
      AND bio   IS NOT NULL AND bio   <> ''
      AND age IS NOT NULL
      AND id NOT IN (
        SELECT liked_id    FROM likes    WHERE liker_id    = :uid
        UNION
        SELECT disliked_id FROM dislikes WHERE disliker_id = :uid
      )
    ORDER BY id ASC
    LIMIT 1
  ";
  $stmt = $conn->prepare($sql);
  $stmt->execute(['uid'=>$uid]);
  $row = $stmt->fetch();
}
?>
<section class="bg-danger text-white text-center py-5">
  <div class="container">
    <h1 class="display-4 fw-bold">Find Your Person</h1>
    <p class="lead">Swipe. Match. Connect. 💘</p>
  </div>
</section>

<section class="container py-5">
  <h2 class="text-center text-danger mb-4">Nearby Matches</h2>

  <?php if ($row): ?>
    <div class="d-flex justify-content-center">
      <div class="card text-center shadow-lg p-3" style="width:18rem" id="profileCard" data-id="<?php echo (int)$row['id']; ?>">
        <img src="<?php echo htmlspecialchars($row['photo'], ENT_QUOTES); ?>" class="rounded-circle mx-auto mt-3" style="width:120px;height:120px;object-fit:cover" alt="Profile">
        <div class="card-body">
          <h5 class="card-title"><?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?><?php echo $row['age']? ', '.(int)$row['age']:''; ?></h5>
          <?php if ($row['distance_km']!==''): ?><p class="card-text">📍 <?php echo (int)$row['distance_km']; ?> km away</p><?php endif; ?>
          <?php if (!empty($row['bio'])): ?><p class="card-text">"<?php echo htmlspecialchars($row['bio'], ENT_QUOTES); ?>"</p><?php endif; ?>
          <div class="d-flex justify-content-around mt-3">
            <button class="btn btn-secondary rounded-pill px-4 btn-dislike">👎 Dislike</button>
            <button class="btn btn-danger rounded-pill px-4 btn-like">❤️ Like</button>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <p class="text-center text-muted">No more profiles right now. Come back later!</p>
  <?php endif; ?>
</section>

<script>
(function(){
  const card = document.getElementById('profileCard');
  const likeBtn = document.querySelector('.btn-like');
  const dislikeBtn = document.querySelector('.btn-dislike');
  const csrf = (window.MM && MM.csrf) ? MM.csrf : '';

  async function react(action){
    if (!card) return;
    const profileId = card.dataset.id;
    [likeBtn,dislikeBtn].forEach(b=>b&&(b.disabled=true));
    try{
      const res = await fetch('reaction.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({ action, profile_id: profileId, csrf })
      });
      const data = await res.json();
      if (data.ok) {
        if (data.match) alert("It's a match! 🎉");
        location.reload(); // loads the NEXT one
      } else {
        alert(data.message || 'Error');
      }
    }catch(e){ alert('Network error: '+e.message); }
    finally{ [likeBtn,dislikeBtn].forEach(b=>b&&(b.disabled=false)); }
  }

  likeBtn && likeBtn.addEventListener('click',()=>react('like'));
  dislikeBtn && dislikeBtn.addEventListener('click',()=>react('dislike'));
})();
</script>

<?php include 'footer.php'; ?>
