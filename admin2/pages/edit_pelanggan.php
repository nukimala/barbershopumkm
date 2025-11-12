<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 🔹 Ambil ID pelanggan dari parameter URL
if (!isset($_GET['id'])) {
    header("Location: pelanggan.php");
    exit();
}

$id = $_GET['id'];

// 🔹 Ambil data pelanggan berdasarkan ID
$query = mysqli_query($conn, "SELECT * FROM customer WHERE id_customer = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data pelanggan tidak ditemukan.'); window.location='pelanggan.php';</script>";
    exit();
}

// 🔹 Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_customer']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update = mysqli_query($conn, "
        UPDATE customer 
        SET nama_customer = '$nama', status = '$status'
        WHERE id_customer = '$id'
    ");

    if ($update) {
        echo "<script>alert('Data pelanggan berhasil diperbarui.'); window.location='pelanggan.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Pelanggan | Barbershop</title>
  <link rel="stylesheet" href="../css/dashboard.css">
  <style>
    .content {
      margin-top: 30px;
      background: #fff;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      max-width: 600px;
    }

    h2 {
      margin-bottom: 20px;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    label {
      margin-top: 10px;
      font-weight: bold;
    }

    input, select {
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    .btn {
      margin-top: 20px;
      padding: 10px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      color: #fff;
    }

    .btn-save {
      background: #007bff;
    }

    .btn-cancel {
      background: #6c757d;
      text-decoration: none;
      display: inline-block;
      text-align: center;
      margin-top: 10px;
    }

    .btn:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <?php include '../includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <div class="content">
      <h2>Edit Data Pelanggan</h2>

      <form action="" method="POST">
        <label for="nama_customer">Nama Pelanggan:</label>
        <input type="text" id="nama_customer" name="nama_customer" value="<?= htmlspecialchars($data['nama_customer']); ?>" required>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
          <option value="Menunggu" <?= ($data['status'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
          <option value="Diproses" <?= ($data['status'] == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
          <option value="Selesai" <?= ($data['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
        </select>

        <button type="submit" class="btn btn-save">Simpan Perubahan</button>
        <a href="pelanggan.php" class="btn btn-cancel">Batal</a>
      </form>
    </div>
  </div>

</body>
</html>
