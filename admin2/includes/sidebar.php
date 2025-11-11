<?php
// Deteksi halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
  <h2>Barbershop</h2>
  <ul>
    <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
    <li><a href="antrian.php" class="<?= $current_page == 'antrian.php' ? 'active' : '' ?>">Antrian</a></li>
    <li><a href="pelanggan.php" class="<?= $current_page == 'pelanggan.php' ? 'active' : '' ?>">Data Pelanggan</a></li>
    <li><a href="laporan.php" class="<?= $current_page == 'laporan.php' ? 'active' : '' ?>">Laporan</a></li>
    <li><a href="transaksi_beli.php" class="<?= $current_page == 'transaksi_beli.php' ? 'active' : '' ?>">Transaksi Beli</a></li>
    <li><a href="logout.php" class="logout">Logout</a></li>
  </ul>
</div>