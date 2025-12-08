<?php
include 'session_check.php';
include '../include/db.php';

$active_page = basename($_SERVER['PHP_SELF']);
$message = '';
$message_type = 'message';

// === FITUR HAPUS PRODUK ===
if (isset($_GET['hapus'])) {
    $id_produk_hapus = $_GET['hapus'];
    
    // Cek dulu apakah produk sudah pernah dipakai di transaksi?
    // Jika sudah pernah dibeli/dijual, sebaiknya jangan dihapus permanen (soft delete) atau ditolak.
    // Di sini kita pakai logika sederhana: Coba hapus, jika gagal (karena Foreign Key), tampilkan error.
    
    $stmt_hapus = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
    $stmt_hapus->bind_param("s", $id_produk_hapus);
    
    try {
        if ($stmt_hapus->execute()) {
            $message = "Produk berhasil dihapus.";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        // Error biasanya karena Foreign Key (produk ini ada di riwayat transaksi)
        $message = "Gagal menghapus! Produk ini sudah tercatat dalam riwayat transaksi (Beli/Jual).";
        $message_type = 'message error';
    }
}

// === LOGIKA TAMBAH PRODUK ===
if (isset($_POST['submit'])) {
    $nama_produk = $_POST['nama_produk'];
    $harga_beli = $_POST['harga_beli']; // Ini jadi harga default
    $stok = 0; // Stok awal produk baru pasti 0
    
    $sql = "INSERT INTO produk (nama_produk, harga_beli, stok) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $nama_produk, $harga_beli, $stok);
    
    if ($stmt->execute()) { 
        $message = "Produk baru berhasil ditambahkan."; 
    } else { 
        $message = "Error: " . $stmt->error; 
        $message_type = 'message error'; 
    }
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
            <h1>Kelola Produk (Master Data)</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>

            <div class="form-wrapper">
                <h2>Tambah Produk Baru</h2>
                <p style="font-size: 0.9em; color: #666; margin-bottom: 15px;">
                    Produk yang ditambahkan di sini akan muncul di menu Pembelian Stok.
                </p>
                <form action="produk.php" method="POST">
                    <div>
                        <label>Nama Produk:</label>
                        <input type="text" name="nama_produk" placeholder="Contoh: Pomade Waterbased" required>
                    </div>
                    <div>
                        <label>Harga Beli Acuan (Per Botol):</label>
                        <input type="number" step="0.01" name="harga_beli" placeholder="Harga standar dari supplier" required>
                        <small style="color: #888;">Harga ini akan muncul otomatis saat input pembelian, namun bisa diubah saat transaksi.</small>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Tambah Produk</button>
                </form>
            </div>

            <h2>Daftar Stok Produk Saat Ini</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Harga Beli (Acuan)</th>
                            <th>Stok Tersedia</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM produk ORDER BY nama_produk ASC");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['id_produk'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_produk']) . "</td>";
                                echo "<td>Rp " . number_format($row['harga_beli'], 0, ',', '.') . "</td>";
                                
                                // Highlight Stok jika menipis (opsional, visual saja)
                                $stok_class = ($row['stok'] < 5) ? 'style="color:red; font-weight:bold;"' : '';
                                echo "<td $stok_class>" . $row['stok'] . " pcs</td>";
                                
                                echo "<td><div class='table-actions'>";
                                echo "<a href='produk_edit.php?id=" . $row['id_produk'] . "' class='btn btn-warning btn-sm'>Edit</a>";
                                echo "<a href='produk.php?hapus=" . $row['id_produk'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin hapus? Produk yang sudah ada riwayat transaksinya tidak bisa dihapus.\")'>Hapus</a>";
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