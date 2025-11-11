<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Mulai session
include '../includes/koneksi.php';

// Cek jika user belum login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$pesan = ""; // Untuk notifikasi

// ==================================================
// INILAH "FUNGSI" (LOGIKA) UNTUK MENAMBAHKAN MODEL
// ==================================================
if (isset($_POST['submit'])) {
    // Ambil data dan amankan dari SQL Injection
    $nama_model = mysqli_real_escape_string($conn, $_POST['nama_model']);
    $harga_model = mysqli_real_escape_string($conn, $_POST['harga_model']);

    // Validasi sederhana (pastikan tidak kosong)
    if (empty($nama_model) || empty($harga_model)) {
        $pesan = "<div class='alert alert-danger'>Nama model dan harga tidak boleh kosong!</div>";
    } else {
        // Query INSERT
        $sql = "INSERT INTO model (nama_model, harga_model) VALUES ('$nama_model', '$harga_model')";

        if (mysqli_query($conn, $sql)) {
            // Jika berhasil, tampilkan alert dan arahkan ke data_model.php
            echo "<script>
                    alert('Data model baru berhasil ditambahkan!');
                    window.location.href = 'data_model.php';
                  </script>";
            exit();
        } else {
            // Jika gagal
            $pesan = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Model Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h3 class="mb-3">Tambah Model Rambut Baru</h3>

        <?php echo $pesan; // Tampilkan pesan error jika ada ?>

        <form method="POST" action="tambah_model.php" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="nama_model" class="form-label">Nama Model</label>
                <input type="text" id="nama_model" name="nama_model" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label for="harga_model" class="form-label">Harga Model (Rp)</label>
                <input type="number" id="harga_model" name="harga_model" class="form-control" placeholder="Contoh: 25000" required>
            </div>

            <div class="text-end">
                <button type="submit" name="submit" class="btn btn-primary">Simpan Model</button>
                <a href="data_model.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>