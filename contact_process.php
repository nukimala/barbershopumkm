<?php
// Atur header untuk output JSON
header('Content-Type: application/json');

// Pengaturan koneksi ke database
$servername = "127.0.0.1"; // atau "localhost"
$username = "root";        // Ganti jika username database Anda berbeda
$password = "";            // Ganti jika password database Anda berbeda
$dbname = "umkm_barber";   // --- [DIUBAH] Sesuai dengan database Anda ---

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi ke database gagal: ' . $conn->connect_error]);
    exit();
}

// Cek apakah data 'name' dan 'message' dikirim
if (isset($_POST['name']) && isset($_POST['message'])) {
    
    // Ambil data (real_escape_string tetap baik untuk lapisan keamanan tambahan sebelum validasi)
    $nama_pengirim = $conn->real_escape_string($_POST['name']);
    $isi_pesan = $conn->real_escape_string($_POST['message']);

    // Validasi sederhana agar tidak kosong
    if (empty(trim($nama_pengirim)) || empty(trim($isi_pesan))) {
        echo json_encode(['status' => 'error', 'message' => 'Nama dan pesan tidak boleh kosong.']);
        $conn->close(); // Tutup koneksi sebelum exit
        exit();
    }

    // --- [DIUBAH] Siapkan query SQL menggunakan Prepared Statements ---
    
    // Tentukan admin default untuk menampung pesan
    $default_admin_id = 'ADM01'; 

    $sql = "INSERT INTO pesan (nama_pengirim, isi_pesan, fk_admin, waktu_kirim) VALUES (?, ?, ?, NOW())";

    // Siapkan statement
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan statement: ' . $conn->error]);
        $conn->close();
        exit();
    }

    // Bind parameter ke statement
    // 'sss' berarti tiga variabel berikutnya adalah tipe string
    $stmt->bind_param("sss", $nama_pengirim, $isi_pesan, $default_admin_id);

    // Eksekusi query
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Pesan Anda berhasil terkirim! Terima kasih.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim pesan: ' . $stmt->error]);
    }

    // Tutup statement
    $stmt->close();

} else {
    echo json_encode(['status' => 'error', 'message' => 'Data formulir tidak lengkap.']);
}

// Tutup koneksi
$conn->close();

?>