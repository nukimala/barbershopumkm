-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2025 at 08:43 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `umkm_barber`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` varchar(10) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
('ADM01', 'admin', '123');

--
-- Triggers `admin`
--
DELIMITER $$
CREATE TRIGGER `generate_id_admin` BEFORE INSERT ON `admin` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(CAST(SUBSTRING(id_admin, 4) AS UNSIGNED)), 0) + 1 INTO last_id FROM admin;
  SET NEW.id_admin = CONCAT('ADM', LPAD(last_id, 2, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id_customer` varchar(10) NOT NULL,
  `nomor_antrian` int(10) DEFAULT NULL,
  `waktu_daftar` datetime NOT NULL,
  `nama_customer` varchar(50) NOT NULL,
  `status` enum('Belum Selesai','Selesai') NOT NULL DEFAULT 'Belum Selesai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `customer`
--
DELIMITER $$
CREATE TRIGGER `generate_id_antrian` BEFORE INSERT ON `customer` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(CAST(SUBSTRING(id_customer, 4) AS UNSIGNED)), 0) + 1 INTO last_id FROM customer;
  SET NEW.id_customer = CONCAT('CUS', LPAD(last_id, 2, '0'));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `generate_nomor_antrian` BEFORE INSERT ON `customer` FOR EACH ROW BEGIN
  DECLARE last_nomor INT;

  SELECT MAX(nomor_antrian)
  INTO last_nomor
  FROM customer
  WHERE DATE(waktu_daftar) = CURDATE();

  IF last_nomor IS NULL THEN
    SET last_nomor = 0;
  END IF;

  SET NEW.nomor_antrian = last_nomor + 1;

  SET NEW.waktu_daftar = NOW();
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_beli`
--

CREATE TABLE `detail_beli` (
  `id_detail_beli` varchar(10) NOT NULL,
  `jumlah_beli` int(10) NOT NULL,
  `fk_beli` varchar(10) NOT NULL,
  `fk_produk` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `detail_beli`
--
DELIMITER $$
CREATE TRIGGER `generate_id_detail_beli` BEFORE INSERT ON `detail_beli` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(CAST(SUBSTRING(id_detail_beli, 4) AS UNSIGNED)), 0) + 1 INTO last_id FROM detail_beli;
  SET NEW.id_detail_beli = CONCAT('DEB', LPAD(last_id, 2, '0'));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `hitung_total_harga_beli` BEFORE INSERT ON `detail_beli` FOR EACH ROW BEGIN
  DECLARE harga_produk DECIMAL(10,2) DEFAULT 0;
  DECLARE subtotal DECIMAL(10,2) DEFAULT 0;

  -- Tentukan harga produk sesuai id_produk
  IF NEW.fk_produk = 'PRO01' THEN
    SET harga_produk = 29000;
  ELSEIF NEW.fk_produk = 'PRO02' THEN
    SET harga_produk = 37000;
  ELSEIF NEW.fk_produk = 'PRO03' THEN
    SET harga_produk = 55000;
  END IF;

  -- Hitung subtotal (jumlah × harga)
  SET subtotal = NEW.jumlah_beli * harga_produk;

  -- Tambahkan ke total transaksi beli
  UPDATE transaksi_beli
  SET total_harga_beli = IFNULL(total_harga_beli, 0) + subtotal
  WHERE id_beli = NEW.fk_beli;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tambah_stok_setelah_pembelian` AFTER INSERT ON `detail_beli` FOR EACH ROW BEGIN
  IF NEW.fk_produk = 'PRO01' THEN
    UPDATE produk SET stok = stok + (NEW.jumlah_beli * 12) WHERE id_produk = 'PRO01';
  ELSEIF NEW.fk_produk = 'PRO02' THEN
    UPDATE produk SET stok = stok + (NEW.jumlah_beli * 16) WHERE id_produk = 'PRO02';
  ELSEIF NEW.fk_produk = 'PRO03' THEN
    UPDATE produk SET stok = stok + (NEW.jumlah_beli * 60) WHERE id_produk = 'PRO03';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_layanan`
--

CREATE TABLE `detail_layanan` (
  `fk_layanan` varchar(10) NOT NULL,
  `fk_produk` varchar(10) NOT NULL,
  `jumlah_produk` int(10) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_layanan`
--

