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

    <style>
        /* Logo Header Adjustment */
        .logo img {
            width: 120px;
            height: auto;
        }
        
        .header-area {
            padding-top: 15px;
            padding-bottom: 15px;
        }
        
        /* Efek hover & bayangan ikon layanan */
        .services-caption {
            background-color: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            height: 100%;
            margin: 0 15px;
        }

        .services-caption:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            background-color: #f9fcff;
        }

        .service-icon img {
            transition: all 0.3s ease;
        }

        .services-caption:hover .service-icon img {
            transform: scale(1.05);
            filter: brightness(1.1);
        }
        
        /* Owl Carousel Custom Styling */
        .owl-nav button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: white !important;
            width: 50px;
            height: 50px;
            border-radius: 50% !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .owl-nav button:hover {
            background: #007bff !important;
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3);
        }
        
        .owl-nav button span {
            font-size: 30px;
            line-height: 50px;
            color: #007bff;
        }
        
        .owl-nav button:hover span {
            color: white;
        }
        
        .owl-prev {
            left: -25px;
        }
        
        .owl-next {
            right: -25px;
        }
        
        .owl-dots {
            text-align: center;
            margin-top: 40px;
        }
        
        .owl-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ddd !important;
            display: inline-block;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        
        .owl-dot.active {
            background: #007bff !important;
            width: 30px;
            border-radius: 10px;
        }
        
        /* Responsive Mobile */
        @media (max-width: 768px) {
            .logo {
                text-align: center;
                margin: 0 auto;
            }
            
            .logo img {
                margin: 0 auto;
                display: block;
            }
            
            .header-area {
                padding-top: 10px;
                padding-bottom: 10px;
            }
            
            .hero-cap {
                padding-top: 80px !important;
                padding-bottom: 30px !important;
            }
            
            .hero-cap img {
                max-width: 100px !important;
            }
        }
    </style>
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
                                <img src="assets/img/logo/logo.png" alt="Pak To Barbershop" style="max-width: 150px; margin-bottom: 20px;">
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
                
                <!-- Services Slider -->
                <div class="services-slider owl-carousel">
                    <?php
                    // 1. PENGATURAN KONEKSI DATABASE
                    $servername = "127.0.0.1";
                    $username = "root";
                    $password = ""; // Sesuaikan jika database Anda memiliki password
                    $dbname = "umkm_barber";

                    // Buat koneksi
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    // Cek koneksi
                    if ($conn->connect_error) {
                        die("<p class='text-center'>Koneksi ke database gagal: " . $conn->connect_error . "</p>");
                    }

                    // 2. QUERY UNTUK MENGAMBIL DATA DARI TABEL 'layanan'
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
    <div id="back-top">
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
    
    <!-- Services Slider Script -->
    <script>
        $(document).ready(function(){
            $('.services-slider').owlCarousel({
                loop: true,
                margin: 30,
                nav: true,
                dots: true,
                autoplay: false,
                touchDrag: true,
                mouseDrag: true,
                pullDrag: true,
                navText: ['<span>‹</span>', '<span>›</span>'],
                responsive: {
                    0: {
                        items: 1
                    },
                    768: {
                        items: 2
                    },
                    1024: {
                        items: 3
                    }
                }
            });
        });
    </script>

</body>

</html>