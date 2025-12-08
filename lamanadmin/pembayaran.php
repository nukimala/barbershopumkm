<?php
include 'session_check.php';
include '../include/db.php';

$id_customer = $_GET['id'];
$message = "";

// Ambil Data Transaksi Berdasarkan Customer
$sql = "SELECT c.nama_customer, c.nomor_antrian, tj.id_jual, tj.total_harga_jual, m.nama_model, l.nama_layanan
        FROM customer c
        JOIN transaksi_jual tj ON c.id_customer = tj.fk_customer
        LEFT JOIN model m ON tj.fk_model = m.id_model
        LEFT JOIN layanan l ON tj.fk_layanan = l.id_layanan
        WHERE c.id_customer = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_customer);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "Data transaksi tidak ditemukan.";
    exit;
}

// PROSES PEMBAYARAN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bayar'])) {
    $uang_bayar = str_replace('.', '', $_POST['uang_bayar']); // Hapus titik ribuan
    $total_tagihan = $data['total_harga_jual'];
    
    if ($uang_bayar < $total_tagihan) {
        $message = "Uang pembayaran kurang!";
    } else {
        $kembalian = $uang_bayar - $total_tagihan;
        
        $conn->begin_transaction();
        try {
            // 1. Update Transaksi (Simpan Uang Bayar & Kembali)
            $stmt_update = $conn->prepare("UPDATE transaksi_jual SET uang_bayar = ?, uang_kembali = ? WHERE id_jual = ?");
            $stmt_update->bind_param("dds", $uang_bayar, $kembalian, $data['id_jual']);
            $stmt_update->execute();
            
            // 2. Update Status Customer jadi 'Selesai'
            // (Trigger database akan otomatis memotong stok di tahap ini)
            $stmt_cust = $conn->prepare("UPDATE customer SET status = 'Selesai' WHERE id_customer = ?");
            $stmt_cust->bind_param("s", $id_customer);
            $stmt_cust->execute();
            
            $conn->commit();
            
            // Redirect ke cetak struk
            header("Location: cetak_struk.php?id=" . $data['id_jual']);
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Kasir</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .box-kasir { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .total-besar { font-size: 2.5em; font-weight: bold; color: #212529; text-align: center; margin: 20px 0; }
        .rincian { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .input-bayar { font-size: 1.5em; padding: 10px; width: 100%; text-align: center; font-weight: bold; }
        .kembalian-text { text-align: center; font-size: 1.2em; margin-top: 10px; color: #28a745; font-weight: bold; }
    </style>
</head>
<body style="background: #e9ecef;">

    <div class="box-kasir">
        <h2 style="text-align: center;">Kasir Pembayaran</h2>
        <hr>
        
        <?php if($message) echo "<p style='color:red; text-align:center;'>$message</p>"; ?>

        <div class="rincian">
            <p><strong>Customer:</strong> <?= htmlspecialchars($data['nama_customer']) ?> (Antrian: <?= $data['nomor_antrian'] ?>)</p>
            <p><strong>Item:</strong> <?= $data['nama_model'] ?> <?= $data['nama_layanan'] ? "+ " . $data['nama_layanan'] : "" ?></p>
        </div>

        <div class="total-besar">
            Rp <?= number_format($data['total_harga_jual'], 0, ',', '.') ?>
        </div>

        <form method="POST">
            <label><strong>Uang Diterima (Rp):</strong></label>
            <input type="number" name="uang_bayar" id="uang_bayar" class="input-bayar" 
                   placeholder="0" required autofocus oninput="hitungKembalian()">
            
            <div id="info_kembalian" class="kembalian-text">Kembalian: Rp 0</div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <a href="customers.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Batal</a>
                <button type="submit" name="bayar" class="btn btn-primary" style="flex: 1;">Bayar & Cetak</button>
            </div>
        </form>
    </div>

    <script>
        var totalTagihan = <?= $data['total_harga_jual'] ?>;

        function hitungKembalian() {
            var bayar = document.getElementById('uang_bayar').value;
            var kembalian = bayar - totalTagihan;
            
            var textKembalian = document.getElementById('info_kembalian');
            
            if (kembalian >= 0) {
                textKembalian.innerHTML = "Kembalian: Rp " + kembalian.toLocaleString('id-ID');
                textKembalian.style.color = "#28a745";
            } else {
                textKembalian.innerHTML = "Kurang: Rp " + Math.abs(kembalian).toLocaleString('id-ID');
                textKembalian.style.color = "#dc3545";
            }
        }
    </script>
</body>
</html>