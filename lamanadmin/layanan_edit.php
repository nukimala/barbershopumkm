<?php
include 'session_check.php';
include 'db.php';

$active_page = 'layanan.php';
$message = '';
$message_type = 'message';
$layanan = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_edit'])) {
    $id_layanan = $_POST['id_layanan'];
    $nama_layanan = $_POST['nama_layanan'];
    $harga_layanan = $_POST['harga_layanan'];
    $deskripsi_layanan = $_POST['deskripsi_layanan'];
    
    $stmt = $conn->prepare("UPDATE layanan SET nama_layanan = ?, harga_layanan = ?, deskripsi_layanan = ? WHERE id_layanan = ?");
    $stmt->bind_param("sdss", $nama_layanan, $harga_layanan, $deskripsi_layanan, $id_layanan);
    
    if ($stmt->execute()) {
        header("Location: layanan.php");
        exit();
    } else {
        $message = "Gagal memperbarui layanan: " . $conn->error;
        $message_type = 'message error';
    }
}

if (isset($_GET['id'])) {
    $id_layanan_edit = $_GET['id'];
    $stmt_get = $conn->prepare("SELECT * FROM layanan WHERE id_layanan = ?");
    $stmt_get->bind_param("s", $id_layanan_edit);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    if ($result->num_rows == 1) {
        $layanan = $result->fetch_assoc();
    } else {
        $message = "Error: Layanan tidak ditemukan.";
        $message_type = 'message error';
    }
} else {
    header("Location: layanan.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Layanan | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="logo.png" alt="Barbershop Logo">
        </div>
        <ul>
            <li><a href="dashboard.php" class="<?php echo ($active_page == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="customers.php" class="<?php echo ($active_page == 'customers.php') ? 'active' : ''; ?>">Customer</a></li>
            <li><a href="pembelian.php" class="<?php echo ($active_page == 'pembelian.php') ? 'active' : ''; ?>">Pembelian Stok</a></li>
            <li><a href="laporan.php" class="<?php echo ($active_page == 'laporan.php') ? 'active' : ''; ?>">Laporan</a></li>
            <li><a href="model.php" class="<?php echo ($active_page == 'model.php') ? 'active' : ''; ?>">Kelola Model</a></li>
            <li><a href="layanan.php" class="<?php echo ($active_page == 'layanan.php') ? 'active' : ''; ?>">Kelola Layanan</a></li>
            <li><a href="produk.php" class="<?php echo ($active_page == 'produk.php') ? 'active' : ''; ?>">Kelola Produk</a></li>
            <li><a href="detail_layanan.php" class="<?php echo ($active_page == 'detail_layanan.php') ? 'active' : ''; ?>">Detail Layanan</a></li>
            <li><a href="pesan.php" class="<?php echo ($active_page == 'pesan.php') ? 'active' : ''; ?>">Pesan Masuk</a></li>
        </ul>
    </div>

    <div class="main-content">
        
        <div class="topbar">
            <h1>Edit Layanan</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>
            
            <?php if ($layanan): ?>
            <div class="form-wrapper">
                <h2>Edit Data Layanan: <?php echo htmlspecialchars($layanan['nama_layanan']); ?></h2>
                <form action="layanan_edit.php" method="POST">
                    <input type="hidden" name="id_layanan" value="<?php echo $layanan['id_layanan']; ?>">
                    
                    <div>
                        <label>Nama Layanan:</label>
                        <input type="text" name="nama_layanan" value="<?php echo htmlspecialchars($layanan['nama_layanan']); ?>" required>
                    </div>
                    <div>
                        <label>Harga:</label>
                        <input type="number" step="0.01" name="harga_layanan" value="<?php echo $layanan['harga_layanan']; ?>" required>
                    </div>
                    <div>
                        <label>Deskripsi:</label>
                        <textarea name="deskripsi_layanan" required><?php echo htmlspecialchars($layanan['deskripsi_layanan']); ?></textarea>
                    </div>
                    <button type="submit" name="submit_edit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="layanan.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
            <?php endif; ?>

        </div> 
    </div> 

</body>
</html>