<!doctype html>
<html class="no-js" lang="zxx">

<head>
    <?php
    include 'include/head.php';
    include 'include/css.php';
    include 'include/db.php'
    ?>
    
    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

</head>

<body>
    <?php include 'include/loading.php' ?>
    <header>
        <?php include 'include/header.php' ?>
    </header>
    <main>
        <div class="slider-area2">
            <div class="slider-height2 d-flex align-items-center">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="hero-cap hero-cap2 text-center" style="padding-top: 120px; padding-bottom: 50px;">
                                <h2>Pilihan Layanan</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section class="service-area section-padding30">
            <div class="container">
                <div class="row d-flex justify-content-center">
                    <div class="col-xl-7 col-lg-8 col-md-11 col-sm-11">
                        <div class="section-tittle text-center mb-90">
                            <span>Layanan</span>
                            <h2>Penawaran pelayanan terbaik dari kami untuk anda</h2>
                            <p class="text-muted mt-3">
                                <i class="fas fa-hand-pointer"></i> Geser dengan cursor atau jari Anda
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Services Slider -->
                <div class="slider owl-carousel">
                    <?php
                    //  QUERY UNTUK MENGAMBIL DATA DARI TABEL 'layanan'
                    $sql = "SELECT nama_layanan, deskripsi_layanan, gambar_layanan FROM layanan ORDER BY id_layanan ASC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Path gambar
                            $gambarPath = "assets/img/layanan/" . htmlspecialchars($row["gambar_layanan"]);
                            ?>
                            <div class="services-caption text-center">
                                <div class="service-icon mb-3">
                                    <img src="<?php echo $gambarPath; ?>"
                                        alt="<?php echo htmlspecialchars($row["nama_layanan"]); ?>"
                                        class="img-fluid rounded" style="max-height:200px; object-fit:cover; width: 100%;">
                                </div>
                                <div class="service-cap">
                                    <h4><a href="#"><?php echo htmlspecialchars($row["nama_layanan"]); ?></a></h4>
                                    <p><?php echo htmlspecialchars($row["deskripsi_layanan"]); ?></p>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div class='col-12'><p class='text-center'>Belum ada layanan yang tersedia.</p></div>";
                    }

                    // TUTUP KONEKSI
                    $conn->close();
                    ?>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <?php include 'include/footer.php' ?>
    </footer>
    <div id="back-top">
        <a title="Go to Top" href="#"> <i class="fas fa-level-up-alt"></i></a>
    </div>
<?php
    include 'include/js.php' ?>

</body>

</html>