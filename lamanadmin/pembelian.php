<?php
include 'session_check.php';
include '../include/db.php';

$active_page = basename($_SERVER['PHP_SELF']);
$message = '';
$message_type = 'message';
$fk_admin = $_SESSION['admin_id'];

// === LOGIKA TAMBAH PEMBELIAN (MULTI ITEM) ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_pembelian'])) {
    
    // 1. Ambil Data Header
    $fk_supplier = isset($_POST['fk_supplier']) ? trim($_POST['fk_supplier']) : ''; 
    $sumber_lain = isset($_POST['sumber_lain']) ? trim($_POST['sumber_lain']) : ''; 
    
    // 2. Ambil Data Detail
    $raw_produk = $_POST['fk_produk'] ?? [];
    $raw_harga  = $_POST['harga_satuan'] ?? [];
    $raw_jumlah = $_POST['jumlah_beli'] ?? [];

    // Konversi paksa ke array
    $produk_items = is_array($raw_produk) ? $raw_produk : [$raw_produk];
    $harga_items  = is_array($raw_harga)  ? $raw_harga  : [$raw_harga];
    $jumlah_items = is_array($raw_jumlah) ? $raw_jumlah : [$raw_jumlah];
    
    // Validasi
    if (empty($produk_items) || count($produk_items) === 0 || empty($produk_items[0])) {
        $message = "Mohon masukkan minimal satu produk.";
        $message_type = 'message error';
    } else {
        
        $conn->begin_transaction();
        try {
            // A. BUAT HEADER TRANSAKSI
            $id_beli = ''; // Variabel penampung ID
            
            if (empty($fk_supplier)) {
                // Pembelian Lokal
                $stmt_ins_beli = $conn->prepare("INSERT INTO transaksi_beli (tanggal_beli, fk_admin, sumber_lain) VALUES (NOW(), ?, ?)");
                $stmt_ins_beli->bind_param("ss", $fk_admin, $sumber_lain);
                $stmt_ins_beli->execute();
            } else {
                // Pembelian Supplier Resmi
                $stmt_ins_beli = $conn->prepare("INSERT INTO transaksi_beli (tanggal_beli, fk_supplier, fk_admin) VALUES (NOW(), ?, ?)");
                $stmt_ins_beli->bind_param("ss", $fk_supplier, $fk_admin);
                $stmt_ins_beli->execute();
            }

            // [PERBAIKAN UTAMA DI SINI]
            // Karena ID dibuat oleh Trigger (misal: BEL05), kita tidak bisa pakai $conn->insert_id.
            // Kita harus ambil manual ID terakhir yang baru saja terbentuk.
            $res_get_id = $conn->query("SELECT id_beli FROM transaksi_beli ORDER BY id_beli DESC LIMIT 1");
            $row_id = $res_get_id->fetch_assoc();
            $id_beli = $row_id['id_beli'];

            // Cek apakah ID berhasil diambil
            if (empty($id_beli)) {
                throw new Exception("Gagal mengambil ID Transaksi dari database.");
            }

            // B. LOOPING INSERT DETAIL BARANG
            $stmt_detail = $conn->prepare("INSERT INTO detail_beli (fk_beli, fk_produk, harga_beli_satuan, jumlah_beli) VALUES (?, ?, ?, ?)");
            
            for ($i = 0; $i < count($produk_items); $i++) {
                $p_id = $produk_items[$i];
                $raw_p_harga = isset($harga_items[$i]) ? $harga_items[$i] : 0;
                $p_qty       = isset($jumlah_items[$i]) ? $jumlah_items[$i] : 0;
                
                // Bersihkan format harga (hapus titik ribuan)
                $p_harga = str_replace('.', '', $raw_p_harga); 
                
                if (!empty($p_id) && $p_qty > 0) {
                    $stmt_detail->bind_param("ssdi", $id_beli, $p_id, $p_harga, $p_qty); // Perhatikan tipe data bind param
                    $stmt_detail->execute();
                }
            }
            
            $conn->commit();
            $message = "Transaksi pembelian berhasil disimpan!";
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error Database: " . $e->getMessage();
            $message_type = 'message error';
        }
    }
}

