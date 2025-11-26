<?php
// Pastikan path db.php benar
include 'file/koneksi.php'; 

$message = '';
$message_type = '';

// === PROSES FORMULIR ===
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_customer = trim($_POST['nama_customer']);
    $fk_model = $_POST['fk_model'];
    // Jika layanan tidak dipilih/habis, nilainya NULL
    $fk_layanan = (!empty($_POST['fk_layanan']) && $_POST['fk_layanan'] !== 'disabled') ? $_POST['fk_layanan'] : NULL;
    
    // --- PERBAIKAN UTAMA: AMBIL ID ADMIN OTOMATIS ---
    // Kita ambil satu admin saja untuk mencatat transaksi (agar tidak error foreign key)
    $q_admin = $conn->query("SELECT id_admin FROM admin LIMIT 1");
    if ($row_admin = $q_admin->fetch_assoc()) {
        $fk_admin = $row_admin['id_admin'];
    } else {
        die("Error Sistem: Tidak ada data Admin di database. Harap tambahkan data admin terlebih dahulu.");
    }

    if (empty($nama_customer) || empty($fk_model)) {
        $message = "Mohon isi Nama dan Pilih Model rambut.";
        $message_type = 'alert-error';
    } else {
        // Gunakan Transaksi Database
        $conn->begin_transaction();
        try {
            // 1. Insert Customer
            $stmt_cust = $conn->prepare("INSERT INTO customer (nama_customer) VALUES (?)");
            $stmt_cust->bind_param("s", $nama_customer);
            
            if (!$stmt_cust->execute()) {
                throw new Exception("Gagal menyimpan data customer: " . $stmt_cust->error);
            }
            
            // 2. Ambil ID Customer & No Antrian
            $result_id = $conn->query("SELECT id_customer, nomor_antrian FROM customer WHERE DATE(waktu_daftar) = CURDATE() ORDER BY nomor_antrian DESC LIMIT 1");
            
            if ($result_id && $result_id->num_rows > 0) {
                $row_cust = $result_id->fetch_assoc();
                $new_customer_id = $row_cust['id_customer'];
                $no_antrian = $row_cust['nomor_antrian'];
            } else {
                throw new Exception("Gagal mengambil antrian.");
            }

            // 3. Insert Transaksi Jual
            $stmt_jual = $conn->prepare("INSERT INTO transaksi_jual (tanggal_jual, fk_customer, fk_admin, fk_model, fk_layanan) VALUES (NOW(), ?, ?, ?, ?)");
            $stmt_jual->bind_param("ssss", $new_customer_id, $fk_admin, $fk_model, $fk_layanan);
            
            if (!$stmt_jual->execute()) {
                throw new Exception("Gagal menyimpan transaksi: " . $stmt_jual->error);
            }

            // 4. Commit jika sukses
            $conn->commit();
            
            // --- REVISI: REDIRECT KE HALAMAN SUKSES ---
            header("Location: sukses.php?antrian=" . $no_antrian . "&nama=" . urlencode($nama_customer));
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = $e->getMessage();
            // Cek pesan error dari trigger MySQL
            if (strpos($error_msg, 'Stok tidak mencukupi') !== false) {
                $message = "Maaf, stok produk untuk layanan tersebut baru saja habis. Silakan pilih layanan lain.";
            } else {
                $message = "Terjadi kesalahan: " . $error_msg;
            }
            $message_type = 'alert-error';
        }
    }
}

// === AMBIL DATA MODEL ===
$models = $conn->query("SELECT * FROM model ORDER BY nama_model ASC");

// === AMBIL DATA LAYANAN ===
$layanan = $conn->query("SELECT * FROM layanan ORDER BY nama_layanan ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="header">
        <img src="file/logo.png" alt="Pak To Barbershop Logo" class="logo">
        <h1 class="title">Potong Rambut Pak To</h1>
        <p class="subtitle">Barber Lokal, Gaya Profesional</p>
    </div>

    <div class="container">
        
        <?php if ($message): ?>
            <div class="alert <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            
            <div class="section-title">Data Diri</div>
            <div class="input-group">
                <input type="text" id="nama" name="nama_customer" class="form-control" placeholder="Masukkan nama Anda..." required>
            </div>

            <div class="section-title">Pilih Model</div>
            <div class="selection-grid">
                <?php while($row = $models->fetch_assoc()): ?>
                    <label class="option-card">
                        <input type="radio" name="fk_model" value="<?php echo $row['id_model']; ?>" required>
                        
                        <div class="card-content">
                            <div class="card-img-wrapper">
                                <img src="file/asset/<?php echo $row['gambar_model']; ?>" 
                                     alt="<?php echo $row['nama_model']; ?>" 
                                     class="card-img"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=No+Image';">
                            </div>
                            <div class="card-details">
                                <span class="card-name"><?php echo $row['nama_model']; ?></span>
                                <span class="card-price">Rp <?php echo number_format($row['harga_model'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </label>
                <?php endwhile; ?>
            </div>

            <div class="section-title">Pilih Layanan</div>
            <div class="selection-grid">
                
                <label class="option-card">
                    <input type="radio" name="fk_layanan" value="" checked>
                    <div class="card-content">
                        <div class="card-img-wrapper" style="background: #333; display:flex; align-items:center; justify-content:center;">
                            <span style="font-size: 3rem; color: #555;">✕</span>
                        </div>
                        <div class="card-details">
                            <span class="card-name">Tanpa Layanan</span>
                            <span class="card-price">Rp 0</span>
                        </div>
                    </div>
                </label>

                <?php 
                while($row = $layanan->fetch_assoc()): 
                    // --- LOGIKA CEK STOK ---
                    $id_layanan_cek = $row['id_layanan'];
                    $is_habis = false;

                    // Cek apakah ada produk di layanan ini yang stoknya kurang dari yang dibutuhkan
                    $cek_stok = $conn->query("
                        SELECT COUNT(*) as total_kurang 
                        FROM detail_layanan dl
                        JOIN produk p ON dl.fk_produk = p.id_produk
                        WHERE dl.fk_layanan = '$id_layanan_cek' 
                        AND p.stok < dl.jumlah_produk
                    ");
                    
                    $data_stok = $cek_stok->fetch_assoc();
                    if ($data_stok['total_kurang'] > 0) {
                        $is_habis = true;
                    }
                ?>
                    <label class="option-card <?php echo $is_habis ? 'disabled' : ''; ?>">
                        <input type="radio" name="fk_layanan" value="<?php echo $is_habis ? 'disabled' : $row['id_layanan']; ?>" <?php echo $is_habis ? 'disabled' : ''; ?>>
                        
                        <div class="card-content">
                            <div class="card-img-wrapper">
                                <?php if($is_habis): ?>
                                    <div class="badge-habis">HABIS</div>
                                <?php endif; ?>
                                <img src="file/asset/<?php echo $row['gambar_layanan']; ?>" 
                                     alt="<?php echo $row['nama_layanan']; ?>" 
                                     class="card-img"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=No+Image';">
                            </div>
                            <div class="card-details">
                                <span class="card-name"><?php echo $row['nama_layanan']; ?></span>
                                <span class="card-price">Rp <?php echo number_format($row['harga_layanan'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </label>
                <?php endwhile; ?>
            </div>

            <button type="submit" class="btn-submit">Reservasi Sekarang</button>

        </form>
    </div>

</body>
</html>