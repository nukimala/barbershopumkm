<?php
include 'session_check.php';
include 'db.php';

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

// === FITUR 1: MENANDAI SELESAI (STOK BERKURANG DI SINI VIA TRIGGER) ===
if (isset($_GET['selesai'])) {
    $id_customer = $_GET['selesai'];
    
    // Kita gunakan try-catch karena trigger database mungkin menolak jika stok minus
    try {
        $stmt = $conn->prepare("UPDATE customer SET status = 'Selesai' WHERE id_customer = ?");
        $stmt->bind_param("s", $id_customer);
        
        if ($stmt->execute()) { 
            // Refresh halaman agar status terupdate
            header('Location: customers.php'); 
            exit(); 
        } else { 
            $message = "Gagal update status: " . $stmt->error; 
            $message_type = 'message error'; 
        }
    } catch (Exception $e) {
        $message = "Gagal menyelesaikan pesanan: " . $e->getMessage();
        $message_type = 'message error';
    }
}

// === FITUR 2: MEMBATALKAN (STOK KEMBALI VIA TRIGGER) ===
if (isset($_GET['batalkan'])) {
    $id_customer = $_GET['batalkan'];
    
    try {
        $stmt = $conn->prepare("UPDATE customer SET status = 'Belum Selesai' WHERE id_customer = ?");
        $stmt->bind_param("s", $id_customer);
        
        if ($stmt->execute()) { 
            header('Location: customers.php'); 
            exit(); 
        } else { 
            $message = "Gagal update status: " . $stmt->error; 
            $message_type = 'message error'; 
        }
    } catch (Exception $e) {
        $message = "Gagal membatalkan status: " . $e->getMessage();
        $message_type = 'message error';
    }
}

// === FITUR 3: MENAMBAH CUSTOMER ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_customer'])) {
    $nama_customer = $_POST['nama_customer'];
    $fk_model = $_POST['fk_model'];
    $fk_layanan = $_POST['fk_layanan'];
    $fk_admin = $_SESSION['admin_id'];

    if (empty($nama_customer) || empty($fk_model)) {
        $message = "Nama customer dan Pilihan Model wajib diisi.";
        $message_type = 'message error';
    } else {
        
        // Validasi Stok Awal (Opsional: Mencegah input jika stok 0, walau stok fisik belum ditarik)
        $stok_aman = true;
        $produk_habis = "";

        if (!empty($fk_layanan)) {
            $sql_cek_stok = "SELECT p.nama_produk, p.stok, dl.jumlah_produk
                             FROM detail_layanan dl
                             JOIN produk p ON dl.fk_produk = p.id_produk
                             WHERE dl.fk_layanan = ?";
            $stmt_cek = $conn->prepare($sql_cek_stok);
            $stmt_cek->bind_param("s", $fk_layanan);
            $stmt_cek->execute();
            $result_stok = $stmt_cek->get_result();
            
            if ($result_stok->num_rows > 0) {
                while ($row = $result_stok->fetch_assoc()) {
                    if ($row['stok'] < $row['jumlah_produk']) {
                        $stok_aman = false;
                        $produk_habis = $row['nama_produk'];
                        break;
                    }
                }
            }
        }
        
        if ($stok_aman == false) {
            $message = "Peringatan: Stok untuk '" . htmlspecialchars($produk_habis) . "' tidak mencukupi. Transaksi tidak dapat dibuat.";
            $message_type = 'message error';
        } else {
            $conn->begin_transaction();
            try {
                // 1. Masukkan customer
                $stmt_cust = $conn->prepare("INSERT INTO customer (nama_customer) VALUES (?)");
                $stmt_cust->bind_param("s", $nama_customer);
                $stmt_cust->execute();
                
                // 2. Ambil ID customer baru
                $result_id = $conn->query("SELECT id_customer FROM customer WHERE DATE(waktu_daftar) = CURDATE() ORDER BY nomor_antrian DESC LIMIT 1");
                $new_customer_id = $result_id->fetch_assoc()['id_customer'];
                
                // 3. Masukkan transaksi jual 
                // (CATATAN: Stok TIDAK berkurang di sini lagi karena trigger lama sudah dihapus)
                $fk_layanan_db = empty($fk_layanan) ? NULL : $fk_layanan;
                $stmt_jual = $conn->prepare("INSERT INTO transaksi_jual (tanggal_jual, fk_customer, fk_admin, fk_model, fk_layanan) VALUES (NOW(), ?, ?, ?, ?)");
                $stmt_jual->bind_param("ssss", $new_customer_id, $fk_admin, $fk_model, $fk_layanan_db);
                $stmt_jual->execute();
                
                $conn->commit();
                $message = "Customer berhasil ditambahkan. Stok akan berkurang saat status 'Selesai'.";
                
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Error: " . $e->getMessage();
                $message_type = 'message error';
            }
        }
    }
}

