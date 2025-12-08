<?php 
include 'session_check.php'; 
include '../include/db.php'; 

$active_page = basename($_SERVER['PHP_SELF']);

// --- DATA UNTUK KARTU (STATISTIK) ---

// 1. Antrian Pending (Sisa antrian saat ini)
$q_cust_pending = $conn->query("SELECT COUNT(id_customer) as total FROM customer WHERE status = 'Belum Selesai'");
$antrian_pending = $q_cust_pending->fetch_assoc()['total'];

// 2. Total Customer (7 HARI TERAKHIR) - REVISI
// Menghitung jumlah transaksi dalam kurun waktu 7 hari ke belakang
$q_cust_7hari = $conn->query("SELECT COUNT(id_jual) as total FROM transaksi_jual WHERE DATE(tanggal_jual) >= CURDATE() - INTERVAL 6 DAY");
$customer_7hari = $q_cust_7hari->fetch_assoc()['total'];

// 3. Pendapatan (7 HARI TERAKHIR) - REVISI
// Menghitung total uang masuk dalam kurun waktu 7 hari ke belakang
$q_pendapatan_7hari = $conn->query("SELECT SUM(total_harga_jual) as total FROM transaksi_jual WHERE DATE(tanggal_jual) >= CURDATE() - INTERVAL 6 DAY");
$pendapatan_7hari = $q_pendapatan_7hari->fetch_assoc()['total'];

// Pastikan tidak error jika hasil null (belum ada penjualan)
if ($pendapatan_7hari === null) {
    $pendapatan_7hari = 0;
}


// --- DATA UNTUK GRAFIK (7 HARI TERAKHIR) ---
$labels = [];
$data = [];
$data_raw = []; 

$sql_grafik = "SELECT 
                   DATE(tanggal_jual) as tanggal, 
                   SUM(total_harga_jual) as total
               FROM 
                   transaksi_jual
               WHERE 
                   DATE(tanggal_jual) >= CURDATE() - INTERVAL 6 DAY
               GROUP BY 
                   DATE(tanggal_jual)
               ORDER BY 
                   tanggal ASC";
$result_grafik = $conn->query($sql_grafik);
while($row = $result_grafik->fetch_assoc()) {
    $data_raw[$row['tanggal']] = $row['total'];
}

// Loop 7 hari ke belakang untuk mengisi grafik (termasuk hari yang 0 penjualan)
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
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            
            <div class="cards">
                <div class="card">
                    <h3>Antrian (Belum Selesai)</h3>
                    <p style="color: #dc3545;"><?php echo $antrian_pending; ?></p>
                </div>
                
                <div class="card">
                    <h3>Total Customer (7 Hari)</h3>
                    <p style="color: #007bff;"><?php echo $customer_7hari; ?></p>
                </div>
                
                <div class="card">
                    <h3>Pendapatan (7 Hari)</h3>
                    <p style="color: #28a745;">Rp <?php echo number_format($pendapatan_7hari, 0, ',', '.'); ?></p>
                </div>
            </div>

            <div class="chart-wrapper">
                <h2>Tren Pendapatan (7 Hari Terakhir)</h2>
                <canvas id="myChart"></canvas>
            </div>

        </div> 
    </div> 

    <script>
        const ctx = document.getElementById('myChart');
        
        new Chart(ctx, {
            type: 'line', // Saya ganti ke 'line' agar tren naik turun lebih enak dilihat (opsional, bisa diganti 'bar')
            data: {
                labels: <?php echo $labels_js; ?>,
                datasets: [{
                    label: 'Pendapatan Harian',
                    data: <?php echo $data_js; ?>,
                    backgroundColor: 'rgba(33, 37, 41, 0.2)',
                    borderColor: 'rgba(33, 37, 41, 1)',
                    borderWidth: 2,
                    tension: 0.3, // Membuat garis sedikit melengkung halus
                    fill: true
                }]
            },
            options: {
                responsive: true,
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