<?php
include 'session_check.php';
include '../include/db.php';

$active_page = basename($_SERVER['PHP_SELF']);

// 1. Inisialisasi Variable
$tanggal_mulai = date('Y-m-01'); 
$tanggal_selesai = date('Y-m-d'); 
$jenis_laporan = 'penjualan'; // Default

$laporan_data = [];
$total_nominal = 0; // Bisa jadi Total Pendapatan atau Total Pengeluaran

// 2. Tangkap Input Filter
if (isset($_GET['submit_filter'])) {
    $tanggal_mulai = $_GET['tanggal_mulai'];
    $tanggal_selesai = $_GET['tanggal_selesai'];
    $jenis_laporan = $_GET['jenis_laporan'];
}

// Format tanggal untuk query (sampai detik terakhir)
$tgl_start_query = $tanggal_mulai . ' 00:00:00';
$tgl_end_query   = $tanggal_selesai . ' 23:59:59';

// 3. Logika Query Berdasarkan Jenis Laporan
switch ($jenis_laporan) {
    case 'pembelian':
        // === QUERY PEMBELIAN STOK ===
        // Mengambil detail barang, harga satuan saat beli, dan sumbernya
        $sql = "SELECT db.id_detail_beli, tb.tanggal_beli, 
                       COALESCE(s.nama_supplier, tb.sumber_lain, 'Lokal/Ad-hoc') AS sumber,
                       p.nama_produk, db.jumlah_beli, db.harga_beli_satuan
                FROM detail_beli db
                JOIN transaksi_beli tb ON db.fk_beli = tb.id_beli
                JOIN produk p ON db.fk_produk = p.id_produk
                LEFT JOIN supplier s ON tb.fk_supplier = s.id_supplier
                WHERE tb.tanggal_beli BETWEEN ? AND ?
                ORDER BY tb.tanggal_beli DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $tgl_start_query, $tgl_end_query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Hitung subtotal per baris
            $row['subtotal'] = $row['jumlah_beli'] * $row['harga_beli_satuan'];
            $laporan_data[] = $row;
            $total_nominal += $row['subtotal'];
        }
        $judul_laporan = "Laporan Pembelian Stok (Pengeluaran)";
        break;

    case 'customer':
        // === QUERY PERFORMA CUSTOMER ===
        // Melihat siapa customer yang datang di periode ini & total belanja mereka
        $sql = "SELECT c.nama_customer, 
                       COUNT(tj.id_jual) as jumlah_kunjungan, 
                       SUM(tj.total_harga_jual) as total_belanja
                FROM customer c
                JOIN transaksi_jual tj ON c.id_customer = tj.fk_customer
                WHERE tj.tanggal_jual BETWEEN ? AND ?
                GROUP BY c.id_customer
                ORDER BY total_belanja DESC"; // Urutkan dari yang paling "sultan"
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $tgl_start_query, $tgl_end_query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $laporan_data[] = $row;
            // Untuk customer, total nominal bisa berarti total omset dari customer yang terdata
            $total_nominal += $row['total_belanja'];
        }
        $judul_laporan = "Laporan Aktivitas Customer";
        break;

    default: // 'penjualan'
        // === QUERY PENJUALAN (DEFAULT) ===
        $sql = "SELECT tj.tanggal_jual, tj.id_jual, c.nama_customer, m.nama_model, l.nama_layanan, tj.total_harga_jual
                FROM transaksi_jual tj
                JOIN customer c ON tj.fk_customer = c.id_customer
                JOIN model m ON tj.fk_model = m.id_model
                LEFT JOIN layanan l ON tj.fk_layanan = l.id_layanan
                WHERE tj.tanggal_jual BETWEEN ? AND ?
                ORDER BY tj.tanggal_jual ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $tgl_start_query, $tgl_end_query);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $laporan_data[] = $row;
            $total_nominal += $row['total_harga_jual'];
        }
        $judul_laporan = "Laporan Penjualan (Pendapatan)";
        break;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Khusus Print agar rapi */
        @media print {
            .sidebar, .topbar, .form-wrapper, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; }
            .content-wrapper { padding: 0 !important; }
            .laporan-hasil { box-shadow: none !important; border: none !important; }
            table { width: 100% !important; border: 1px solid #000; }
            th, td { border: 1px solid #000 !important; padding: 5px !important; }
        }
    </style>
</head>
<body>
    
    <div class="sidebar">
        <div class="sidebar-logo"><img src="logo.png" alt="Barbershop Logo"></div>
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
            <h1>Pusat Laporan</h1>
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
                        <label>Jenis Laporan:</label>
                        <select name="jenis_laporan" style="width: 100%; padding: 10px;">
                            <option value="penjualan" <?php echo ($jenis_laporan == 'penjualan') ? 'selected' : ''; ?>>Laporan Penjualan (Pendapatan)</option>
                            <option value="pembelian" <?php echo ($jenis_laporan == 'pembelian') ? 'selected' : ''; ?>>Laporan Pembelian Stok (Pengeluaran)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dari Tanggal:</label>
                        <input type="date" name="tanggal_mulai" value="<?php echo $tanggal_mulai; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Sampai Tanggal:</label>
                        <input type="date" name="tanggal_selesai" value="<?php echo $tanggal_selesai; ?>" required>
                    </div>
                    <div class="form-group-submit">
                        <button type="submit" name="submit_filter" value="1" class="btn btn-primary">Tampilkan</button>
                        <button type="button" onclick="window.print()" class="btn btn-secondary no-print">🖨️ Cetak PDF</button>
                    </div>
                </form>
            </div>

            <div class="laporan-hasil">
                <h2 style="text-align: center; margin-bottom: 5px;"><?php echo $judul_laporan; ?></h2>
                <p class="laporan-periode" style="text-align: center; margin-top: 0;">
                    Periode: <strong><?php echo date('d/m/Y', strtotime($tanggal_mulai)); ?></strong> s/d <strong><?php echo date('d/m/Y', strtotime($tanggal_selesai)); ?></strong>
                </p>
                <hr>
                
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <?php if ($jenis_laporan == 'penjualan'): ?>
                                    <th>Tanggal</th>
                                    <th>No Nota</th>
                                    <th>Customer</th>
                                    <th>Item (Model+Layanan)</th>
                                    <th style="text-align: right;">Total (Rp)</th>
                                
                                <?php elseif ($jenis_laporan == 'pembelian'): ?>
                                    <th>Tanggal</th>
                                    <th>Supplier / Sumber</th>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Harga @</th>
                                    <th style="text-align: right;">Subtotal (Rp)</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($laporan_data) > 0): ?>
                                <?php foreach ($laporan_data as $row): ?>
                                    <tr>
                                        <?php if ($jenis_laporan == 'penjualan'): ?>
                                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_jual'])); ?></td>
                                            <td><?php echo $row['id_jual']; ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_customer']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_model']); ?><?php echo $row['nama_layanan'] ? ' + '.$row['nama_layanan'] : ''; ?></td>
                                            <td style="text-align: right;"><?php echo number_format($row['total_harga_jual'], 0, ',', '.'); ?></td>
                                        
                                        <?php elseif ($jenis_laporan == 'pembelian'): ?>
                                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_beli'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['sumber']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                            <td><?php echo $row['jumlah_beli']; ?></td>
                                            <td><?php echo number_format($row['harga_beli_satuan'], 0, ',', '.'); ?></td>
                                            <td style="text-align: right;"><?php echo number_format($row['subtotal'], 0, ',', '.'); ?></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-data" style="text-align:center; padding: 20px;">
                                        Tidak ada data transaksi pada periode dan kategori ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="laporan-total" style="background: #f1f1f1;">
                                <?php 
                                    // Hitung colspan agar rapi
                                    $colspan = ($jenis_laporan == 'customer') ? 2 : 
                                               (($jenis_laporan == 'pembelian') ? 5 : 4); 
                                ?>
                                <th colspan="<?php echo $colspan; ?>" style="text-align: right; font-size: 1.1em;">
                                    <?php echo ($jenis_laporan == 'pembelian') ? 'TOTAL PENGELUARAN:' : 'TOTAL PENDAPATAN:'; ?>
                                </th>
                                <th style="text-align: right; font-size: 1.1em;">
                                    Rp <?php echo number_format($total_nominal, 0, ',', '.'); ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div> 
    </div> 
</body>
</html>