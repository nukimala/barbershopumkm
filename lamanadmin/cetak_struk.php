<?php
include 'session_check.php';
include '../include/db.php';

$id_jual = $_GET['id'];

// Ambil Data Lengkap Transaksi
$sql = "SELECT c.nama_customer, tj.tanggal_jual, tj.total_harga_jual, tj.uang_bayar, tj.uang_kembali,
               m.nama_model, m.harga_model, l.nama_layanan, l.harga_layanan, a.username
        FROM transaksi_jual tj
        JOIN customer c ON tj.fk_customer = c.id_customer
        JOIN admin a ON tj.fk_admin = a.id_admin
        JOIN model m ON tj.fk_model = m.id_model
        LEFT JOIN layanan l ON tj.fk_layanan = l.id_layanan
        WHERE tj.id_jual = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_jual);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) { echo "Struk tidak ditemukan."; exit; }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Struk Belanja</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 12px; max-width: 300px; margin: 0 auto; padding: 10px; color: #000; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .total-row { display: flex; justify-content: space-between; font-weight: bold; margin-top: 5px; }
        .footer { text-align: center; margin-top: 15px; font-size: 10px; }
        
        /* Sembunyikan tombol saat diprint */
        @media print {
            .no-print { display: none; }
        }
        .btn-print { display: block; width: 100%; padding: 10px; background: #333; color: white; text-align: center; text-decoration: none; margin-top: 20px; font-family: sans-serif; cursor: pointer;}
        .btn-back { display: block; width: 100%; padding: 10px; background: #ccc; color: black; text-align: center; text-decoration: none; margin-top: 5px; font-family: sans-serif;}
    </style>
</head>
<body> <div class="header">
        <h2>UMKM BARBER</h2>
        <p>Jl. Contoh No. 123, Kota Anda</p>
        <p><?= date('d/m/Y H:i', strtotime($data['tanggal_jual'])) ?></p>
        <p>Kasir: <?= $data['username'] ?></p>
    </div>

    <div class="divider"></div>

    <div class="item-row">
        <span><?= $data['nama_model'] ?></span>
        <span><?= number_format($data['harga_model'], 0, ',', '.') ?></span>
    </div>

    <?php if ($data['nama_layanan']): ?>
    <div class="item-row">
        <span><?= $data['nama_layanan'] ?></span>
        <span><?= number_format($data['harga_layanan'], 0, ',', '.') ?></span>
    </div>
    <?php endif; ?>

    <div class="divider"></div>

    <div class="total-row">
        <span>TOTAL</span>
        <span><?= number_format($data['total_harga_jual'], 0, ',', '.') ?></span>
    </div>
    <div class="item-row">
        <span>Bayar</span>
        <span><?= number_format($data['uang_bayar'], 0, ',', '.') ?></span>
    </div>
    <div class="item-row">
        <span>Kembali</span>
        <span><?= number_format($data['uang_kembali'], 0, ',', '.') ?></span>
    </div>

    <div class="divider"></div>
    <div class="footer">
        <p>Terima Kasih atas Kunjungan Anda</p>
        <p>Pelanggan: <?= htmlspecialchars($data['nama_customer']) ?></p>
    </div>

    <button onclick="window.print()" class="btn-print no-print">Cetak Struk</button>
    <a href="customers.php" class="btn-back no-print">Kembali ke Kasir</a>

    <script>
        // Otomatis muncul dialog print saat halaman dimuat
        window.onload = function() { window.print(); }
    </script>
</body>
</html>