<!doctype html>
<html class="no-js" lang="zxx">

<head>
    <?php
    include 'include/head.php';
    include 'include/css.php';
    include 'include/db.php'
    ?>
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

                <div class="owl-carousel slider">
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

    <?php include 'include/js.php' ?>
</body>

</html>