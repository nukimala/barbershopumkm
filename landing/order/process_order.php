<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pastikan model tidak kosong
    if (empty($_POST['model'])) {
        die("<p style='color:red; font-family:sans-serif;'>❌ Anda harus memilih model potongan rambut. <a href='form_pemesanan.php'>Kembali</a></p>");
    }

    $customer_name = trim($_POST['name']);
    $model_id = $_POST['model'];
    $service_id = !empty($_POST['service']) ? $_POST['service'] : NULL;
    $admin_id = 'ADM01';

    // Gunakan transaksi supaya data aman
    $conn->begin_transaction();

    try {
        // 🔹 1. Tambahkan customer baru
        $stmt_customer = $conn->prepare("INSERT INTO customer (waktu_daftar, nama_customer) VALUES (NOW(), ?)");
        $stmt_customer->bind_param("s", $customer_name);
        $stmt_customer->execute();
        $stmt_customer->close();

        // 🔹 2. Ambil id_customer terakhir
        $result = $conn->query("SELECT * FROM customer ORDER BY waktu_daftar DESC LIMIT 1");
        $data_cus = $result->fetch_assoc();
        $id_customer = $data_cus['id_customer'];
        $nomor_antrian = $data_cus['nomor_antrian'];

        // 🔹 3. Ambil harga model
        $total_harga = 0;
        $stmt_model = $conn->prepare("SELECT nama_model, harga_model FROM model WHERE id_model = ?");
        $stmt_model->bind_param("s", $model_id);
        $stmt_model->execute();
        $data_model = $stmt_model->get_result()->fetch_assoc();
        $total_harga += $data_model['harga_model'];
        $stmt_model->close();

        // 🔹 4. Ambil harga layanan (jika dipilih)
        $nama_layanan = "Tidak ada layanan tambahan";
        if (!is_null($service_id) && $service_id !== "") {
            $stmt_service = $conn->prepare("SELECT nama_layanan, harga_layanan FROM layanan WHERE id_layanan = ?");
            $stmt_service->bind_param("s", $service_id);
            $stmt_service->execute();
            $data_service = $stmt_service->get_result()->fetch_assoc();
            $nama_layanan = $data_service['nama_layanan'];
            $total_harga += $data_service['harga_layanan'];
            $stmt_service->close();
        }

        // 🔹 5. Simpan transaksi jual
        $stmt_jual = $conn->prepare("
            INSERT INTO transaksi_jual (tanggal_jual, total_harga_jual, fk_customer, fk_admin, fk_model, fk_layanan)
            VALUES (NOW(), ?, ?, ?, ?, ?)
        ");
        $stmt_jual->bind_param("dssss", $total_harga, $id_customer, $admin_id, $model_id, $service_id);
        $stmt_jual->execute();
        $stmt_jual->close();

        // Commit transaksi
        $conn->commit();

        // 🔹 6. Tampilkan hasil langsung
        echo "
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>Pemesanan Berhasil</title>
            <style>
                body {
                    font-family: 'Poppins', sans-serif;
                    background: #f8f9fc;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }
                .box {
                    background: #fff;
                    padding: 30px;
                    border-radius: 12px;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                    width: 400px;
                    text-align: center;
                }
                h2 {
                    color: #007bff;
                }
                p {
                    font-size: 15px;
                    color: #333;
                    line-height: 1.6;
                }
                .btn {
                    display: inline-block;
                    background: #007bff;
                    color: #fff;
                    padding: 10px 20px;
                    border-radius: 6px;
                    text-decoration: none;
                    margin-top: 20px;
                    transition: 0.3s;
                }
                .btn:hover {
                    background: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class='box'>
                <h2>✅ Pemesanan Berhasil!</h2>
                <p>Terima kasih, <strong>$customer_name</strong>!</p>
                <p><strong>Nomor Antrian:</strong> $nomor_antrian</p>
                <p><strong>Model Potongan:</strong> {$data_model['nama_model']}</p>
                <p><strong>Layanan Tambahan:</strong> $nama_layanan</p>
                <p><strong>Total Harga:</strong> Rp " . number_format($total_harga, 0, ',', '.') . "</p>
                <a href='order.php' class='btn'>Pesan Lagi</a>
            </div>
        </body>
        </html>
        ";

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo "<p style='color:red; font-family:sans-serif;'>❌ Terjadi kesalahan: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    $conn->close();
}
?>