INSERT INTO `detail_layanan` (`fk_layanan`, `fk_produk`, `jumlah_produk`) VALUES
('LAY01', 'PRO01', 1),
('LAY02', 'PRO01', 1),
('LAY02', 'PRO02', 1),
('LAY03', 'PRO01', 1),
('LAY03', 'PRO02', 1),
('LAY03', 'PRO03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `id_layanan` varchar(10) NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `harga_layanan` decimal(10,2) NOT NULL,
  `deskripsi_layanan` varchar(500) NOT NULL,
  `gambar_layanan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `layanan`
--

INSERT INTO `layanan` (`id_layanan`, `nama_layanan`, `harga_layanan`, `deskripsi_layanan`, `gambar_layanan`) VALUES
('LAY01', 'Paket Basic', 13000.00, 'Pijat untuk relaksasi dan Shampoo untuk membersihkan rambut serta kulit kepala.', 'paket_basic.jpeg'),
('LAY02', 'Paket Perawatan', 16000.00, 'Pijat untuk Relaksasi, Shampoo untuk membersihkan rambut dan kulit kepala, dan Conditioner untuk untuk menghaluskan kutikula rambut.', 'paket_perawatan.jpeg'),
('LAY03', 'Paket Perawatan Plus', 17000.00, 'Pijat untuk relaksasi, Shampoo untuk membersihkan rambut dan kulit kepala, Conditioner untuk untuk menghaluskan kutikula rambut, dan Hair Tonic untuk merangsang pertumbuhan rambut.', 'paket_perawatan_plus.jpeg');

--
-- Triggers `layanan`
--
DELIMITER $$
CREATE TRIGGER `generate_id_layanan` BEFORE INSERT ON `layanan` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(CAST(SUBSTRING(id_layanan, 4) AS UNSIGNED)), 0) + 1 INTO last_id FROM layanan;
  SET NEW.id_layanan = CONCAT('LAY', LPAD(last_id, 2, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `model`
--

CREATE TABLE `model` (
  `id_model` varchar(10) NOT NULL,
  `nama_model` varchar(50) NOT NULL,
  `harga_model` decimal(10,2) NOT NULL,
  `deskripsi_model` varchar(500) NOT NULL,
  `gambar_model` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `model`
--

INSERT INTO `model` (`id_model`, `nama_model`, `harga_model`, `deskripsi_model`, `gambar_model`) VALUES
('MOD01', 'Buzz Cut', 20000.00, 'Potongan rambut pendek yang merata di seluruh area kepala, praktis dan mudah dirawat', 'buzz_cut.jpeg'),
('MOD02', 'Crew Cut', 20000.00, 'Potongan rambut dengan bagian atas sedikit lebih panjang dibandingkan bagian samping, memberikan kesan rapi dan maskulin', 'crew_cut.jpeg'),
('MOD03', 'French Crop', 20000.00, 'Potongan rambut dengan bagian atas lebih panjang dibandingkan bagian samping dan masih menyisakan poni untuk dijatuhkan ke depan, memberikan kesan stylish namun tetap sopan', 'french_crop.jpeg'),
('MOD04', 'Fringe', 20000.00, 'Potongan rambut yang membiarkan poni menutupi dahi, memberikan kesan stylish dan kasual', 'fringe.jpeg'),
('MOD05', 'Comma Hair', 20000.00, 'Potongan rambut ala Korea dengan poni yang diarahkan ke samping dan dibentuk melengkung seperti tanda koma, memberi kesan stylish dan trendy', 'comma_hair.jpeg'),
('MOD06', 'Mullet', 20000.00, 'Potongan rambut dengan rambut yang pendek di bagian depan dan sampping, lalu panjang di bagian belakang, memberi kesan unik, berani, dan percaya diri', 'mullet.jpeg');

--
-- Triggers `model`
--
DELIMITER $$
CREATE TRIGGER `generate_id_model` BEFORE INSERT ON `model` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(CAST(SUBSTRING(id_model, 4) AS UNSIGNED)), 0) + 1 INTO last_id FROM model;
  SET NEW.id_model = CONCAT('MOD', LPAD(last_id, 2, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `id_pesan` varchar(10) NOT NULL,
  `nama_pengirim` varchar(100) NOT NULL,
  `waktu_kirim` datetime NOT NULL,
  `isi_pesan` varchar(1000) NOT NULL,
  `kategori_pesan` enum('Ditampilkan','Tidak Ditampilkan') NOT NULL DEFAULT 'Tidak Ditampilkan',
  `fk_admin` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `pesan`
--
DELIMITER $$
CREATE TRIGGER `generate_id_pesan` BEFORE INSERT ON `pesan` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(CAST(SUBSTRING(id_pesan, 4) AS UNSIGNED)), 0) + 1 INTO last_id FROM pesan;
  SET NEW.id_pesan = CONCAT('PES', LPAD(last_id, 2, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` varchar(10) NOT NULL,
  `nama_produk` varchar(50) NOT NULL,
  `harga_beli` decimal(10,2) NOT NULL,
  `stok` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `harga_beli`, `stok`) VALUES
