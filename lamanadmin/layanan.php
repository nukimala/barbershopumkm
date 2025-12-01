<?php
include 'session_check.php';
include 'db.php';

$active_page = basename($_SERVER['PHP_SELF']);
$message = '';
$message_type = 'message';

// Tentukan direktori penyimpanan (Relative path dari folder admin)
$target_dir = "../assets/img/layanan/";

// === FITUR HAPUS LAYANAN ===
if (isset($_GET['hapus'])) {
    $id_layanan_hapus = $_GET['hapus'];
    
    // 1. Ambil nama file dari DB
    $stmt_img = $conn->prepare("SELECT gambar_layanan FROM layanan WHERE id_layanan = ?");
    $stmt_img->bind_param("s", $id_layanan_hapus);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    
    if ($result_img->num_rows > 0) {
        $row_img = $result_img->fetch_assoc();
        $nama_file = $row_img['gambar_layanan'];
        
        // 2. Hapus file dari folder assets
        $file_path = $target_dir . $nama_file;
        if (!empty($nama_file) && file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // 3. Hapus data dari database
    $stmt_hapus = $conn->prepare("DELETE FROM layanan WHERE id_layanan = ?");
    $stmt_hapus->bind_param("s", $id_layanan_hapus);
    
    if ($stmt_hapus->execute()) {
        $message = "Layanan berhasil dihapus.";
    } else {
        $message = "Gagal menghapus layanan. Error: " . $conn->error;
        $message_type = 'message error';
    }
}

// === LOGIKA TAMBAH LAYANAN ===
if (isset($_POST['submit'])) {
    $nama_layanan = $_POST['nama_layanan'];
    $harga_layanan = $_POST['harga_layanan'];
    $deskripsi_layanan = $_POST['deskripsi_layanan'];
    $gambar_path_db = "";
    
    if (isset($_FILES['gambar_layanan']) && $_FILES['gambar_layanan']['error'] == 0) {
        
        // Buat nama file unik
        $file_name = basename($_FILES["gambar_layanan"]["name"]);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_name_no_ext = pathinfo($file_name, PATHINFO_FILENAME);
        $safe_file_name = preg_replace("/[^A-Za-z0-9_-]/", "", $file_name_no_ext);
        $unique_file_name = $safe_file_name . '-' . time() . '.' . $file_ext;
        
        $target_file_path = $target_dir . $unique_file_name;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_types)) {
            // Buat direktori jika belum ada
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

            // Pindahkan file langsung ke folder assets
            if (move_uploaded_file($_FILES["gambar_layanan"]["tmp_name"], $target_file_path)) {
                $gambar_path_db = $unique_file_name; // Simpan nama file saja
            } else { 
                $message = "Error: Gagal memindahkan file ke folder assets."; 
                $message_type = 'message error'; 
            }
        } else { 
            $message = "Error: Tipe file tidak diizinkan (Hanya JPG, PNG, GIF, WebP)."; 
            $message_type = 'message error'; 
        }
    }
    
    // Masukkan ke database jika tidak ada error
    if (empty($message)) {
        $sql = "INSERT INTO layanan (nama_layanan, harga_layanan, deskripsi_layanan, gambar_layanan) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdss", $nama_layanan, $harga_layanan, $deskripsi_layanan, $gambar_path_db);
        if ($stmt->execute()) { 
            $message = "Layanan baru berhasil ditambahkan."; 
        } else { 
            $message = "Error: Gagal menyimpan ke database. " . $stmt->error; 
            $message_type = 'message error'; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Layanan | Admin</title>
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
            <h1>Kelola Layanan</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>

            <div class="form-wrapper">
                <h2>Tambah Layanan Baru</h2>
                <form action="layanan.php" method="POST" enctype="multipart/form-data">
                    <div>
                        <label>Nama Layanan:</label>
                        <input type="text" name="nama_layanan" required>
                    </div>
                    <div>
                        <label>Harga:</label>
                        <input type="number" step="0.01" name="harga_layanan" required>
                    </div>
                    <div>
                        <label>Deskripsi:</label>
                        <textarea name="deskripsi_layanan" required></textarea>
                    </div>
                    <div>
                        <label>Gambar Layanan:</label>
                        <input type="file" name="gambar_layanan" accept="image/*">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Tambah Layanan</button>
                </form>
            </div>

            <h2>Daftar Layanan Saat Ini</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM layanan ORDER BY nama_layanan");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>";
                                // Mengambil gambar langsung dari folder assets
                                $gambar_url = $target_dir . htmlspecialchars($row['gambar_layanan']);
                                if (!empty($row['gambar_layanan']) && file_exists($gambar_url)) {
                                    echo "<img src='" . $gambar_url . "' alt='" . htmlspecialchars($row['nama_layanan']) . "' class='table-image'>";
                                } else {
                                    echo "<em>-</em>";
                                }
                                echo "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_layanan']) . "</td>";
                                echo "<td>" . number_format($row['harga_layanan'], 0, ',', '.') . "</td>";
                                echo "<td>" . htmlspecialchars($row['deskripsi_layanan']) . "</td>";
                                echo "<td><div class='table-actions'>";
                                echo "<a href='layanan_edit.php?id=" . $row['id_layanan'] . "' class='btn btn-warning btn-sm'>Edit</a>";
                                echo "<a href='layanan.php?hapus=" . $row['id_layanan'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Hapus layanan ini?\")'>Hapus</a>";
                                echo "</div></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='no-data'>Belum ada data layanan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div> 
    </div> 

</body>
</html>