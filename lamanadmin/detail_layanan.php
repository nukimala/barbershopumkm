<?php
include 'session_check.php';
include '../include/db.php';

$active_page = basename($_SERVER['PHP_SELF']);
$message = '';
$message_type = 'message';

// === FITUR HAPUS DETAIL LAYANAN ===
if (isset($_GET['hapus_layanan']) && isset($_GET['hapus_produk'])) {
    $fk_layanan_hapus = $_GET['hapus_layanan'];
    $fk_produk_hapus = $_GET['hapus_produk'];
    
    $stmt_hapus = $conn->prepare("DELETE FROM detail_layanan WHERE fk_layanan = ? AND fk_produk = ?");
    $stmt_hapus->bind_param("ss", $fk_layanan_hapus, $fk_produk_hapus);
    
    if ($stmt_hapus->execute()) {
        $message = "Detail layanan berhasil dihapus.";
    } else {
        $message = "Gagal menghapus detail layanan: " . $conn->error;
        $message_type = 'message error';
    }
}


// === LOGIKA TAMBAH DETAIL LAYANAN ===
if (isset($_POST['submit'])) {
    $fk_layanan = $_POST['fk_layanan'];
    $fk_produk = $_POST['fk_produk'];
    $jumlah_produk = $_POST['jumlah_produk'];

    $cek_sql = "SELECT * FROM detail_layanan WHERE fk_layanan = ? AND fk_produk = ?";
    $cek_stmt = $conn->prepare($cek_sql);
    $cek_stmt->bind_param("ss", $fk_layanan, $fk_produk);
    $cek_stmt->execute();
    $cek_result = $cek_stmt->get_result();

    if ($cek_result->num_rows > 0) {
        $message = "Error: Produk ini sudah ada dalam layanan tersebut.";
        $message_type = 'message error';
    } else {
        $sql = "INSERT INTO detail_layanan (fk_layanan, fk_produk, jumlah_produk) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $fk_layanan, $fk_produk, $jumlah_produk);
        if ($stmt->execute()) { $message = "Detail layanan berhasil ditambahkan."; } 
        else { $message = "Error: " . $stmt->error; $message_type = 'message error'; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Layanan | Admin</title>
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
            <h1>Kelola Detail Layanan</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>

            <div class="form-wrapper">
                <h2>Tambah Detail Layanan</h2>
                <form action="detail_layanan.php" method="POST">
                    <div>
                        <label>Pilih Layanan:</label>
                        <select name="fk_layanan" required>
                            <option value="">Pilih Layanan</option>
                            <?php
                            $layanan_result = $conn->query("SELECT * FROM layanan ORDER BY nama_layanan");
                            while ($row = $layanan_result->fetch_assoc()) {
                                echo "<option value='{$row['id_layanan']}'>{$row['nama_layanan']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Pilih Produk:</label>
                        <select name="fk_produk" required>
                            <option value="">Pilih Produk</option>
                            <?php
                            $produk_result = $conn->query("SELECT * FROM produk ORDER BY nama_produk");
                            while ($row = $produk_result->fetch_assoc()) {
                                echo "<option value='{$row['id_produk']}'>{$row['nama_produk']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Jumlah Produk (yang dipakai per layanan):</label>
                        <input type="number" name="jumlah_produk" value="1" min="1" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Tambahkan ke Layanan</button>
                </form>
            </div>

            <h2>Daftar Detail Layanan Saat Ini</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Layanan</th>
                            <th>Produk yang Termasuk</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT l.nama_layanan, p.nama_produk, dl.jumlah_produk, dl.fk_layanan, dl.fk_produk
                                FROM detail_layanan dl
                                JOIN layanan l ON dl.fk_layanan = l.id_layanan
                                JOIN produk p ON dl.fk_produk = p.id_produk
                                ORDER BY l.nama_layanan, p.nama_produk";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['nama_layanan']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_produk']) . "</td>";
                                echo "<td>" . $row['jumlah_produk'] . "</td>";
                                echo "<td><div class='table-actions'>";
                                echo "<a href='detail_layanan_edit.php?fk_layanan=" . $row['fk_layanan'] . "&fk_produk=" . $row['fk_produk'] . "' class='btn btn-warning btn-sm'>Edit</a>";
                                echo "<a href='detail_layanan.php?hapus_layanan=" . $row['fk_layanan'] . "&hapus_produk=" . $row['fk_produk'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Hapus produk ini dari layanan?\")'>Hapus</a>";
                                echo "</div></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='no-data'>Belum ada data detail layanan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div> 
    </div> 

</body>
</html>