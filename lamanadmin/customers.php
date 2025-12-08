<?php
include 'session_check.php';
include '../include/db.php';

$active_page = basename($_SERVER['PHP_SELF']);
$message = '';
$message_type = 'message';

// === FITUR HAPUS CUSTOMER ===
if (isset($_GET['hapus'])) {
    $id_customer_hapus = $_GET['hapus'];
    $stmt_hapus = $conn->prepare("DELETE FROM customer WHERE id_customer = ?");
    $stmt_hapus->bind_param("s", $id_customer_hapus);
    
    if ($stmt_hapus->execute()) {
        $message = "Customer berhasil dihapus.";
    } else {
        $message = "Gagal menghapus customer: " . $conn->error;
        $message_type = 'message error';
    }
}

// === FITUR MEMBATALKAN (Jika salah pencet bayar, status dikembalikan) ===
if (isset($_GET['batalkan'])) {
    $id_customer = $_GET['batalkan'];
    // Update status kembali ke Belum Selesai (Stok akan balik via Trigger database jika ada)
    $conn->query("UPDATE customer SET status = 'Belum Selesai' WHERE id_customer = '$id_customer'");
    header('Location: customers.php');
    exit();
}

// === LOGIKA TAMBAH CUSTOMER (Antrian Baru) ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_customer'])) {
    $nama_customer = $_POST['nama_customer'];
    $fk_model = $_POST['fk_model'];
    $fk_layanan = !empty($_POST['fk_layanan']) ? $_POST['fk_layanan'] : NULL;
    $fk_admin = $_SESSION['admin_id'];

    if (empty($nama_customer) || empty($fk_model)) {
        $message = "Nama customer dan Model wajib diisi.";
        $message_type = 'message error';
    } else {
        $conn->begin_transaction();
        try {
            // 1. Insert Customer
            $stmt_cust = $conn->prepare("INSERT INTO customer (nama_customer) VALUES (?)");
            $stmt_cust->bind_param("s", $nama_customer);
            $stmt_cust->execute();
            
            // 2. Ambil ID Customer Baru
            $result_id = $conn->query("SELECT id_customer FROM customer WHERE DATE(waktu_daftar) = CURDATE() ORDER BY nomor_antrian DESC LIMIT 1");
            $new_customer_id = $result_id->fetch_assoc()['id_customer'];
            
            // 3. Insert Transaksi Jual (Awal)
            $stmt_jual = $conn->prepare("INSERT INTO transaksi_jual (tanggal_jual, fk_customer, fk_admin, fk_model, fk_layanan) VALUES (NOW(), ?, ?, ?, ?)");
            $stmt_jual->bind_param("ssss", $new_customer_id, $fk_admin, $fk_model, $fk_layanan);
            $stmt_jual->execute();
            
            $conn->commit();
            $message = "Customer masuk antrian!";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
            $message_type = 'message error';
        }
    }
}

$models_result = $conn->query("SELECT * FROM model ORDER BY nama_model");
$layanan_result = $conn->query("SELECT * FROM layanan ORDER BY nama_layanan");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kasir / Antrian | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <div class="sidebar">
        <div class="sidebar-logo"><img src="logo.png" alt="Logo"></div>
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
            <h1>Kasir & Antrian</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>

            <div class="form-wrapper">
                <h2>Input Pelanggan Baru</h2>
                <form action="customers.php" method="POST">
                    <div style="display: flex; gap: 15px;">
                        <div style="flex: 1;">
                            <label>Nama Customer:</label>
                            <input type="text" name="nama_customer" required placeholder="Masukkan nama...">
                        </div>
                        <div style="flex: 1;">
                            <label>Model Rambut:</label>
                            <select name="fk_model" required>
                                <option value="">Pilih Model</option>
                                <?php if ($models_result->num_rows > 0) {
                                    while($row = $models_result->fetch_assoc()) echo "<option value='{$row['id_model']}'>{$row['nama_model']} (" . number_format($row['harga_model']) . ")</option>";
                                } ?>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label>Layanan Tambahan:</label>
                            <select name="fk_layanan">
                                <option value="">- Tidak Ada -</option>
                                <?php 
                                $layanan_result->data_seek(0);
                                if ($layanan_result->num_rows > 0) {
                                    while($row = $layanan_result->fetch_assoc()) echo "<option value='{$row['id_layanan']}'>{$row['nama_layanan']} (" . number_format($row['harga_layanan']) . ")</option>";
                                } ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="tambah_customer" class="btn btn-primary" style="margin-top: 10px;">Masuk Antrian</button>
                </form>
            </div>

            <h2>Daftar Antrian Hari Ini</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Model</th>
                            <th>Layanan</th>
                            <th>Total Tagihan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT c.*, m.nama_model, l.nama_layanan, tj.total_harga_jual, tj.id_jual
                                FROM customer c
                                LEFT JOIN transaksi_jual tj ON c.id_customer = tj.fk_customer
                                LEFT JOIN model m ON tj.fk_model = m.id_model
                                LEFT JOIN layanan l ON tj.fk_layanan = l.id_layanan
                                WHERE DATE(c.waktu_daftar) = CURDATE() OR c.status = 'Belum Selesai'
                                ORDER BY c.status ASC, c.nomor_antrian ASC";
                        $result = $conn->query($sql);
                        
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . ($row['status'] == 'Belum Selesai' ? "<span style='font-size:1.2em; font-weight:bold;'>{$row['nomor_antrian']}</span>" : "-") . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_customer']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_model']) . "</td>";
                                echo "<td>" . ($row['nama_layanan'] ? htmlspecialchars($row['nama_layanan']) : '-') . "</td>";
                                echo "<td>Rp " . number_format($row['total_harga_jual'], 0, ',', '.') . "</td>";
                                
                                $status_class = ($row['status'] == 'Belum Selesai') ? 'status-belum' : 'status-selesai';
                                echo "<td><span class='$status_class'>" . $row['status'] . "</span></td>";
                                
                                echo "<td><div class='table-actions'>";
                                if ($row['status'] == 'Belum Selesai') {
                                    // TOMBOL BAYAR (Mengarah ke pembayaran.php)
                                    echo "<a href='pembayaran.php?id=" . $row['id_customer'] . "' class='btn btn-success btn-sm'>Bayar</a>";
                                    echo "<a href='customers.php?hapus=" . $row['id_customer'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Batalkan antrian ini?\")'>Batal</a>";
                                } else {
                                    // Jika sudah selesai, tombol untuk cetak ulang struk
                                    echo "<a href='cetak_struk.php?id=" . $row['id_jual'] . "' target='_blank' class='btn btn-secondary btn-sm'>Cetak Struk</a>";
                                }
                                echo "</div></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='no-data'>Belum ada antrian hari ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div> 
    </div> 
</body>
</html>