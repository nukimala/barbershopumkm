<?php
// db.php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root'); // Ganti jika user Anda bukan root
define('DB_PASS', '');     // Ganti jika Anda punya password
define('DB_NAME', 'umkm_barber');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>