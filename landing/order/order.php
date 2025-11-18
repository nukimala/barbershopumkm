<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potong Rambut Pak To</title>
    <!-- Menghubungkan ke file CSS yang sudah ada -->
    <link rel="stylesheet" href="style.css">
    
    <?php
    include '../include/db.php'; 
    ?>
</head>

<body>
    <!-- 
      Struktur Baru: .split-container menggantikan .order-container.
      Ini akan menampung dua kolom berdampingan.
    -->
    <div class="split-container">

        <!-- ===== KOLOM KIRI (BRANDING) ===== -->
        <div class="split-info">
            <div class="order-header">
                <!-- Placeholder logo disesuaikan dengan warna tema baru -->
                <img src="../assets/img/logo/logo.png" 
                     alt="Logo Barbershop" 
                     class="order-logo" 
                     onerror="this.src='https://placehold.co/70x70/C6A969/222222?text=PAK+TO'; this.onerror=null;">
                <h2>Formulir Pemesanan</h2>
                <p>Silakan isi untuk memesan layanan kami.</p>
            </div>
        </div>

        <!-- ===== KOLOM KANAN (FORMULIR) ===== -->
        <div class="split-form">
            <form id="order-form" action="process_order.php" method="POST">

                <!-- 
                  Layout Grid Dihapus: 
                  Form kembali menjadi satu kolom.
                -->
                <div class="form-group">
                    <label for="name">Nama Lengkap:</label>
                    <input type="text" id="name" name="name" placeholder="Contoh: Budi Setiawan" required>
                </div>

                <div class="form-group">
                    <label for="model">Pilih Model Potongan Rambut:</label>
                    <select id="model" name="model" required>
                        <option value="" disabled selected>-- Silakan pilih model --</option>

                        <?php
                        include 'db.php';
                        $sql = "SELECT * FROM model";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id_model'] . "'>" . $row['nama_model'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Pilih Layanan Tambahan:</label>
                    <div class="checkbox-group">
                        
                        <div class="checkbox-item">
                            <input type="radio" id="no-service" name="service" value="" checked>
                            <label for="no-service">Tidak ada layanan tambahan</label>
                        </div>

                        <?php

                        $sql = "SELECT * FROM layanan";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='checkbox-item'>";
                            echo "<input type='radio' id='" . $row['id_layanan'] . "' name='service' value='" . $row['id_layanan'] . "'>";
                            echo "<label for='" . $row['id_layanan'] . "'>" . $row['nama_layanan'] . "</label>";
                            echo "</div>";
                        }
                        $conn->close();
                        ?>
                    </div>
                </div>

                <button type="submit" class="cta-button">Kirim Pesanan</button>
            </form>
        </div>
    </div>
</body>

</html>