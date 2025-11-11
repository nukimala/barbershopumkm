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
  <title>Antrian | Barbershop</title>
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
    }

    .btn-delete {
      background: #dc3545;
    }

    .status {
      font-weight: bold;
      padding: 4px 8px;
      border-radius: 4px;
    }

    .waiting {
      background: #f1c40f;
      color: #000;
    }

    .active {
      background: #17a2b8;
    }

    .done {
      background: #28a745;
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
      <h2>Daftar Antrian Hari Ini</h2>
      <a href="tambah_antrian.php" class="btn btn-add">+ Tambah Antrian</a>

      <table>
        <tr>
          <th>No</th>
          <th>Nama Pelanggan</th>
          <th>Nomor Antrian</th>
          <th>Waktu</th>
        </tr>

        <?php
        $result = mysqli_query($conn, "SELECT * FROM antrian ORDER BY waktu_daftar ASC");
        $no = 1;
        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$no}</td>
                    <td>{$row['nama_customer']}</td>
                    <td>{$row['nomor_antrian']}</td>
                    <td>{$row['waktu_daftar']}</td>
                    
                  </tr>";
            $no++;
          }
        } else {
          echo "<tr><td colspan='7' style='text-align:center;'>Belum ada data antrian.</td></tr>";
        }
        ?>
      </table>
    </div>

  </div>

</body>
</html>
