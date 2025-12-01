<?php
include 'session_check.php';
include '../include/db.php';
$active_page = basename($_SERVER['PHP_SELF']);
$message = '';
$message_type = 'message';

// === FITUR HAPUS PESAN ===
if (isset($_GET['hapus'])) {
    $id_pesan_hapus = $_GET['hapus'];
    $stmt_hapus = $conn->prepare("DELETE FROM pesan WHERE id_pesan = ?");
    $stmt_hapus->bind_param("s", $id_pesan_hapus);
    
    if ($stmt_hapus->execute()) {
        $message = "Pesan berhasil dihapus.";
    } else {
        $message = "Gagal menghapus pesan: " . $conn->error;
        $message_type = 'message error';
    }
}

// === FITUR BARU: UPDATE KATEGORI PESAN (TAMPILKAN) ===
if (isset($_GET['tampilkan'])) {
    $id_pesan = $_GET['tampilkan'];
    $stmt_update = $conn->prepare("UPDATE pesan SET kategori_pesan = 'Ditampilkan' WHERE id_pesan = ?");
    $stmt_update->bind_param("s", $id_pesan);
    if ($stmt_update->execute()) {
        header('Location: pesan.php'); // Refresh halaman
        exit();
    }
}

// === FITUR BARU: UPDATE KATEGORI PESAN (SEMBUNYIKAN) ===
if (isset($_GET['sembunyikan'])) {
    $id_pesan = $_GET['sembunyikan'];
    $stmt_update = $conn->prepare("UPDATE pesan SET kategori_pesan = 'Tidak Ditampilkan' WHERE id_pesan = ?");
    $stmt_update->bind_param("s", $id_pesan);
    if ($stmt_update->execute()) {
        header('Location: pesan.php'); // Refresh halaman
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesan Masuk | Admin</title>
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
            <h1>Pesan Masuk</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>
            
            <h2>Daftar Pesan Masuk</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu Kirim</th>
                            <th>Nama Pengirim</th>
                            <th>Isi Pesan</th>
                            <th>Kategori</th> <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM pesan ORDER BY waktu_kirim DESC");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['waktu_kirim'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_pengirim']) . "</td>";
                                echo "<td style='white-space: pre-wrap;'>" . htmlspecialchars($row['isi_pesan']) . "</td>";
                                
                                // --- TAMPILKAN KATEGORI ---
                                echo "<td>";
                                if ($row['kategori_pesan'] == 'Ditampilkan') {
                                    echo "<span class='status-selesai'>" . $row['kategori_pesan'] . "</span>";
                                } else {
                                    echo "<span class='status-belum'>" . $row['kategori_pesan'] . "</span>";
                                }
                                echo "</td>";
                                // --- AKHIR TAMPILKAN KATEGORI ---
                                
                                // --- TOMBOL AKSI BARU ---
                                echo "<td><div class='table-actions'>";
                                if ($row['kategori_pesan'] == 'Tidak Ditampilkan') {
                                    echo "<a href='pesan.php?tampilkan=" . $row['id_pesan'] . "' class='btn btn-success btn-sm' onclick='return confirm(\"Tampilkan pesan ini?\")'>Tampilkan</a>";
                                } else {
                                    echo "<a href='pesan.php?sembunyikan=" . $row['id_pesan'] . "' class='btn btn-warning btn-sm' onclick='return confirm(\"Sembunyikan pesan ini?\")'>Sembunyikan</a>";
                                }
                                echo "<a href='pesan.php?hapus=" . $row['id_pesan'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Hapus pesan ini?\")'>Hapus</a>";
                                echo "</div></td>";
                                // --- AKHIR TOMBOL AKSI ---
                                
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='no-data'>Tidak ada pesan masuk.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div> 
    </div> 

</body>
</html>