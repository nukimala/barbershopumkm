<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../includes/koneksi.php';

if (isset($_POST['submit'])) {
    $nomor = $_POST['nomor_antrian'];
    $nama = $_POST['nama_customer'];
    $tanggal = $_POST['tanggal'];
    $layanan = $_POST['layanan'];
    $model = $_POST['model'];
    $status = "Belum Selesai";

    $sql = "INSERT INTO antrian (nomor_antrian, nama_customer, tanggal, fk_layanan, fk_model, status)
            VALUES ('$nomor', '$nama', '$tanggal', '$layanan', '$model', '$status')";

}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Antrian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h3 class="mb-3">Tambah Antrian Baru</h3>

        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label class="form-label">Nomor Antrian</label>
                <input type="text" name="nomor_antrian" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nama Customer</label>
                <input type="text" name="nama_customer" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Model Rambut</label>
                <select name="model" class="form-select" required>
                    <option value="">-- Pilih Model --</option>
                    <?php
                    $modelQuery = mysqli_query($conn, "SELECT id_model, nama_model FROM model");
                    while ($m = mysqli_fetch_assoc($modelQuery)) {
                        echo "<option value='{$m['id_model']}'>{$m['nama_model']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Layanan</label>
                <select name="layanan" class="form-select">
                    <option value="">-- Pilih Layanan (Opsional) --</option>
                    <?php
                    $layananQuery = mysqli_query($conn, "SELECT id_layanan, nama_layanan FROM layanan");
                    while ($l = mysqli_fetch_assoc($layananQuery)) {
                        echo "<option value='{$l['id_layanan']}'>{$l['nama_layanan']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
