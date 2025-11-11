<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <?php include 'include/head.php' ?>
    <?php include 'include/css.php' ?>
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
                            <div class="hero-cap hero-cap2 pt-70 text-center">
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
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php
                    // 1. PENGATURAN KONEKSI DATABASE
                    $servername = "127.0.0.1";
                    $username = "root";
                    $password = ""; // Sesuaikan jika database Anda memiliki password
                    $dbname = "umkmbarber";

                    // Buat koneksi
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    // Cek koneksi
                    if ($conn->connect_error) {
                        die("<p class='text-center'>Koneksi ke database gagal: " . $conn->connect_error . "</p>");
                    }

                    // 2. QUERY UNTUK MENGAMBIL DATA DARI TABEL 'layanan'
                    $sql = "SELECT nama_layanan, deskripsi_layanan FROM layanan ORDER BY id_layanan ASC";
                    $result = $conn->query($sql);

                    // 3. TAMPILKAN DATA JIKA ADA
                    if ($result->num_rows > 0) {
                        // Looping untuk setiap baris data layanan
                        while($row = $result->fetch_assoc()) {
                    ?>
                            <div class="col-xl-4 col-lg-4 col-md-6">
                                <div class="services-caption text-center mb-30">
                                    <div class="service-icon">
                                        <i class="flaticon-healthcare-and-medical"></i>
                                    </div> 
                                    <div class="service-cap">
                                        <h4><a href="#"><?php echo htmlspecialchars($row["nama_layanan"]); ?></a></h4>
                                        <p><?php echo htmlspecialchars($row["deskripsi_layanan"]); ?></p>
                                    </div>
                                </div>
                            </div>
                    <?php
                        } // Akhir dari while loop
                    } else {
                        echo "<div class='col-12'><p class='text-center'>Belum ada layanan yang tersedia.</p></div>";
                    }
                    
                    // 4. TUTUP KONEKSI
                    $conn->close();
                    ?>
                </div>
            </div>
        </section>
        </main>
    <footer>
        <?php include 'include/footer.php' ?>
        </footer>
    <div id="back-top" >
        <a title="Go to Top" href="#"> <i class="fas fa-level-up-alt"></i></a>
    </div>

    <script src="./assets/js/vendor/modernizr-3.5.0.min.js"></script>
    <script src="./assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="./assets/js/popper.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
    <script src="./assets/js/jquery.slicknav.min.js"></script>
    <script src="./assets/js/owl.carousel.min.js"></script>
    <script src="./assets/js/slick.min.js"></script>
    <script src="./assets/js/wow.min.js"></script>
    <script src="./assets/js/animated.headline.js"></script>
    <script src="./assets/js/jquery.magnific-popup.js"></script>
    <script src="./assets/js/gijgo.min.js"></script>
    <script src="./assets/js/jquery.nice-select.min.js"></script>
    <script src="./assets/js/jquery.sticky.js"></script>
    <script src="./assets/js/jquery.counterup.min.js"></script>
    <script src="./assets/js/waypoints.min.js"></script>
    <script src="./assets/js/jquery.countdown.min.js"></script>
    <script src="./assets/js/hover-direction-snake.min.js"></script>
    <script src="./assets/js/contact.js"></script>
    <script src="./assets/js/jquery.form.js"></script>
    <script src="./assets/js/jquery.validate.min.js"></script>
    <script src="./assets/js/mail-script.js"></script>
    <script src="./assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="./assets/js/plugins.js"></script>
    <script src="./assets/js/main.js"></script>
    
</body>
</html>