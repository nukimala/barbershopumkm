<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi utama: Pastikan model tidak kosong
    if (empty($_POST['model'])) {
        die("Error: Anda harus memilih model potongan rambut. Silakan kembali dan lengkapi form.");
    }

    $customer_name = $_POST['name'];
    $model_id = $_POST['model']; // Langsung ambil nilainya karena sudah divalidasi
    $service_id = !empty($_POST['service']) ? $_POST['service'] : NULL;
    $admin_id = 'ADM01';

    $conn->begin_transaction();

    try {
        // Langkah 1: Buat entri antrian
        $stmt_antrian = $conn->prepare("INSERT INTO antrian (nama_customer) VALUES (?)");
        $stmt_antrian->bind_param("s", $customer_name);
        $stmt_antrian->execute();
        $stmt_antrian->close();

        // Langkah 2: Dapatkan ID antrian yang baru dibuat
        $result = $conn->query("SELECT id_antrian FROM antrian ORDER BY waktu_daftar DESC LIMIT 1");
        $antrian_row = $result->fetch_assoc();
        $customer_id = $antrian_row['id_antrian'];

        // Langkah 3: Hitung total harga
        $total_price = 0;

        // Harga model (sekarang wajib)
        $model_stmt = $conn->prepare("SELECT harga_model FROM model WHERE id_model = ?");
        $model_stmt->bind_param("s", $model_id);
        $model_stmt->execute();
        $total_price += $model_stmt->get_result()->fetch_assoc()['harga_model'];
        $model_stmt->close();
        
        // Harga layanan (opsional)
        if (!is_null($service_id)) {
            $service_stmt = $conn->prepare("SELECT harga_layanan FROM layanan WHERE id_layanan = ?");
            $service_stmt->bind_param("s", $service_id);
            $service_stmt->execute();
            $total_price += $service_stmt->get_result()->fetch_assoc()['harga_layanan'];
            $service_stmt->close();
        }

        // Langkah 4: Masukkan catatan transaksi utama
        $stmt_jual = $conn->prepare("INSERT INTO transaksi_jual (tanggal_jual, total_harga_jual, fk_customer, fk_admin, fk_model, fk_layanan) VALUES (CURDATE(), ?, ?, ?, ?, ?)");
        $stmt_jual->bind_param("dssss", $total_price, $customer_id, $admin_id, $model_id, $service_id);
        $stmt_jual->execute();
        $stmt_jual->close();

        $conn->commit();
        echo "Pemesanan berhasil! ID antrian Anda adalah " . $customer_id;

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        echo "Gagal membuat pesanan. Silakan coba lagi. Detail: " . $exception->getMessage();
    }

    $conn->close();
}
?>