<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="topbar">
  <div class="topbar-left">
    <h1>
      <?php 
        // Nama halaman otomatis dari nama file
        $page = basename($_SERVER['PHP_SELF'], '.php');
        $judul = ucfirst($page); // contoh: dashboard → Dashboard
        echo $judul;
      ?>
    </h1>
  </div>

  <div class="topbar-right">
    <span class="admin-name">👤 <?= htmlspecialchars($_SESSION['user']); ?></span>
    <a href="logout.php" class="btn-logout">Logout</a>
  </div>
</div>
