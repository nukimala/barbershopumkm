<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
// 🔹 Proses hapus data pelanggan
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);

    // Pastikan data ada sebelum dihapus
    $cek = mysqli_query($conn, "SELECT * FROM customer WHERE id_customer = '$id_hapus'");
    if (mysqli_num_rows($cek) > 0) {
        $hapus = mysqli_query($conn, "DELETE FROM customer WHERE id_customer = '$id_hapus'");
        if ($hapus) {
            echo "<script>alert('Data pelanggan berhasil dihapus.'); window.location='pelanggan.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data pelanggan.');</script>";
        }
    } else {
        echo "<script>alert('Data tidak ditemukan.'); window.location='pelanggan.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Pelanggan | Barbershop</title>
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

    .btn-add {
      background: #28a745;
      margin-bottom: 15px;
      display: inline-block;
    }

    .btn-edit {
      background: #ffc107;
      color: #000;
    }

    .btn-delete {
      background: #dc3545;
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
      <h2>Data Pelanggan</h2>
      <a href="pelanggan.php" class="btn btn-add">+ Tambah Pelanggan</a>

      <table>
        <tr>
          <th>No</th>
          <th>ID</th>
          <th>Nama</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>

        <?php
        $result = mysqli_query($conn, "SELECT * FROM customer ORDER BY id_customer ASC");
        $no = 1;
        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$no}</td>
                    <td>{$row['id_customer']}</td>
                    <td>{$row['nama_customer']}</td>
                    <td>{$row['status']}</td>
                    <td>
                      <a href='edit_pelanggan.php?id={$row['id_customer']}' class='btn btn-edit'>Edit</a>
                      <a href='pelanggan.php?hapus={$row['id_customer']}' class='btn btn-delete' onclick='return confirm(\"Yakin ingin hapus data ini?\")'>Hapus</a>
                    </td>
                  </tr>";
            $no++;
          }
        } else {
          echo "<tr><td colspan='5' style='text-align:center;'>Belum ada data pelanggan.</td></tr>";
        }
        ?>
      </table>
    </div>

  </div>

</body>
</html>
