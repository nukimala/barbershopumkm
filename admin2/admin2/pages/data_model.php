<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/koneksi.php';

// Cek jika user belum login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Logika untuk Hapus Data
if (isset($_GET['hapus'])) {
    $id_model = $_GET['hapus'];
    // Amankan dari SQL Injection
    $id_model = mysqli_real_escape_string($conn, $id_model);

    $sql_delete = "DELETE FROM model WHERE id_model = '$id_model'";
    if (mysqli_query($conn, $sql_delete)) {
        echo "<script>alert('Data model berhasil dihapus.'); window.location.href = 'data_model.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data: " . mysqli_error($conn) . "'); window.location.href = 'data_model.php';</script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Model | Barbershop</title>
  <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    /* Style ini disalin dari antrian.php & pelanggan.php Anda */
    .content {
      margin-top: 20px;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h2 { margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    table th, table td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    table th { background: #007bff; color: #fff; }
    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      color: #fff;
      display: inline-block; /* Agar rapi */
      margin-right: 5px; /* Jarak antar tombol */
    }
    .btn-add {
      background: #28a745;
      margin-bottom: 15px;
    }
    .btn-edit {
      background: #ffc107;
      color: #000; /* Edit button di file Anda pakai color black */
    }
    .btn-delete {
      background: #dc3545;
    }
  </style>
</head>
<body>

  <?php include '../includes/sidebar.php'; ?>

  <div class="main-content">

    <?php include '../includes/topbar.php'; ?>

    <div class="content">
      <h2>Data Model Rambut</h2>
      <a href="tambah_model.php" class="btn btn-add">+ Tambah Model</a>

      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Model</th>
            <th>Harga</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Asumsi tabel Anda memiliki kolom 'harga_model'
          $result = mysqli_query($conn, "SELECT * FROM model ORDER BY id_model ASC");
          $no = 1;
          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              // Asumsi ada kolom 'harga_model'
              $harga = isset($row['harga_model']) ? $row['harga_model'] : 0;

              echo "<tr>
                      <td>{$no}</td>
                      <td>{$row['nama_model']}</td>
                      <td>Rp " . number_format($harga, 0, ',', '.') . "</td>
                      <td>
                        <a href='edit_model.php?id={$row['id_model']}' class='btn btn-edit'>Edit</a>
                        <a href='data_model.php?hapus={$row['id_model']}' class='btn btn-delete' onclick='return confirm(\"Yakin ingin hapus data ini?\")'>Hapus</a>
                      </td>
                    </tr>";
              $no++;
            }
          } else {
            echo "<tr><td colspan='4' style='text-align:center;'>Belum ada data model.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

  </div>

</body>
</html>