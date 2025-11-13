<?php 
include 'session_check.php'; 
include 'db.php'; 

$active_page = basename($_SERVER['PHP_SELF']);

// --- Data untuk Kartu ---
$q_cust_pending = $conn->query("SELECT COUNT(id_customer) as total FROM customer WHERE status = 'Belum Selesai'");
$antrian_pending = $q_cust_pending->fetch_assoc()['total'];

$q_cust_selesai = $conn->query("SELECT COUNT(id_customer) as total FROM customer WHERE status = 'Selesai'");
$antrian_selesai = $q_cust_selesai->fetch_assoc()['total'];

$q_pendapatan_all = $conn->query("SELECT SUM(total_harga_jual) as total FROM transaksi_jual");
$total_pendapatan = $q_pendapatan_all->fetch_assoc()['total'];


// --- Data untuk Grafik (7 Hari Terakhir) ---
$labels = [];
$data = [];
$data_raw = []; 

$sql_grafik = "SELECT 
                   DATE(tanggal_jual) as tanggal, 
                   SUM(total_harga_jual) as total
               FROM 
                   transaksi_jual
               WHERE 
                   tanggal_jual >= CURDATE() - INTERVAL 6 DAY
               GROUP BY 
                   DATE(tanggal_jual)
               ORDER BY 
                   tanggal ASC";
$result_grafik = $conn->query($sql_grafik);
while($row = $result_grafik->fetch_assoc()) {
    $data_raw[$row['tanggal']] = $row['total'];
}

for ($i = 6; $i >= 0; $i--) {
    $tanggal = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($tanggal)); 
    
    if (isset($data_raw[$tanggal])) {
        $data[] = $data_raw[$tanggal];
    } else {
        $data[] = 0; 
    }
}

$labels_js = json_encode($labels);
$data_js = json_encode($data);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <h1>Dashboard</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <div class="cards">
                <div class="card">
                    <h3>Total Antrian (Belum Selesai)</h3>
                    <p><?php echo $antrian_pending; ?></p>
                </div>
                <div class="card">
                    <h3>Total Antrian (Selesai)</h3>
                    <p><?php echo $antrian_selesai; ?></p>
                </div>
                <div class="card">
                    <h3>Total Pendapatan (Semua)</h3>
                    <p>Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></p>
                </div>
            </div>

            <div class="chart-wrapper">
                <h2>Pendapatan 7 Hari Terakhir</h2>
                <canvas id="myChart"></canvas>
            </div>

        </div> 
    </div> 

    <script>
        const ctx = document.getElementById('myChart');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $labels_js; ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?php echo $data_js; ?>,
                    backgroundColor: 'rgba(33, 37, 41, 0.5)',  /* WARNA BARU */
                    borderColor: 'rgba(33, 37, 41, 1)',    /* WARNA BARU */
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>