('PRO01', 'Shampoo', 29000.00, 0),
('PRO02', 'Conditioner', 37000.00, 0),
('PRO03', 'Hair Tonic', 55000.00, 0);

--
-- Triggers `produk`
--
DELIMITER $$
CREATE TRIGGER `generate_id_produk` BEFORE INSERT ON `produk` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(CAST(SUBSTRING(id_produk, 4) AS UNSIGNED)), 0) + 1 INTO last_id FROM produk;
  SET NEW.id_produk = CONCAT('PRO', LPAD(last_id, 2, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` varchar(10) NOT NULL,
  `nama_supplier` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`) VALUES
('SUP01', 'PT. Unilever Indonesia, Tbk.');

--
-- Triggers `supplier`
--
DELIMITER $$
CREATE TRIGGER `generate_id_supplier` BEFORE INSERT ON `supplier` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(CAST(SUBSTRING(id_supplier, 4) AS UNSIGNED)), 0) + 1 INTO last_id FROM supplier;
  SET NEW.id_supplier = CONCAT('SUP', LPAD(last_id, 2, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_beli`
--

CREATE TABLE `transaksi_beli` (
  `id_beli` varchar(10) NOT NULL,
  `tanggal_beli` datetime NOT NULL,
  `total_harga_beli` decimal(10,2) DEFAULT 0.00,
  `fk_supplier` varchar(10) NOT NULL,
  `fk_admin` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi_beli`
--

INSERT INTO `transaksi_beli` (`id_beli`, `tanggal_beli`, `total_harga_beli`, `fk_supplier`, `fk_admin`) VALUES
('BEL01', '2025-11-17 14:27:02', 29000.00, 'SUP01', 'ADM01');

--
-- Triggers `transaksi_beli`
--
DELIMITER $$
CREATE TRIGGER `generate_id_beli` BEFORE INSERT ON `transaksi_beli` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(CAST(SUBSTRING(id_beli, 4) AS UNSIGNED)), 0) + 1 INTO last_id FROM transaksi_beli;
  SET NEW.id_beli = CONCAT('BEL', LPAD(last_id, 2, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_jual`
--

CREATE TABLE `transaksi_jual` (
  `id_jual` varchar(10) NOT NULL,
  `tanggal_jual` datetime NOT NULL,
  `total_harga_jual` decimal(10,2) DEFAULT NULL,
  `fk_customer` varchar(10) NOT NULL,
  `fk_admin` varchar(10) NOT NULL,
  `fk_model` varchar(10) NOT NULL,
  `fk_layanan` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `transaksi_jual`
--
DELIMITER $$
CREATE TRIGGER `cek_stok_sebelum_penjualan` BEFORE INSERT ON `transaksi_jual` FOR EACH ROW BEGIN
  DECLARE v_stok_sisa_terendah INT DEFAULT 0;
  DECLARE v_nama_produk_habis VARCHAR(100);

  DECLARE v_error_message VARCHAR(255);

  IF NEW.fk_layanan IS NOT NULL AND NEW.fk_layanan <> '' THEN
    
    SELECT 
      MIN(p.stok - dl.jumlah_produk),
      (SELECT p.nama_produk 
       FROM produk p 
       JOIN detail_layanan dl ON p.id_produk = dl.fk_produk 
       WHERE dl.fk_layanan = NEW.fk_layanan 
       ORDER BY (p.stok - dl.jumlah_produk) ASC 
       LIMIT 1)
    INTO 
      v_stok_sisa_terendah, 
      v_nama_produk_habis
    FROM 
      detail_layanan dl
    JOIN 
      produk p ON dl.fk_produk = p.id_produk
    WHERE 
      dl.fk_layanan = NEW.fk_layanan;

    IF v_stok_sisa_terendah < 0 THEN

      SET v_error_message = CONCAT('Stok tidak mencukupi untuk produk: ', v_nama_produk_habis, '. Transaksi dibatalkan.');

      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = v_error_message;
      
    END IF;

  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `generate_id_jual` BEFORE INSERT ON `transaksi_jual` FOR EACH ROW BEGIN
  DECLARE last_id INT;
  SELECT IFNULL(MAX(CAST(SUBSTRING(id_jual, 4) AS UNSIGNED)), 0) + 1 INTO last_id FROM transaksi_jual;
  SET NEW.id_jual = CONCAT('JUA', LPAD(last_id, 2, '0'));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `hitung_total_harga_jual` BEFORE INSERT ON `transaksi_jual` FOR EACH ROW BEGIN
  DECLARE v_harga_model DECIMAL(10,2) DEFAULT 0;
  DECLARE v_harga_layanan DECIMAL(10,2) DEFAULT 0;

  IF NEW.fk_model IS NOT NULL THEN
    SELECT harga_model INTO v_harga_model
    FROM model
    WHERE id_model = NEW.fk_model;
  END IF;

  IF NEW.fk_layanan IS NOT NULL AND NEW.fk_layanan <> '' THEN
    SELECT harga_layanan INTO v_harga_layanan
    FROM layanan
    WHERE id_layanan = NEW.fk_layanan;
  END IF;

  SET NEW.total_harga_jual = IFNULL(v_harga_model,0) + IFNULL(v_harga_layanan,0);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `kurangi_stok_setelah_penjualan` AFTER INSERT ON `transaksi_jual` FOR EACH ROW BEGIN
  IF NEW.fk_layanan IS NOT NULL THEN
    UPDATE produk p
    JOIN detail_layanan dl ON p.id_produk = dl.fk_produk
    SET p.stok = p.stok - dl.jumlah_produk
    WHERE dl.fk_layanan = NEW.fk_layanan;
  END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id_customer`);

--
-- Indexes for table `detail_beli`
--
ALTER TABLE `detail_beli`
  ADD PRIMARY KEY (`id_detail_beli`),
  ADD KEY `fk_beli` (`fk_beli`),
  ADD KEY `fk_produk` (`fk_produk`);

--
-- Indexes for table `detail_layanan`
--
ALTER TABLE `detail_layanan`
  ADD KEY `fk_layanan` (`fk_layanan`),
  ADD KEY `fk_produk` (`fk_produk`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id_layanan`);

--
-- Indexes for table `model`
--
ALTER TABLE `model`
  ADD PRIMARY KEY (`id_model`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id_pesan`),
  ADD KEY `fk_admin` (`fk_admin`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `transaksi_beli`
--
ALTER TABLE `transaksi_beli`
  ADD PRIMARY KEY (`id_beli`),
  ADD KEY `fk_admin` (`fk_admin`),
  ADD KEY `fk_supplier` (`fk_supplier`);

--
-- Indexes for table `transaksi_jual`
--
ALTER TABLE `transaksi_jual`
  ADD PRIMARY KEY (`id_jual`),
  ADD KEY `fk_antrian` (`fk_customer`),
  ADD KEY `id_admin` (`fk_admin`),
  ADD KEY `fk_model` (`fk_model`),
  ADD KEY `fk_layanan` (`fk_layanan`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_beli`
--
ALTER TABLE `detail_beli`
  ADD CONSTRAINT `detail_beli_ibfk_1` FOREIGN KEY (`fk_beli`) REFERENCES `transaksi_beli` (`id_beli`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_beli_ibfk_2` FOREIGN KEY (`fk_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_layanan`
--
ALTER TABLE `detail_layanan`
  ADD CONSTRAINT `detail_layanan_ibfk_1` FOREIGN KEY (`fk_layanan`) REFERENCES `layanan` (`id_layanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_layanan_ibfk_2` FOREIGN KEY (`fk_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pesan`
--
ALTER TABLE `pesan`
  ADD CONSTRAINT `pesan_ibfk_1` FOREIGN KEY (`fk_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi_beli`
--
ALTER TABLE `transaksi_beli`
  ADD CONSTRAINT `transaksi_beli_ibfk_1` FOREIGN KEY (`fk_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_beli_ibfk_2` FOREIGN KEY (`fk_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi_jual`
--
ALTER TABLE `transaksi_jual`
  ADD CONSTRAINT `transaksi_jual_ibfk_1` FOREIGN KEY (`fk_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_jual_ibfk_2` FOREIGN KEY (`fk_model`) REFERENCES `model` (`id_model`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_jual_ibfk_3` FOREIGN KEY (`fk_layanan`) REFERENCES `layanan` (`id_layanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_jual_ibfk_4` FOREIGN KEY (`fk_customer`) REFERENCES `customer` (`id_customer`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
