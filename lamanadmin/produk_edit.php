<?php
include 'session_check.php';
include 'db.php';

$active_page = 'produk.php';
$message = '';
$message_type = 'message';
$produk = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_edit'])) {
    $id_produk = $_POST['id_produk'];
    $nama_produk = $_POST['nama_produk'];
    $harga_beli = $_POST['harga_beli'];
    $stok = $_POST['stok'];
    
    $stmt = $conn->prepare("UPDATE produk SET nama_produk = ?, harga_beli = ?, stok = ? WHERE id_produk = ?");
    $stmt->bind_param("sdis", $nama_produk, $harga_beli, $stok, $id_produk);
    
    if ($stmt->execute()) {
        header("Location: produk.php");
        exit();
    } else {
        $message = "Gagal memperbarui produk: " . $conn->error;
        $message_type = 'message error';
    }
}

if (isset($_GET['id'])) {
    $id_produk_edit = $_GET['id'];
    $stmt_get = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
    $stmt_get->bind_param("s", $id_produk_edit);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    if ($result->num_rows == 1) {
        $produk = $result->fetch_assoc();
    } else {
        $message = "Error: Produk tidak ditemukan.";
        $message_type = 'message error';
    }
} else {
    header("Location: produk.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk | Admin</title>
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
            <h1>Edit Produk</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>
            
            <?php if ($produk): ?>
            <div class="form-wrapper">
                <h2>Edit Data Produk: <?php echo htmlspecialchars($produk['nama_produk']); ?></h2>
                <form action="produk_edit.php" method="POST">
                    <input type="hidden" name="id_produk" value="<?php echo $produk['id_produk']; ?>">
                    
                    <div>
                        <label>Nama Produk:</label>
                        <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($produk['nama_produk']); ?>" required>
                    </div>
                    <div>
                        <label>Harga Beli:</label>
                        <input type="number" step="0.01" name="harga_beli" value="<?php echo $produk['harga_beli']; ?>" required>
                    </div>
                    <div>
                        <label>Stok Saat Ini:</label>
                        <input type="number" name="stok" value="<?php echo $produk['stok']; ?>" required>
                        <small>Anda dapat menyesuaikan stok secara manual di sini.</small>
                    </div>
                    <button type="submit" name="submit_edit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="produk.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
            <?php endif; ?>

        </div> 
    </div> 

</body>
</html>