<?php
include 'session_check.php';
include 'db.php';

$active_page = basename($_SERVER['PHP_SELF']);
$message = '';
$message_type = 'message';

// === FITUR HAPUS MODEL ===
if (isset($_GET['hapus'])) {
    $id_model_hapus = $_GET['hapus'];
    
    $stmt_img = $conn->prepare("SELECT gambar_model FROM model WHERE id_model = ?");
    $stmt_img->bind_param("s", $id_model_hapus);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    if ($result_img->num_rows > 0) {
        $row_img = $result_img->fetch_assoc();
        $gambar_path = "uploads/" . $row_img['gambar_model']; // Tambahkan 'uploads/'
        
        if (!empty($row_img['gambar_model']) && file_exists($gambar_path)) {
            unlink($gambar_path);
        }
    }
    
    $stmt_hapus = $conn->prepare("DELETE FROM model WHERE id_model = ?");
    $stmt_hapus->bind_param("s", $id_model_hapus);
    
    if ($stmt_hapus->execute()) {
        $message = "Model berhasil dihapus.";
    } else {
        $message = "Gagal menghapus model. Error: " . $conn->error;
        $message_type = 'message error';
    }
}

// === LOGIKA TAMBAH MODEL ===
if (isset($_POST['submit'])) {
    $nama_model = $_POST['nama_model'];
    $harga_model = $_POST['harga_model'];
    $deskripsi_model = $_POST['deskripsi_model'];
    $gambar_path_db = "";
    if (isset($_FILES['gambar_model']) && $_FILES['gambar_model']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir) && !mkdir($target_dir, 0777, true)) {
             $message = "Error: Gagal membuat folder uploads.";
             $message_type = 'message error';
        }
        if (empty($message)) {
            $file_name = basename($_FILES["gambar_model"]["name"]);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_name_no_ext = pathinfo($file_name, PATHINFO_FILENAME);
            $safe_file_name = preg_replace("/[^A-Za-z0-9_-]/", "", $file_name_no_ext);
            $unique_file_name = $safe_file_name . '-' . time() . '.' . $file_ext;
            $target_file_path = $target_dir . $unique_file_name;
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($file_ext, $allowed_types)) {
                if (move_uploaded_file($_FILES["gambar_model"]["tmp_name"], $target_file_path)) {
                    // --- PERUBAHAN 1 DI SINI ---
                    // Kita simpan nama filenya saja, bukan seluruh path
                    $gambar_path_db = $unique_file_name;
                    // --- AKHIR PERUBAHAN 1 ---
                } else { $message = "Error: Gagal memindahkan file."; $message_type = 'message error'; }
            } else { $message = "Error: Tipe file tidak diizinkan."; $message_type = 'message error'; }
        }
    }
    if (empty($message)) {
        $sql = "INSERT INTO model (nama_model, harga_model, deskripsi_model, gambar_model) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdss", $nama_model, $harga_model, $deskripsi_model, $gambar_path_db);
        if ($stmt->execute()) { $message = "Model baru berhasil ditambahkan."; } 
        else { $message = "Error: " . $stmt->error; $message_type = 'message error'; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Model | Admin</title>
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
            <h1>Kelola Model</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>

            <div class="form-wrapper">
                <h2>Tambah Model Baru</h2>
                <form action="model.php" method="POST" enctype="multipart/form-data">
                    <div>
                        <label>Nama Model:</label>
                        <input type="text" name="nama_model" required>
                    </div>
                    <div>
                        <label>Harga:</label>
                        <input type="number" step="0.01" name="harga_model" required>
                    </div>
                    <div>
                        <label>Deskripsi:</label>
                        <textarea name="deskripsi_model" required></textarea>
                    </div>
                    <div>
                        <label>Gambar Model:</label>
                        <input type="file" name="gambar_model" accept="image/*">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Tambah Model</button>
                </form>
            </div>

            <h2>Daftar Model Saat Ini</h2>
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
                        $result = $conn->query("SELECT * FROM model ORDER BY nama_model");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>";
                                // --- PERUBAHAN 2 DI SINI ---
                                // Tambahkan path "uploads/" secara manual saat menampilkan gambar
                                $gambar_url = "uploads/" . htmlspecialchars($row['gambar_model']);
                                if (!empty($row['gambar_model']) && file_exists($gambar_url)) {
                                    echo "<img src='" . $gambar_url . "' alt='" . htmlspecialchars($row['nama_model']) . "' class='table-image'>";
                                } else {
                                    echo "<em>-</em>";
                                }
                                // --- AKHIR PERUBAHAN 2 ---
                                echo "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_model']) . "</td>";
                                echo "<td>" . number_format($row['harga_model'], 0, ',', '.') . "</td>";
                                echo "<td>" . htmlspecialchars($row['deskripsi_model']) . "</td>";
                                echo "<td><div class='table-actions'>";
                                echo "<a href='model_edit.php?id=" . $row['id_model'] . "' class='btn btn-warning btn-sm'>Edit</a>";
                                echo "<a href='model.php?hapus=" . $row['id_model'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"ANDA YAKIN? Menghapus model ini akan menghapus data transaksi terkait.\")'>Hapus</a>";
                                echo "</div></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='no-data'>Belum ada data model.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div> 
    </div> 

</body>
</html>