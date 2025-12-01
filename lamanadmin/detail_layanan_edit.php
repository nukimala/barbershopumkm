<?php
include 'session_check.php';
include '../include/db.php';

$active_page = 'detail_layanan.php';
$message = '';
$message_type = 'message';
$detail = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_edit'])) {
    $fk_layanan = $_POST['fk_layanan'];
    $fk_produk = $_POST['fk_produk'];
    $jumlah_produk = $_POST['jumlah_produk'];
    
    if ($jumlah_produk <= 0) {
        $message = "Jumlah produk harus lebih dari 0.";
        $message_type = 'message error';
    } else {
        $stmt = $conn->prepare("UPDATE detail_layanan SET jumlah_produk = ? WHERE fk_layanan = ? AND fk_produk = ?");
        $stmt->bind_param("iss", $jumlah_produk, $fk_layanan, $fk_produk);
        
        if ($stmt->execute()) {
            header("Location: detail_layanan.php");
            exit();
        } else {
            $message = "Gagal memperbarui jumlah: " . $conn->error;
            $message_type = 'message error';
        }
    }
}

if (isset($_GET['fk_layanan']) && isset($_GET['fk_produk'])) {
    $fk_layanan_edit = $_GET['fk_layanan'];
    $fk_produk_edit = $_GET['fk_produk'];
    
    $stmt_get = $conn->prepare("SELECT dl.*, l.nama_layanan, p.nama_produk 
                               FROM detail_layanan dl
                               JOIN layanan l ON dl.fk_layanan = l.id_layanan
                               JOIN produk p ON dl.fk_produk = p.id_produk
                               WHERE dl.fk_layanan = ? AND dl.fk_produk = ?");
    $stmt_get->bind_param("ss", $fk_layanan_edit, $fk_produk_edit);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($result->num_rows == 1) {
        $detail = $result->fetch_assoc();
    } else {
        $message = "Error: Detail layanan tidak ditemukan.";
        $message_type = 'message error';
    }
} else {
    header("Location: detail_layanan.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Detail Layanan | Admin</title>
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
            <h1>Edit Detail Layanan</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>
            
            <?php if ($detail): ?>
            <div class="form-wrapper">
                <h2>Edit Detail</h2>
                <form action="detail_layanan_edit.php" method="POST">
                    <input type="hidden" name="fk_layanan" value="<?php echo $detail['fk_layanan']; ?>">
                    <input type="hidden" name="fk_produk" value="<?php echo $detail['fk_produk']; ?>">
                    
                    <div>
                        <label>Nama Layanan:</label>
                        <input type="text" value="<?php echo htmlspecialchars($detail['nama_layanan']); ?>" disabled>
                    </div>
                    <div>
                        <label>Nama Produk:</label>
                        <input type="text" value="<?php echo htmlspecialchars($detail['nama_produk']); ?>" disabled>
                    </div>
                    <div>
                        <label>Jumlah Produk (yang dipakai per layanan):</label>
                        <input type="number" name="jumlah_produk" value="<?php echo $detail['jumlah_produk']; ?>" min="1" required>
                    </div>
                    
                    <button type="submit" name="submit_edit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="detail_layanan.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
            <?php endif; ?>

        </div> 
    </div> 

</body>
</html>