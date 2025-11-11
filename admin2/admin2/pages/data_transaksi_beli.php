<?php
session_start();
include '../includes/koneksi.php';
$query = mysqli_query($conn, "
  SELECT t.id_beli, t.tanggal_beli, t.harga_beli, 
         COALESCE(s.nama_supplier, t.supplier_lain) AS nama_supplier
  FROM transaksi_beli t
  LEFT JOIN supplier s ON t.fk_supplier = s.id_supplier
  ORDER BY t.tanggal_beli DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Transaksi Pembelian</title>
<link rel="stylesheet" href="../css/dashboard.css">
<style>
  .content { background:#fff; padding:20px; border-radius:10px; margin-top:20px; }
  table { width:100%; border-collapse:collapse; }
  th, td { border:1px solid #ddd; padding:8px; }
  th { background:#007bff; color:#fff; }
  .btn-tambah { background:#28a745; color:#fff; padding:8px 12px; border-radius:5px; text-decoration:none; }
</style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/topbar.php'; ?>

<div class="content">
  <h2>Data Transaksi Pembelian</h2>
  <a href="tambah_transaksi_beli.php" class="btn-tambah">+ Tambah Transaksi</a>
  <table>
    <tr>
      <th>No</th>
      <th>ID Transaksi</th>
      <th>Tanggal</th>
      <th>Supplier</th>
      <th>Total Harga</th>
    </tr>
    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($query)) {
      echo "<tr>
              <td>{$no}</td>
              <td>{$row['id_beli']}</td>
              <td>{$row['tanggal_beli']}</td>
              <td>{$row['nama_supplier']}</td>
              <td>Rp " . number_format($row['harga_beli'], 0, ',', '.') . "</td>
            </tr>";
      $no++;
    }
    ?>
  </table>
</div>
</div>
</body>
</html>
