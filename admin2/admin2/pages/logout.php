<?php
session_start();

// Hapus semua data session
$_SESSION = [];
session_unset();
session_destroy();

// Tampilkan alert dan arahkan ke halaman login
echo "<script>
        alert('Anda berhasil logout!');
        window.location.href = 'login.php';
      </script>";
exit;
?>
