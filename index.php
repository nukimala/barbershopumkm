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
        <!--? Header Start -->
        <?php include 'include/header.php' ?>
        <!-- Header End -->
    </header>
    <main>
        <!--? slider Area Start-->
        <div class="slider-area position-relative fix">
            <div class="slider-active">
                <!-- Single Slider -->
                <div class="single-slider slider-height d-flex align-items-center">
                    <div class="container">
                        <div class="row">
                            <div class="col-xl-8 col-lg-9 col-md-11 col-sm-10">
                                <div class="hero__caption">
                                    <span data-animation="fadeInUp" data-delay="0.2s">Barber Lokal, Gaya Professional</span>
                                    <h1 data-animation="fadeInUp" data-delay="0.5s">Gaya Rambut Terbaik Untuk Tampil Percaya Diri</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Single Slider -->
                <div class="single-slider slider-height d-flex align-items-center">
                    <div class="container">
                        <div class="row">
                            <div class="col-xl-8 col-lg-9 col-md-11 col-sm-10">
                                <div class="hero__caption">
                                    <span data-animation="fadeInUp" data-delay="0.2s">Barber Lokal, Gaya Professional</span>
                                    <h1 data-animation="fadeInUp" data-delay="0.5s">Gaya Rambut Terbaik Untuk Tampil Percaya Diri</h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Arrow -->
            <div class="thumb-content-box">
                <div class="thumb-content">
                    <h3>Pesan Jadwal Cukur Sekarang</h3>
                    <a href="reservasi/"> <i class="fas fa-long-arrow-alt-right"></i></a>
                </div>
            </div>
        </div>
        <!-- slider Area End-->
        <!--? About Area Start -->
        <section class="about-area section-padding30 position-relative">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-11">
                        <!-- about-img -->
                        <div class="about-img ">
                            <img src="assets/img/gallery/owners.png" alt="">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="about-caption">
                            <!-- Section Tittle -->
                            <div class="section-tittle section-tittle3 mb-35">
                                <span>OWNER</span>
                                <h2>PAK SUPRAPTO</h2>
                            </div>
                            <p class="pera-top mb-50">Pak Suprapto adalah pemilik dari usaha UMKM barbershop ini, beliau telah menekuni bidang usaha jasa potong rambut sejak 10 tahun lalu. 
                                Berawal dari beliau mengantarkan anaknya untuk potong rambut, dari situlah beliau mendapatkan rasa minat terhadap bidang potong rambut, 
                                maka sejak itu mulailah beliau mengasah skill nya dalam bidang potong rambut hingga kemudian pada tahun 2015 akhirnya beliau memutuskan untuk membuka usaha jasa potong rambut yang diberi nama "Potong Rambut Pak To". 
                                Berawal dari membuka usaha di kontrakan yang beliau sewa, hingga kini telah berdiri didepan rumah sendiri.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- About Shape -->
            <div class="about-shape">
                <img src="assets/img/gallery/about-shape.png" alt="">
            </div>
        </section>
        <!-- About-2 Area End -->
         
        <!-- Best Pricing Area Start -->
        <?php

        //  QUERY UNTUK MENGAMBIL DATA
        
        // Ambil data dari tabel 'model'
        $sql_model = "SELECT nama_model, harga_model FROM model ORDER BY harga_model ASC";
        $result_model = $conn->query($sql_model);

        // Ambil data dari tabel 'layanan'
        $sql_layanan = "SELECT nama_layanan, harga_layanan FROM layanan ORDER BY harga_layanan ASC";
        $result_layanan = $conn->query($sql_layanan);

        ?>

        <div class="best-pricing section-padding2 position-relative">
            <div class="container">
                <div class="row justify-content-end">
                    <div class="col-xl-7 col-lg-7">
                        <div class="section-tittle mb-50">
                            <span>dapatkan dengan harga yang ramah dikantong!</span>
                            <h2>gaya kekinian, layanan memanjakan</h2>
                        </div>
                        <div class="row">

                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="pricing-list">
                                    <ul>
                                        <?php
                                        if ($result_model && $result_model->num_rows > 0) {
                                            // Loop untuk setiap baris data model
                                            while ($row = $result_model->fetch_assoc()) {
                                                $nama = htmlspecialchars($row["nama_model"]);
                                                // Format harga. (int) untuk membulatkan seperti di HTML asli Anda ($25, bukan $25.00)
                                                $harga = 'RP ' . (int) $row["harga_model"];

                                                // Cetak list item
                                                echo "<li> <span class='nama-layanan'>$nama</span> <span>$harga</span> </li>";
                                            }
                                        } else {
                                            echo "<li>Belum ada data model.</li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="pricing-list">
                                    <ul>
                                        <?php
                                        if ($result_layanan && $result_layanan->num_rows > 0) {
                                            // Loop untuk setiap baris data layanan
                                            while ($row = $result_layanan->fetch_assoc()) {
                                                $nama = htmlspecialchars($row["nama_layanan"]);
                                                $harga = 'RP ' . (int) $row["harga_layanan"];

                                                // Cetak list item
                                                echo "<li> <span class='nama-layanan'>$nama</span> <span>$harga</span> </li>";
                                            }
                                        } else {
                                            echo "<li>Belum ada data layanan.</li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="pricing-img">
                <img class="pricing-img1" src="assets/img/gallery/listbg.png" alt="">
                <img class="pricing-img2" src="assets/img/gallery/list.png" alt="">
            </div>
        </div>
        <!-- Best Pricing Area End -->

        <!-- Cut Details Start -->
        <?php

//  QUERY UNTUK MENGAMBIL PESAN
// -------------------------------------------------
// Kita ambil pesan yang 'Ditampilkan' dan urutkan berdasarkan waktu kirim terbaru
$sql_pesan = "SELECT nama_pengirim, isi_pesan FROM pesan 
              WHERE kategori_pesan = 'Ditampilkan' 
              ORDER BY waktu_kirim DESC";

$result_pesan = $conn->query($sql_pesan);

?>

<div class="cut-details section-bg section-padding2" data-background="assets/img/gallery/section_bg.png">
    <div class="container">
        <div class="cut-active dot-style">

            <?php
            // 4. Lakukan Pengecekan dan Loop Data
            if ($result_pesan && $result_pesan->num_rows > 0) {
                
                // Loop untuk setiap baris data pesan
                while($row = $result_pesan->fetch_assoc()) {
                    
                    // Amankan data sebelum ditampilkan (mencegah XSS attack)
                    $nama_pengirim = htmlspecialchars($row["nama_pengirim"]);
                    $isi_pesan = htmlspecialchars($row["isi_pesan"]);
            ?>

                    <div class="single-cut">
                        <div class="cut-icon mb-20">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="50px" height="50px">
                                <image x="0px" y="0px" width="50px" height="50px"
                                    xlink:href="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAQAAAC0NkA6AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QA/4ePzL8AAAAHdElNRQfkBQ4MDDIERuyfAAADc0lEQVRYw7WYXWxTZRjH/+e0ikhh7QgfiYJZZ7bhBC6mU0LQ6DBADNGYLEaNJGpi4jTEQczYjQG8EL2ThAUTvTRGBwmECyBA+XRKHJpUL1yXFseWbe1ixgZCSAg/Lmo9bXe+up0+/5vT//Oc9/ee8z7nqwbyGbVqUL2iiuiurmtMKf2tu/52DXtW1OhVtekFRZTSkCY1rYcV0VI1arl+VULH9JvnGLhpHT/wD728z+M22QVs5ksyJOlkgds4zqlWEgzSQQ3uEzF4ju8ZpZsHK4NEOcgo7xL2AFhq4CgDtPmHPEWGg0R9AwrayjD77CY2s/RtsrRXDMhrCSc5wyIvyE6GaJ4lQogQB/idZW6QjxlkxRwQee0lWdoupec0a9uqlauHM8VrYyXqyLIuEIQIcYLPZ0JC/EJnQIh8C4xYDV0wO0hgBAgRm0kxrxhSS46mQBFCHKa7GLKbbwNHiCayRAqQCBMBdVW5etlRgGzjWFUQYgMDGHnIaZfbSIxTWNFP3MGzl0GaViQWMVXoAhv9SGn0O3hO+oLPkHiZ4y5FacrD3nPSJn5GptbrJ7+P+VnERa3VA6bWKFlFyC0NqdFUXOkqQqS06kwt1XhVIeNaZiqqSZeS0z4955jWwrBCuudSskvSRklSTDEXzznuaJ74l/m+rt4Wm3Zt8WxhcYAOU5Na7OuwJ3165RHTlKlhrfQFaZckXfH0ymOFhsNKaZX6POYSU7v2SZJ6XTz7aFJKbKfH9ZxuLLp9pIk5evaKM4ZMndXzrjOJ/7+V0Uv/rYKdZx9tOi8Jg3HqPY+kn66iGdt59jrMe/nnyX52V+mhVcsNFuchLWQqeH+vRB9xCBVeJC7xZhUQYTKstyBb+JNQ4JB3OJvfKhgJPggYEeEaz5ZCmpgI4H2+WD18Xdi2zG4uBbj8r5GxvtUs2+AE+wNCrCZHq/W7OBUlya4AEI9yjbeKnfL0VbrmiIgzyCelXnnJI/zBV3NYm6cZoaPcnVkW4yQXZtVpBp1keWVmxq7YpIsc2ys8nmbOc5k6u5zTLqtIkOQNn/eBer4hx4eY9nm3XbdwkTSfun67PEQ7R8ixh1rnKsPj/64WbdPrmtI5XdGAruqGrmu+IlquBj2hDXpGl/WdDumm2yBeEEky9KRe1Go16jFFFNVt3dSEUvpLfbqgae8B7gNdcvnkrRzZ4gAAAABJRU5ErkJggg==" />
                            </svg>
                        </div>
                        <div class="cut-descriptions">
                            <p><?php echo $isi_pesan; ?></p>
                            <span><?php echo $nama_pengirim; ?></span>
                        </div>
                    </div>

            <?php
                } // Akhir dari loop while
            
            } else {
                // Jika tidak ada pesan yang 'Ditampilkan', beri tahu pengguna
                echo "<div class='single-cut'>
                        <div class='cut-descriptions'>
                            <p style='color: white;'>Belum ada testimoni untuk ditampilkan saat ini.</p>
                        </div>
                      </div>";
            }
            ?>

        </div>
    </div>
</div>

<?php
// 5. Tutup Koneksi
$conn->close();
?>
        <!-- Cut Details End -->
    </main>
    <footer>
        <!--? Footer Start-->
        <?php include 'include/footer.php' ?>
        <!-- Footer End-->
    </footer>
    <!-- Scroll Up -->
    <div id="back-top">
        <a title="Go to Top" href="#"> <i class="fas fa-level-up-alt"></i></a>
    </div>
    <?php
    include 'include/js.php' ?>
</body>

</html>