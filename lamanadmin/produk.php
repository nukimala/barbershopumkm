<?php
include 'session_check.php';
include 'db.php';

$active_page = basename($_SERVER['PHP_SELF']);
$message = '';
$message_type = 'message';

// === FITUR HAPUS PRODUK ===
if (isset($_GET['hapus'])) {
    $id_produk_hapus = $_GET['hapus'];
    
    $stmt_hapus = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
    $stmt_hapus->bind_param("s", $id_produk_hapus);
    
    if ($stmt_hapus->execute()) {
        $message = "Produk berhasil dihapus.";
    } else {
        $message = "Gagal menghapus produk. Pastikan produk tidak digunakan di 'Detail Layanan' manapun. Error: " . $conn->error;
        $message_type = 'message error';
    }
}

// === LOGIKA TAMBAH PRODUK ===
if (isset($_POST['submit'])) {
    $nama_produk = $_POST['nama_produk'];
    $harga_beli = $_POST['harga_beli'];
    $stok = 0; 
    $sql = "INSERT INTO produk (nama_produk, harga_beli, stok) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $nama_produk, $harga_beli, $stok);
    if ($stmt->execute()) { $message = "Produk baru berhasil ditambahkan."; } 
    else { $message = "Error: " . $stmt->error; $message_type = 'message error'; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk | Admin</title>
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
            <h1>Kelola Produk</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>

            <div class="form-wrapper">
                <h2>Tambah Produk Baru</h2>
                <form action="produk.php" method="POST">
                    <div>
                        <label>Nama Produk:</label>
                        <input type="text" name="nama_produk" required>
                    </div>
                    <div>
                        <label>Harga Beli (per unit/botol):</label>
                        <input type="number" step="0.01" name="harga_beli" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Tambah Produk</button>
                </form>
            </div>

            <h2>Daftar Produk Saat Ini</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Harga Beli</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM produk ORDER BY id_produk");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['id_produk'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_produk']) . "</td>";
                                echo "<td>" . number_format($row['harga_beli'], 0, ',', '.') . "</td>";
                                echo "<td>" . $row['stok'] . "</td>";
                                echo "<td><div class='table-actions'>";
                                echo "<a href='produk_edit.php?id=" . $row['id_produk'] . "' class='btn btn-warning btn-sm'>Edit</a>";
                                echo "<a href='produk.php?hapus=" . $row['id_produk'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"ANDA YAKIN? Menghapus produk ini mungkin gagal jika sedang terikat di Detail Layanan.\")'>Hapus</a>";
                                echo "</div></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='no-data'>Belum ada data produk.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div> 
    </div> 

</body>
</html>