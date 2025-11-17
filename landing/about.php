<!doctype html>
<html class="no-js" lang="zxx">

<head>
    <?php
    include 'include/head.php';
    include 'include/css.php';
    include 'include/db.php'
    ?>
    <style>
        /* Efek hover untuk card model */
        .services-caption {
            transition: all 0.3s ease;
            cursor: grab;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .services-caption:active {
            cursor: grabbing;
        }
        
        .services-caption:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        /* Efek hover untuk gambar */
        .service-icon {
            overflow: hidden;
            border-radius: 10px;
        }
        
        .service-icon img {
            transition: transform 0.4s ease;
        }
        
        .services-caption:hover .service-icon img {
            transform: scale(1.1);
        }
        
        /* Efek hover untuk teks */
        .service-cap h4 {
            transition: color 0.3s ease;
        }
        
        .services-caption:hover .service-cap h4 {
            color: #ff6b6b;
        }
        
        /* Sembunyikan navigation buttons */
        .owl-nav {
            display: none !important;
        }
        
        /* Cursor grab untuk carousel */
        .owl-carousel {
            cursor: grab;
        }
        
        .owl-carousel.dragging {
            cursor: grabbing;
        }
        
        .owl-carousel .owl-stage {
            cursor: grab;
        }
        
        .owl-carousel .owl-stage:active {
            cursor: grabbing;
        }
        
        /* Style untuk dots - visible untuk semua device */
        .owl-dots {
            margin-top: 40px !important;
            text-align: center;
            display: block !important;
        }
        
        .owl-dot {
            display: inline-block;
            margin: 0 8px;
            transition: all 0.3s ease;
        }
        
        .owl-dot span {
            width: 15px !important;
            height: 15px !important;
            background: #d1d1d1 !important;
            border-radius: 50%;
            display: block;
            transition: all 0.3s ease;
        }
        
        .owl-dot:hover span {
            transform: scale(1.3);
            background: #999 !important;
        }
        
        .owl-dot.active span {
            background: #ff6b6b !important;
            width: 40px !important;
            border-radius: 10px;
            transform: scale(1.1);
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
                            <div class="hero-cap hero-cap2 pt-70 text-center">
                                <h2>Pilihan Model</h2>
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
                            <span>Model Rambut</span>
                            <h2>Temukan gaya rambut yang cocok dengan kepribadian anda</h2>
                            <p class="text-muted mt-3">
                                <i class="fas fa-hand-pointer"></i> Geser dengan cursor atau jari Anda
                            </p>
                        </div>
                    </div>
                </div>

                <div class="owl-carousel model-carousel">
                    <?php
                    // Ambil kolom gambar_model
                    $sql = "SELECT nama_model, deskripsi_model, gambar_model FROM model ORDER BY id_model ASC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // tambahkan ./ agar path relatif ke folder project
                            $gambarPath = "assets/img/model/" . htmlspecialchars($row['gambar_model']);

                            // jika file tidak ada, tampilkan gambar default
                            if (!file_exists($gambarPath)) {
                                $gambarPath = "../assets/img/model/mullet.jpeg";
                            }
                    ?>
                            <div class="services-caption text-center p-4">
                                <div class="service-icon mb-3">
                                    <img src="<?php echo $gambarPath; ?>"
                                        alt="<?php echo htmlspecialchars($row['nama_model']); ?>"
                                        style="width: 250px; height: 250px; object-fit: cover;">
                                </div>
                                <div class="service-cap">
                                    <h4><a href="#"><?php echo htmlspecialchars($row["nama_model"]); ?></a></h4>
                                    <p><?php echo htmlspecialchars($row["deskripsi_model"]); ?></p>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p class='text-center'>Belum ada model rambut yang tersedia.</p>";
                    }
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
    
    <!-- Slider Configuration -->
    <script>
    $(document).ready(function() {
        var owl = $('.model-carousel').owlCarousel({
            loop: true,
            margin: 30,
            nav: false, // Disable navigation buttons
            dots: true, // Enable dots
            autoplay: false,
            mouseDrag: true, // Enable mouse drag
            touchDrag: true, // Enable touch drag
            pullDrag: true,
            freeDrag: false,
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

        // Tambahkan class 'dragging' saat sedang drag
        owl.on('drag.owl.carousel', function(event) {
            $('.owl-carousel').addClass('dragging');
        });

        owl.on('dragged.owl.carousel', function(event) {
            $('.owl-carousel').removeClass('dragging');
        });

        // Efek tap untuk mobile - simulasi hover
        $('.services-caption').on('touchstart', function() {
            $(this).css({
                'transform': 'translateY(-10px)',
                'box-shadow': '0 10px 30px rgba(0,0,0,0.2)'
            });
            $(this).find('img').css('transform', 'scale(1.1)');
            $(this).find('h4').css('color', '#ff6b6b');
        });

        $('.services-caption').on('touchend touchcancel', function() {
            $(this).css({
                'transform': 'translateY(0)',
                'box-shadow': '0 2px 10px rgba(0,0,0,0.1)'
            });
            $(this).find('img').css('transform', 'scale(1)');
            $(this).find('h4').css('color', '');
        });

        // Efek untuk dots di mobile
        $('.owl-dot').on('touchstart', function() {
            $(this).find('span').css('transform', 'scale(1.3)');
        });

        $('.owl-dot').on('touchend touchcancel', function() {
            if (!$(this).hasClass('active')) {
                $(this).find('span').css('transform', 'scale(1)');
            }
        });
    });
    </script>
</body>

</html>