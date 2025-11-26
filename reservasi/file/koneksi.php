<?php
// Konfigurasi Database
// Sesuaikan dengan settingan XAMPP/Laragon Anda
$host = '127.0.0.1'; // Atau 'localhost'
$user = 'root';      // Default XAMPP user
$pass = '';          // Default XAMPP password (biasanya kosong)
$db   = 'umkm_barber'; // Nama database Anda (sesuai file .sql terakhir)

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}
?>