// === FITUR HAPUS ITEM RIWAYAT ===
if (isset($_GET['hapus'])) {
    $id_detail_hapus = $_GET['hapus'];
    $conn->begin_transaction();
    try {
        $stmt_del = $conn->prepare("DELETE FROM detail_beli WHERE id_detail_beli = ?");
        $stmt_del->bind_param("s", $id_detail_hapus);
        $stmt_del->execute();
        $conn->commit();
        $message = "Item berhasil dihapus.";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Gagal hapus: " . $e->getMessage();
        $message_type = "message error";
    }
}

// Ambil data untuk Dropdown
$suppliers = $conn->query("SELECT * FROM supplier ORDER BY nama_supplier");
$produk_query = $conn->query("SELECT * FROM produk ORDER BY nama_produk");
$produk_list = [];
while ($row = $produk_query->fetch_assoc()) {
    $produk_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembelian Stok | Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .table-input { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-input th, .table-input td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table-input th { background-color: #f1f1f1; }
        .table-input input, .table-input select { width: 100%; padding: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;}
        .btn-add-row { background: #28a745; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 10px; font-size: 0.9em;}
        .btn-remove-row { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-logo"><img src="logo.png" alt="Barbershop Logo"></div>
        <ul>
            <li><a href="dashboard.php" class="<?php echo ($active_page == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="customers.php" class="<?php echo ($active_page == 'customers.php') ? 'active' : ''; ?>">Customer</a></li>
            <li><a href="pembelian.php" class="<?php echo ($active_page == 'pembelian.php') ? 'active' : ''; ?>">Pembelian Stok</a></li>
            <li><a href="laporan.php" class="<?php echo ($active_page == 'laporan.php') ? 'active' : ''; ?>">Laporan</a></li>
            <li><a href="model.php" class="<?php echo ($active_page == 'model.php') ? 'active' : ''; ?>">Kelola Model</a></li>
            <li><a href="layanan.php" class="<?php echo ($active_page == 'layanan.php') ? 'active' : ''; ?>">Kelola Layanan</a></li>
            <li><a href="produk.php" class="<?php echo ($active_page == 'produk.php') ? 'active' : ''; ?>">Kelola Produk</a></li>
            <li><a href="detail_layanan.php" class="<?php echo ($active_page == 'detail_layanan.php') ? 'active' : ''; ?>">Detail Layanan</a></li>
            <li><a href="pesan.php" class="<?php echo ($active_page == 'pesan.php') ? 'active' : ''; ?>">Pesan Masuk</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h1>Pembelian Stok</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../lamanadmin/logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if ($message) echo "<p class='$message_type'>$message</p>"; ?>

            <div class="form-wrapper" style="max-width: 900px;">
                <h2>Input Transaksi Baru</h2>
                <form action="pembelian.php" method="POST">
                    
                    <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <label><strong>Supplier / Sumber:</strong></label>
                        <select name="fk_supplier" id="selectSupplier" onchange="toggleSumberLain()" style="width: 100%; padding: 10px; margin-bottom: 10px;">
                            <option value="">-- Pembelian Lokal / Lainnya --</option>
                            <?php if ($suppliers->num_rows > 0) {
                                while($row = $suppliers->fetch_assoc()) {
                                    echo "<option value='{$row['id_supplier']}'>{$row['nama_supplier']}</option>";
                                }
                            } ?>
                        </select>
                        <input type="text" name="sumber_lain" id="inputSumberLain" 
                               placeholder="Nama Toko (Wajib jika pembelian lokal)" 
                               style="width: 100%; padding: 10px; display: block;" required>
                    </div>

                    <button type="button" class="btn-add-row" onclick="addRow()">+ Tambah Produk Lain</button>
                    
                    <table class="table-input" id="transactionTable">
                        <thead>
                            <tr>
                                <th width="35%">Produk</th>
                                <th width="25%">Harga Satuan (Rp)</th>
                                <th width="15%">Qty</th>
                                <th width="20%">Subtotal</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="fk_produk[]" class="produk-select" onchange="updatePrice(this)" required>
                                        <option value="" data-harga="0">Pilih Produk</option>
                                        <?php foreach ($produk_list as $prod): ?>
                                            <option value="<?= $prod['id_produk'] ?>" data-harga="<?= $prod['harga_beli'] ?>">
                                                <?= $prod['nama_produk'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="harga_satuan[]" class="input-harga" min="0" value="0" oninput="calculateSubtotal(this)" required>
                                </td>
                                <td>
                                    <input type="number" name="jumlah_beli[]" class="input-qty" min="1" value="1" oninput="calculateSubtotal(this)" required>
                                </td>
                                <td>
                                    <input type="text" class="input-subtotal" value="0" readonly style="background: #eee;">
                                </td>
                                <td>
                                    <button type="button" class="btn-remove-row" onclick="removeRow(this)">X</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="margin-top: 20px; text-align: right;">
                        <h3>Total Estimasi: Rp <span id="grandTotal">0</span></h3>
                        <button type="submit" name="submit_pembelian" class="btn btn-primary" style="padding: 12px 25px; font-size: 16px;">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
            
            <h2>Riwayat Pembelian Terakhir</h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>ID Nota</th>
                            <th>Sumber</th>
                            <th>Produk</th>
                            <th>Harga @</th>
                            <th>Qty</th>
                            <th>Total Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT db.id_detail_beli, tb.id_beli, tb.tanggal_beli, 
                                COALESCE(s.nama_supplier, tb.sumber_lain, 'Lokal') AS nama_sumber, 
                                p.nama_produk, db.jumlah_beli, db.harga_beli_satuan
                                FROM detail_beli db
                                JOIN transaksi_beli tb ON db.fk_beli = tb.id_beli
                                JOIN produk p ON db.fk_produk = p.id_produk
                                LEFT JOIN supplier s ON tb.fk_supplier = s.id_supplier
                                ORDER BY tb.tanggal_beli DESC, tb.id_beli DESC
                                LIMIT 20";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $total_item = $row['jumlah_beli'] * $row['harga_beli_satuan'];
                                echo "<tr>";
                                echo "<td>" . date('d/m/Y', strtotime($row['tanggal_beli'])) . "</td>";
                                echo "<td>" . $row['id_beli'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_sumber']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_produk']) . "</td>";
                                echo "<td>Rp " . number_format($row['harga_beli_satuan'], 0, ',', '.') . "</td>";
                                echo "<td>" . $row['jumlah_beli'] . "</td>";
                                echo "<td>Rp " . number_format($total_item, 0, ',', '.') . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='no-data'>Belum ada riwayat.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div> 
    </div> 

    <script>
        function toggleSumberLain() {
            var selectBox = document.getElementById("selectSupplier");
            var inputBox = document.getElementById("inputSumberLain");
            if (selectBox.value === "") {
                inputBox.style.display = "block";
                inputBox.required = true; 
            } else {
                inputBox.style.display = "none";
                inputBox.required = false;
                inputBox.value = ""; 
            }
        }

        function addRow() {
            var table = document.getElementById("transactionTable").getElementsByTagName('tbody')[0];
            var newRow = table.rows[0].cloneNode(true); 
            newRow.querySelector('.input-harga').value = 0;
            newRow.querySelector('.input-qty').value = 1;
            newRow.querySelector('.input-subtotal').value = 0;
            newRow.querySelector('select').value = "";
            table.appendChild(newRow);
        }

        function removeRow(btn) {
            var row = btn.parentNode.parentNode;
            var table = row.parentNode;
            if (table.rows.length > 1) {
                table.removeChild(row);
                calculateGrandTotal(); 
            } else {
                alert("Minimal harus ada satu baris produk.");
            }
        }

        function updatePrice(selectElement) {
            var price = selectElement.options[selectElement.selectedIndex].getAttribute('data-harga');
            var row = selectElement.parentNode.parentNode;
            var priceInput = row.querySelector('.input-harga');
            priceInput.value = price; 
            calculateSubtotal(priceInput); 
        }

        function calculateSubtotal(element) {
            var row = element.parentNode.parentNode;
            var price = parseFloat(row.querySelector('.input-harga').value) || 0;
            var qty = parseFloat(row.querySelector('.input-qty').value) || 0;
            var subtotal = price * qty;
            row.querySelector('.input-subtotal').value = subtotal.toLocaleString('id-ID');
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            var subtotals = document.querySelectorAll('.input-subtotal');
            var grandTotal = 0;
            subtotals.forEach(function(item) {
                var val = item.value.replace(/\./g, '');
                grandTotal += parseFloat(val) || 0;
            });
            document.getElementById('grandTotal').innerText = grandTotal.toLocaleString('id-ID');
        }
        
        toggleSumberLain();
    </script>
</body>
</html>