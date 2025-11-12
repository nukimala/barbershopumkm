<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan | Barbershop</title>
  <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    .content {
      margin-top: 20px;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    h2 {
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table th, table td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    table th {
      background: #007bff;
      color: #fff;
    }

    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      color: #fff;
    }

    .btn-print {
      background: #17a2b8;
      margin-bottom: 15px;
      display: inline-block;
    }

    .total {
      font-weight: bold;
      text-align: right;
      background: #f8f9fa;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <?php include '../includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">

    <!-- Topbar -->
    <?php include '../includes/topbar.php'; ?>

    <!-- Content -->
    <div class="content">
      <h2>Laporan Transaksi</h2>
      <a href="cetak_laporan.php" target="_blank" class="btn btn-print">🖨️ Cetak Laporan</a>

      <table>
        <tr>
          <th>No</th>
          <th>ID Transaksi</th>
          <th>Nama Pelanggan</th>
          <th>Tanggal</th>
          <th>Total Harga</th>
        </tr>

        <?php
        $query = "SELECT 
                    transaksi_jual.id_jual, 
                    customer.nama_customer, 
                    transaksi_jual.tanggal_jual, 
                    transaksi_jual.total_harga_jual
                FROM transaksi_jual
                LEFT JOIN customer 
                    ON transaksi_jual.fk_customer = customer.id_customer
                ORDER BY transaksi_jual.tanggal_jual DESC";

        $result = mysqli_query($conn, $query);
        $no = 1;
        $total_semua = 0;

        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            $total_semua += $row['total_harga_jual'];
            echo "<tr>
                    <td>{$no}</td>
                    <td>{$row['id_jual']}</td>
                    <td>{$row['nama_customer']}</td>
                    <td>{$row['tanggal_jual']}</td>
                    <td>Rp " . number_format($row['total_harga_jual'], 0, ',', '.') . "</td>
                  </tr>";
            $no++;
          }
          echo "<tr class='total'>
                  <td colspan='4'>Total Pendapatan</td>
                  <td>Rp " . number_format($total_semua, 0, ',', '.') . "</td>
                </tr>";
        } else {
          echo "<tr><td colspan='5' style='text-align:center;'>Belum ada data transaksi.</td></tr>";
        }
        ?>
      </table>
    </div>

  </div>

</body>
</html>
