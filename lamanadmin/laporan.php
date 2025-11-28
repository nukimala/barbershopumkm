<?php
include 'session_check.php';
include 'db.php';

$active_page = basename($_SERVER['PHP_SELF']);

// Set default tanggal (Awal bulan ini sampai Hari ini)
$tanggal_mulai = date('Y-m-01'); 
$tanggal_selesai = date('Y-m-d'); 

$laporan_data = [];
$total_pendapatan = 0;

// Jika user menekan tombol "Tampilkan" (Filter Tanggal)
if (isset($_GET['submit_tanggal'])) {
    $tanggal_mulai = $_GET['tanggal_mulai'];
    $tanggal_selesai = $_GET['tanggal_selesai'];
}

// --- QUERY DATA TRANSAKSI ---
// Kita tambahkan ' 23:59:59' ke tanggal selesai agar mencakup transaksi sampai detik terakhir hari itu
$tanggal_selesai_query = $tanggal_selesai . ' 23:59:59';

$query_laporan = $conn->prepare(
    "SELECT tj.tanggal_jual, c.nama_customer, m.nama_model, l.nama_layanan, tj.total_harga_jual
     FROM transaksi_jual tj
     JOIN customer c ON tj.fk_customer = c.id_customer
     JOIN model m ON tj.fk_model = m.id_model
     LEFT JOIN layanan l ON tj.fk_layanan = l.id_layanan
     WHERE tj.tanggal_jual BETWEEN ? AND ?
     ORDER BY tj.tanggal_jual ASC"
);

$query_laporan->bind_param("ss", $tanggal_mulai, $tanggal_selesai_query);
$query_laporan->execute();
$result_laporan = $query_laporan->get_result();

if ($result_laporan->num_rows > 0) {
    while ($row = $result_laporan->fetch_assoc()) {
        $laporan_data[] = $row;
    }
}

// --- QUERY TOTAL PENDAPATAN ---
$query_total = $conn->prepare(
    "SELECT SUM(total_harga_jual) as total
     FROM transaksi_jual
     WHERE tanggal_jual BETWEEN ? AND ?"
);
$query_total->bind_param("ss", $tanggal_mulai, $tanggal_selesai_query);
$query_total->execute();
$total_pendapatan = $query_total->get_result()->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan | Admin</title>
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
            <h1>Laporan</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <div class="form-wrapper">
                <h2>Filter Laporan</h2>
                <form action="laporan.php" method="GET" class="filter-form">
                    <div class="form-group">
                        <label for="tanggal_mulai">Tanggal Mulai:</label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo $tanggal_mulai; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_selesai">Tanggal Selesai:</label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo $tanggal_selesai; ?>" required>
                    </div>
                    <div class="form-group-submit">
                        <button type="submit" name="submit_tanggal" value="1" class="btn btn-primary">Tampilkan</button>
                        <button type="button" onclick="window.print()" class="btn btn-secondary">Cetak Laporan</button>
                    </div>
                </form>
            </div>

            <div class="laporan-hasil">
                <h2>Laporan</h2>
                <p class="laporan-periode">Periode: <strong><?php echo date('d M Y', strtotime($tanggal_mulai)); ?></strong> s/d <strong><?php echo date('d M Y', strtotime($tanggal_selesai)); ?></strong></p>
                
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Customer</th>
                                <th>Model</th>
                                <th>Layanan</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($laporan_data) > 0): ?>
                                <?php foreach ($laporan_data as $data): ?>
                                    <tr>
                                        <td><?php echo date('d M Y, H:i', strtotime($data['tanggal_jual'])); ?></td>
                                        <td><?php echo htmlspecialchars($data['nama_customer']); ?></td>
                                        <td><?php echo htmlspecialchars($data['nama_model']); ?></td>
                                        <td><?php echo htmlspecialchars($data['nama_layanan'] ?? '-'); ?></td>
                                        <td style="text-align: right;">Rp <?php echo number_format($data['total_harga_jual'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-data">Tidak ada data transaksi pada periode ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="laporan-total">
                                <th colspan="4" style="text-align: right; font-size: 1.1em;">TOTAL PENDAPATAN</th>
                                <th style="text-align: right; font-size: 1.1em;">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div> 
    </div> 

</body>
</html>