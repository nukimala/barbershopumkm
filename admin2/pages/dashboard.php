<?php
session_start();
include '../includes/koneksi.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit();
}

$total_antrian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM antrian"))['total'];
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_harga_jual), 0) AS total FROM transaksi_jual"))['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Barbershop</title>
  <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
  <?php include '../includes/sidebar.php'; ?>

  <!-- 🔹 Topbar -->
  <div class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <!-- 🔹 Konten utama -->
    <section class="cards">
      <div class="card">
        <h3>Total Antrian</h3>
        <p><?= $total_antrian; ?></p>
      </div>
      <div class="card">
        <h3>Total Pendapatan</h3>
        <p>Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></p>
      </div>
    </section>
  </div>
</body>

</html>