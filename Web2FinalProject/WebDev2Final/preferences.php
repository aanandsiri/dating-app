<?php
$pageTitle = "Preferences";
require 'header.php';
?>
<div class="container py-5">
  <h2 class="mb-4">Preferences</h2>
  <form id="prefsForm">
    <div class="mb-3">
      <label class="form-label">Theme</label>
      <select name="theme" class="form-select">
        <option value="light" <?php echo (($_COOKIE['theme']??'light')==='light')?'selected':''; ?>>Light</option>
        <option value="dark"  <?php echo (($_COOKIE['theme']??'light')==='dark') ?'selected':''; ?>>Dark</option>
      </select>
      <div class="form-text">Stored in a cookie.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Max distance (km) for browsing</label>
      <input name="max_distance" type="number" min="1" class="form-control" value="<?php echo htmlspecialchars($_COOKIE['max_distance'] ?? '50', ENT_QUOTES); ?>">
      <div class="form-text">Stored in a cookie and used on Home filtering.</div>
    </div>
    <button class="btn btn-danger">Save Preferences</button>
    <div id="prefsAlert" class="mt-3"></div>
  </form>
</div>
<?php require 'footer.php'; ?>
