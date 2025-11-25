<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = $pageTitle ?? 'MatchMaker';
$theme = $_COOKIE['theme'] ?? 'light'; // cookie-based preference
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($theme, ENT_QUOTES); ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES); ?></title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $theme === 'dark' ? 'bg-dark text-light' : ''; ?>">
<nav class="navbar navbar-expand-lg <?php echo $theme==='dark'?'navbar-dark bg-dark':'navbar-dark bg-danger'; ?>">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">❤️ MatchMaker</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMM">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="navMM" class="collapse navbar-collapse">
     <ul class="navbar-nav ms-auto">
  <?php if (!empty($_SESSION['user_id'])): ?>
    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
    <li class="nav-item"><a class="nav-link" href="matches.php">Matches</a></li>
    <li class="nav-item"><a class="nav-link" href="preferences.php">Preferences</a></li>
    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
  <?php else: ?>
    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
  <?php endif; ?>
</ul>

    </div>
  </div>
</nav>
