<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potong Rambut Pak To</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="order-container">
        <div class="order-header">
            <img src="logo.png" alt="Logo Barbershop" class="order-logo">
            <h2>Formulir Pemesanan</h2>
            <p>Silakan isi detail di bawah ini untuk memesan layanan kami.</p>
        </div>
        <form id="order-form" action="process_order.php" method="POST">
            <div class="form-group">
                <label for="name">Nama Lengkap Anda:</label>
                <input type="text" id="name" name="name" placeholder="Contoh: Budi Setiawan" required>
            </div>

            <div class="form-group">
                <label for="model">Pilih Model Potongan Rambut:</label>
                <select id="model" name="model" required>
                    <option value="" disabled selected>-- Silakan pilih model --</option>
                    <?php
                    include 'db.php';
                    if ($conn->connect_error) {
                        $conn = new mysqli("localhost", "root", "", "umkm");
                    }
                    $sql = "SELECT * FROM model";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row["id_model"] . "'>" . $row["nama_model"] . "</option>";
                        }
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
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<div class='checkbox-item'>";
                            echo "<input type='radio' id='" . $row["id_layanan"] . "' name='service' value='" . $row["id_layanan"] . "'>";
                            echo "<label for='" . $row["id_layanan"] . "'>" . $row["nama_layanan"] . "</label>";
                            echo "</div>";
                        }
                    }
                    $conn->close();
                    ?>
                </div>
            </div>

            <button type="submit" class="cta-button">Kirim Pesanan</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>