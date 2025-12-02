<?php
include 'session_check.php';
include '../include/db.php';

$active_page = basename($_SERVER['PHP_SELF']);
$message = '';
$message_type = 'message';
$fk_admin = $_SESSION['admin_id'];

// === FITUR HAPUS RIWAYAT ===
if (isset($_GET['hapus'])) {
    $id_detail_beli_hapus = $_GET['hapus'];
    
    $stmt_hapus = $conn->prepare("DELETE FROM detail_beli WHERE id_detail_beli = ?");
    $stmt_hapus->bind_param("s", $id_detail_beli_hapus);
    
    if ($stmt_hapus->execute()) {
        $message = "Riwayat pembelian berhasil dihapus.";
    } else {
        $message = "Gagal menghapus riwayat: " . $conn->error;
        $message_type = 'message error';
    }
}


// === LOGIKA TAMBAH PEMBELIAN ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_pembelian'])) {
    $fk_supplier = $_POST['fk_supplier'];
    $fk_produk = $_POST['fk_produk'];
    $jumlah_beli = $_POST['jumlah_beli'];
    
    if (empty($fk_supplier) || empty($fk_produk) || empty($jumlah_beli) || $jumlah_beli <= 0) {
        $message = "Semua field wajib diisi dan jumlah harus lebih dari 0.";
        $message_type = 'message error';
    } else {
        
        $conn->begin_transaction();
        try {
            $id_beli = '';
            $stmt_cek = $conn->prepare("SELECT id_beli FROM transaksi_beli WHERE fk_supplier = ? AND fk_admin = ? AND DATE(tanggal_beli) = CURDATE() LIMIT 1");
            $stmt_cek->bind_param("ss", $fk_supplier, $fk_admin);
            $stmt_cek->execute();
            $result_cek = $stmt_cek->get_result();
            
            if ($result_cek->num_rows > 0) {
                $id_beli = $result_cek->fetch_assoc()['id_beli'];
            } else {
                $stmt_ins_beli = $conn->prepare("INSERT INTO transaksi_beli (tanggal_beli, fk_supplier, fk_admin) VALUES (NOW(), ?, ?)");
                $stmt_ins_beli->bind_param("ss", $fk_supplier, $fk_admin);
                $stmt_ins_beli->execute();
                
                $result_id = $conn->query("SELECT id_beli FROM transaksi_beli WHERE fk_supplier = '$fk_supplier' AND fk_admin = '$fk_admin' AND DATE(tanggal_beli) = CURDATE() LIMIT 1");
                $id_beli = $result_id->fetch_assoc()['id_beli'];
            }
            
            $stmt_detail = $conn->prepare("INSERT INTO detail_beli (jumlah_beli, fk_beli, fk_produk) VALUES (?, ?, ?)");
            $stmt_detail->bind_param("iss", $jumlah_beli, $id_beli, $fk_produk);
            $stmt_detail->execute();
            
            $conn->commit();
            $message = "Pembelian berhasil dicatat.";
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: Gagal mencatat pembelian. " . $e->getMessage();
            $message_type = 'message error';
        }
    }
}

$suppliers = $conn->query("SELECT * FROM supplier ORDER BY nama_supplier");
$produks = $conn->query("SELECT * FROM produk ORDER BY nama_produk");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembelian Stok | Admin</title>
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
            <h1>Pembelian Stok</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>

            <div class="form-wrapper">
                <h2>Pembelian Baru</h2>
                <form action="pembelian.php" method="POST">
                    <div>
                        <label>Pilih Supplier:</label>
                        <select name="fk_supplier" required>
                            <option value="">Pilih Supplier</option>
                            <?php
                            if ($suppliers->num_rows > 0) {
                                while($row = $suppliers->fetch_assoc()) {
                                    echo "<option value='{$row['id_supplier']}'>{$row['nama_supplier']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Pilih Produk:</label>
                        <select name="fk_produk" required>
                            <option value="">Pilih Produk</option>
                            <?php
                            $produks->data_seek(0); 
                            if ($produks->num_rows > 0) {
                                while($row = $produks->fetch_assoc()) {
                                    echo "<option value='{$row['id_produk']}'>{$row['nama_produk']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Jumlah Beli (botol):</label>
                        <input type="number" name="jumlah_beli" min="1" required>
                    </div>
                    <button type="submit" name="submit_pembelian" class="btn btn-primary">Catat Pembelian</button>
                </form>
            </div>
            
            <h2>Riwayat Pembelian Terakhir</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Beli</th>
                            <th>Tanggal Beli</th>
                            <th>Supplier</th>
                            <th>Produk</th>
                            <th>Jumlah Beli</th>
                            <th>Total Harga Beli</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT db.id_detail_beli, tb.id_beli, tb.tanggal_beli, s.nama_supplier, p.nama_produk, db.jumlah_beli, tb.total_harga_beli
                                FROM detail_beli db
                                JOIN transaksi_beli tb ON db.fk_beli = tb.id_beli
                                JOIN produk p ON db.fk_produk = p.id_produk
                                JOIN supplier s ON tb.fk_supplier = s.id_supplier
                                ORDER BY tb.tanggal_beli DESC, db.id_detail_beli DESC
                                LIMIT 20";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['id_beli'] . "</td>";
                                echo "<td>" . $row['tanggal_beli'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_supplier']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_produk']) . "</td>";
                                echo "<td>" . $row['jumlah_beli'] . "</td>";
                                echo "<td>" . number_format($row['total_harga_beli'], 0, ',', '.') . "</td>";
                                echo "<td>";
                                echo "<a href='pembelian.php?hapus=" . $row['id_detail_beli'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Hapus? Menghapus riwayat juga akan menghapus data transaksinya.\")'>Hapus</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='no-data'>Belum ada riwayat pembelian.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div> 
    </div> 

</body>
</html>