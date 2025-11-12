<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../includes/koneksi.php';

// 🔹 Pastikan ID dikirim lewat URL
if (!isset($_GET['id'])) {
    echo "<script>alert('ID antrian tidak ditemukan!'); window.location='antrian.php';</script>";
    exit();
}

$id_customer = $_GET['id'];

// 🔹 Ambil data customer
$result = mysqli_query($conn, "SELECT * FROM customer WHERE id_customer = '$id_customer'");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Data antrian tidak ditemukan!'); window.location='antrian.php';</script>";
    exit();
}

// 🔹 Jika form disubmit
if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_customer']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update = mysqli_query($conn, "
        UPDATE customer 
        SET nama_customer = '$nama',
            status = '$status'
        WHERE id_customer = '$id_customer'
    ");

    if ($update) {
        echo "<script>alert('Data antrian berhasil diperbarui.'); window.location='antrian.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data antrian: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Antrian</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
    </div>
  </nav>

  <div class="container mt-4">
    <h3 class="mb-3">Edit Antrian Pelanggan</h3>

    <form method="POST" class="card p-4 shadow-sm">
      <div class="mb-3">
        <label class="form-label">Nomor Antrian</label>
        <input type="text" class="form-control" value="<?= $data['nomor_antrian']; ?>" disabled>
      </div>

      <div class="mb-3">
        <label class="form-label">Nama Customer</label>
        <input type="text" name="nama_customer" class="form-control" value="<?= htmlspecialchars($data['nama_customer']); ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <option value="Belum Selesai" <?= ($data['status'] == 'Belum Selesai') ? 'selected' : ''; ?>>Belum Selesai</option>
          <option value="Selesai" <?= ($data['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
        </select>
      </div>

      <div class="text-end">
        <button type="submit" name="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="antrian.php" class="btn btn-secondary">Kembali</a>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
