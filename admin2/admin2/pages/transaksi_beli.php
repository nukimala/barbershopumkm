<?php
session_start();
include '../includes/koneksi.php';

// Simpan transaksi
if (isset($_POST['simpan'])) {
    $tanggal = $_POST['tanggal'];
    $supplier = $_POST['supplier'];
    $supplier_lain = !empty($_POST['supplier_lain']) ? $_POST['supplier_lain'] : NULL;
    $admin = 'ADM01'; // bisa diganti pakai $_SESSION['id_admin']

    mysqli_query($conn, "INSERT INTO transaksi_beli (tanggal_beli, harga_beli, fk_supplier, fk_admin, supplier_lain)
                         VALUES ('$tanggal', 0, " . ($supplier ? "'$supplier'" : "NULL") . ", '$admin', " . ($supplier_lain ? "'$supplier_lain'" : "NULL") . ")");

    $id_beli = mysqli_insert_id($conn);
    $total = 0;

    foreach ($_POST['produk'] as $key => $id_produk) {
        $jumlah = $_POST['jumlah'][$key];
        $harga = $_POST['harga'][$key];
        $subtotal = $jumlah * $harga;
        $total += $subtotal;

        mysqli_query($conn, "INSERT INTO detail_beli (id_beli, id_produk, jumlah, harga_satuan, subtotal)
                             VALUES ('$id_beli', '$id_produk', '$jumlah', '$harga', '$subtotal')");

        mysqli_query($conn, "UPDATE produk SET stok = stok + $jumlah WHERE id_produk='$id_produk'");
    }

    mysqli_query($conn, "UPDATE transaksi_beli SET harga_beli = '$total' WHERE id_beli='$id_beli'");

    echo "<script>alert('Transaksi pembelian berhasil disimpan!'); window.location='data_transaksi_beli.php';</script>";
}

// Ambil data produk dan supplier
$produk = mysqli_query($conn, "SELECT * FROM produk");
$supplier = mysqli_query($conn, "SELECT * FROM supplier");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Transaksi Pembelian</title>
<link rel="stylesheet" href="../css/dashboard.css">
<style>
  .content { background:#fff; padding:20px; border-radius:10px; margin-top:20px; }
  table { width:100%; border-collapse:collapse; margin-top:10px; }
  th, td { border:1px solid #ccc; padding:8px; text-align:left; }
  th { background:#007bff; color:#fff; }
  input[type="number"], select, input[type="text"], input[type="date"] { padding:5px; width:100%; }
  .btn-simpan { background:#28a745; color:white; border:none; padding:10px 15px; border-radius:6px; cursor:pointer; margin-top:10px; }
  .btn-simpan:hover { background:#218838; }
</style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/topbar.php'; ?>

<div class="content">
  <h2>Tambah Transaksi Pembelian</h2>
  <form method="POST">
    <label>Tanggal Pembelian:</label>
    <input type="date" name="tanggal" required><br><br>

    <label>Supplier Utama:</label>
    <select name="supplier">
      <option value="">-- Pilih Supplier --</option>
      <?php while ($s = mysqli_fetch_assoc($supplier)) { ?>
        <option value="<?= $s['id_supplier']; ?>"><?= $s['nama_supplier']; ?></option>
      <?php } ?>
    </select><br><br>

    <label>Atau Supplier Lain:</label>
    <input type="text" name="supplier_lain" placeholder="Masukkan nama supplier lain (opsional)"><br><br>

    <h3>Daftar Produk Dibeli</h3>
    <table>
      <tr>
        <th>Produk</th>
        <th>Jumlah</th>
        <th>Harga Satuan</th>
      </tr>
      <?php while ($p = mysqli_fetch_assoc($produk)) { ?>
      <tr>
        <td>
          <input type="checkbox" name="produk[]" value="<?= $p['id_produk']; ?>"> <?= $p['nama_produk']; ?>
        </td>
        <td><input type="number" name="jumlah[]" min="0"></td>
        <td><input type="number" name="harga[]" value="<?= $p['harga_beli']; ?>"></td>
      </tr>
      <?php } ?>
    </table>

    <button type="submit" name="simpan" class="btn-simpan">Simpan Transaksi</button>
  </form>
</div>
</div>
</body>
</html>
