<?php
include '../includes/koneksi.php';
$query = mysqli_query($conn, "SELECT 
                    transaksi_jual.id_jual, 
                    antrian.nama_customer, 
                    transaksi_jual.tanggal_jual, 
                    transaksi_jual.total_harga_jual
                FROM transaksi_jual
                LEFT JOIN antrian 
                    ON transaksi_jual.fk_customer = antrian.id_antrian
                ORDER BY transaksi_jual.tanggal_jual DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Laporan</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      color: #000;
      margin: 20px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    th, td {
      border: 1px solid #333;
      padding: 8px;
      text-align: left;
    }
    th {
      background: #eee;
    }
    .total {
      font-weight: bold;
      text-align: right;
    }
    @media print {
      .no-print {
        display: none;
      }
    }
  </style>
</head>
<body>

<h2>Laporan Transaksi Barbershop</h2>
<table>
  <tr>
    <th>No</th>
    <th>Nama Customer</th>
    <th>Tanggal</th>
    <th>Total Harga</th>
  </tr>

  <?php
  $no = 1;
  $total = 0;
  while ($row = mysqli_fetch_assoc($query)) {
    echo "<tr>
            <td>{$no}</td>
            <td>{$row['nama_customer']}</td>
            <td>{$row['tanggal_jual']}</td>
            <td>Rp " . number_format($row['total_harga_jual'], 0, ',', '.') . "</td>
          </tr>";
    $total += $row['total_harga_jual'];
    $no++;
  }
  ?>
  <tr>
    <th colspan="3" class="total">Total Pendapatan:</th>
    <th>Rp <?= number_format($total, 0, ',', '.'); ?></th>
  </tr>
</table>

<div class="no-print" style="margin-top:20px; text-align:center;">
  <button onclick="window.print()">🖨️ Cetak Sekarang</button>
</div>

</body>
</html>
