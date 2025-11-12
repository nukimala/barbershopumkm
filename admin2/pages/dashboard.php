<?php
session_start();
include '../includes/koneksi.php';

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit();
}

// 🔹 Data ringkasan
$total_antrian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM customer"))['total'];
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_harga_jual), 0) AS total FROM transaksi_jual"))['total'];

// 🔹 Siapkan array bulan (Januari–Desember)
$bulan = [
  "Januari", "Februari", "Maret", "April", "Mei", "Juni",
  "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];
$pendapatan = array_fill(0, 12, 0); // isi semua bulan dengan 0

// 🔹 Ambil data pendapatan per bulan dari database
$query_grafik = mysqli_query($conn, "
  SELECT MONTH(tanggal_jual) AS bulan, SUM(total_harga_jual) AS total 
  FROM transaksi_jual 
  GROUP BY MONTH(tanggal_jual)
");

while ($row = mysqli_fetch_assoc($query_grafik)) {
  $index = (int)$row['bulan'] - 1; // 0 = Januari
  $pendapatan[$index] = (float)$row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Barbershop</title>
  <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    /* ===== Tampilan Umum ===== */
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
      background-color: #f8f9fc;
    }

    /* ===== Sidebar ===== */
    .sidebar {
      width: 220px;
      background-color: #007bff;
      color: white;
      height: 100vh;
      padding-top: 20px;
      position: fixed;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 25px;
    }

    .sidebar a {
      display: block;
      color: white;
      padding: 12px 20px;
      text-decoration: none;
      transition: 0.3s;
    }

    .sidebar a:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .sidebar a i {
      margin-right: 8px;
    }

    /* Tombol kembali */
    .btn-back {
      background-color: #1e90ff;
      border-radius: 6px;
      margin: 16px 20px;
      text-align: center;
      font-weight: bold;
    }

    .btn-back:hover {
      background-color: #0d6efd;
    }

    /* Tombol logout */
    .logout-btn {
      background-color: #dc2626;
      border-radius: 6px;
      margin: 10px 20px;
      text-align: center;
      font-weight: bold;
    }

    .logout-btn:hover {
      background-color: #b91c1c;
    }

    /* ===== Main Content ===== */
    .main-content {
      margin-left: 230px;
      padding: 30px;
      width: 100%;
      min-height: 100vh;
    }

    .cards {
      display: flex;
      gap: 20px;
      margin-top: 20px;
      flex-wrap: wrap;
    }

    .card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      padding: 20px;
      width: 250px;
      text-align: center;
    }

    .card h3 {
      margin-bottom: 10px;
    }

    .card p {
      font-size: 1.5rem;
      color: #007bff;
      font-weight: bold;
    }

    /* ===== Grafik ===== */
    .chart-container {
      margin-top: 40px;
      background-color: #fff;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .chart-container h3 {
      margin-bottom: 15px;
      text-align: center;
      color: #333;
    }
  </style>
</head>

<body>
  <!-- 🔹 Sidebar -->
  <div class="sidebar">
    <div>
      <h2>Barbershop</h2>
      <a href="dashboard.php"><i class="fa fa-chart-line"></i> Dashboard</a>
      <a href="antrian.php"><i class="fa fa-users"></i> Antrian</a>
      <a href="pelanggan.php"><i class="fa fa-user"></i> Data Pelanggan</a>
      <a href="laporan.php"><i class="fa fa-file-alt"></i> Laporan</a>
      <a href="transaksi_beli.php"><i class="fa fa-shopping-cart"></i> Transaksi Beli</a>
    </div>

    <div>
      <a href="../../landing/index.php" class="btn-back"><i class="fa fa-home"></i> Kembali ke Beranda</a>
      <a href="../logout.php" class="logout-btn"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>

  <!-- 🔹 Konten utama -->
  <div class="main-content">
    <?php include '../includes/topbar.php'; ?>

    <section class="cards">
      <div class="card">
        <h3>Total Antrian</h3>
        <p><?= $total_antrian; ?></p>
      </div>
      <div class="card">
        <h3>Total Pendapatan</h3>
        <p>Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></p>
      </div>
    </section>

    <!-- 🔹 Grafik Pendapatan -->
    <div class="chart-container">
      <h3>Grafik Pendapatan per Bulan</h3>
      <canvas id="chartPendapatan"></canvas>
    </div>
  </div>

  <script>
    const ctx = document.getElementById('chartPendapatan').getContext('2d');
    const chartPendapatan = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($bulan); ?>,
        datasets: [{
          label: 'Total Pendapatan (Rp)',
          data: <?= json_encode($pendapatan); ?>,
          backgroundColor: '#007bff',
          borderColor: '#0056b3',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return 'Rp ' + value.toLocaleString('id-ID');
              }
            }
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
              }
            }
          }
        }
      }
    });
  </script>

</body>
</html>
