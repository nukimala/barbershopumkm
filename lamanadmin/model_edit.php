<?php
include 'session_check.php';
include '../include/db.php';

$active_page = 'model.php';
$message = '';
$message_type = 'message';
$model = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_edit'])) {
    $id_model = $_POST['id_model'];
    $nama_model = $_POST['nama_model'];
    $harga_model = $_POST['harga_model'];
    $deskripsi_model = $_POST['deskripsi_model'];
    
    $stmt = $conn->prepare("UPDATE model SET nama_model = ?, harga_model = ?, deskripsi_model = ? WHERE id_model = ?");
    $stmt->bind_param("sdss", $nama_model, $harga_model, $deskripsi_model, $id_model);
    
    if ($stmt->execute()) {
        header("Location: model.php");
        exit();
    } else {
        $message = "Gagal memperbarui model: " . $conn->error;
        $message_type = 'message error';
    }
}

if (isset($_GET['id'])) {
    $id_model_edit = $_GET['id'];
    $stmt_get = $conn->prepare("SELECT * FROM model WHERE id_model = ?");
    $stmt_get->bind_param("s", $id_model_edit);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    if ($result->num_rows == 1) {
        $model = $result->fetch_assoc();
    } else {
        $message = "Error: Model tidak ditemukan.";
        $message_type = 'message error';
    }
} else {
    header("Location: model.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Model | Admin</title>
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
            <h1>Edit Model</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>
            
            <?php if ($model): ?>
            <div class="form-wrapper">
                <h2>Edit Data Model: <?php echo htmlspecialchars($model['nama_model']); ?></h2>
                <form action="model_edit.php" method="POST">
                    <input type="hidden" name="id_model" value="<?php echo $model['id_model']; ?>">
                    
                    <div>
                        <label>Nama Model:</label>
                        <input type="text" name="nama_model" value="<?php echo htmlspecialchars($model['nama_model']); ?>" required>
                    </div>
                    <div>
                        <label>Harga:</label>
                        <input type="number" step="0.01" name="harga_model" value="<?php echo $model['harga_model']; ?>" required>
                    </div>
                    <div>
                        <label>Deskripsi:</label>
                        <textarea name="deskripsi_model" required><?php echo htmlspecialchars($model['deskripsi_model']); ?></textarea>
                    </div>
                    <button type="submit" name="submit_edit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="model.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
            <?php endif; ?>

        </div> 
    </div> 

</body>
</html>