$models_result = $conn->query("SELECT id_model, nama_model, harga_model FROM model ORDER BY nama_model");
$layanan_result = $conn->query("SELECT id_layanan, nama_layanan, harga_layanan FROM layanan ORDER BY nama_layanan");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Customer | Admin</title>
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
            <h1>Customer</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>

            <div class="form-wrapper">
                <h2>Tambah Customer & Transaksi</h2>
                <form action="customers.php" method="POST">
                    <div>
                        <label for="nama_customer">Nama Customer:</label>
                        <input type="text" id="nama_customer" name="nama_customer" required>
                    </div>
                    <div>
                        <label for="fk_model">Pilih Model Rambut (Wajib):</label>
                        <select id="fk_model" name="fk_model" required>
                            <option value="">-- Pilih Model --</option>
                            <?php
                            if ($models_result->num_rows > 0) {
                                while($row = $models_result->fetch_assoc()) {
                                    echo "<option value='{$row['id_model']}'>{$row['nama_model']} (" . number_format($row['harga_model'], 0, ',', '.') . ")</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="fk_layanan">Pilih Layanan (Opsional):</label>
                        <select id="fk_layanan" name="fk_layanan">
                            <option value="">-- Tidak ada layanan tambahan --</option>
                             <?php
                             $layanan_result->data_seek(0); 
                            if ($layanan_result->num_rows > 0) {
                                while($row = $layanan_result->fetch_assoc()) {
                                    echo "<option value='{$row['id_layanan']}'>{$row['nama_layanan']} (" . number_format($row['harga_layanan'], 0, ',', '.') . ")</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="tambah_customer" class="btn btn-primary">Tambah Customer</button>
                </form>
            </div>

            <h2>Data Customer & Transaksi</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Antrian</th>
                            <th>Nama Customer</th>
                            <th>Waktu Daftar</th>
                            <th>Model</th>
                            <th>Layanan</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT c.*, m.nama_model, l.nama_layanan, tj.total_harga_jual
                                FROM customer c
                                LEFT JOIN transaksi_jual tj ON c.id_customer = tj.fk_customer
                                LEFT JOIN model m ON tj.fk_model = m.id_model
                                LEFT JOIN layanan l ON tj.fk_layanan = l.id_layanan
                                ORDER BY c.status ASC, c.nomor_antrian ASC, c.waktu_daftar DESC
                                LIMIT 100";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . ($row['status'] == 'Belum Selesai' ? '<strong>' . $row['nomor_antrian'] . '</strong>' : '-') . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_customer']) . "</td>";
                                echo "<td>" . $row['waktu_daftar'] . "</td>";
                                
                                if ($row['total_harga_jual'] !== null) {
                                    echo "<td>" . htmlspecialchars($row['nama_model']) . "</td>";
                                    echo "<td>" . ($row['nama_layanan'] ? htmlspecialchars($row['nama_layanan']) : '-') . "</td>";
                                    echo "<td>" . number_format($row['total_harga_jual'], 0, ',', '.') . "</td>";
                                } else {
                                    echo "<td colspan='3' class='no-data' style='text-align:left; padding-left:15px;'>Belum ada transaksi</td>";
                                }
                                
                                if ($row['status'] == 'Belum Selesai') {
                                    echo "<td><span class='status-belum'>" . $row['status'] . "</span></td>";
                                } else {
                                    echo "<td><span class='status-selesai'>" . $row['status'] . "</span></td>";
                                }
                                
                                echo "<td><div class='table-actions'>";
                                if ($row['status'] == 'Belum Selesai') {
                                    echo "<a href='customers.php?selesai=" . $row['id_customer'] . "' class='btn btn-success btn-sm' onclick='return confirm(\"Tandai Selesai? Stok produk akan dikurangi.\")'>Selesai</a>";
                                } else {
                                    echo "<a href='customers.php?batalkan=" . $row['id_customer'] . "' class='btn btn-warning btn-sm' onclick='return confirm(\"Batalkan status? Stok produk akan dikembalikan.\")'>Batalkan</a>";
                                }
                                echo "<a href='customers.php?hapus=" . $row['id_customer'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"ANDA YAKIN? Menghapus customer juga akan menghapus data transaksinya.\")'>Hapus</a>";
                                echo "</div></td>";
                                
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='no-data'>Belum ada data customer.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div> 
    </div> 

</body>
</html>