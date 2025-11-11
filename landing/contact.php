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
                                <h2>Hubungi Kami</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section class="contact-section">
            <div class="container">
                <div class="mb-5 pb-4">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d21600.686848089605!2d111.98124287244755!3d-7.5402571914161625!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xaace6e88201e199f%3A0x6ccd7a68aceaaa35!2sPotong%20Rambut%20Pak%20To!5e0!3m2!1sid!2sid!4v1761825430350!5m2!1sid!2sid" width="100%" height="480" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h2 class="contact-title">Berikan Kritik Dan Saran Anda!</h2>
                    </div>
                    <div class="col-lg-8">
                        <form class="form-contact contact_form" action="contact_process.php" method="post" id="contactForm" novalidate="novalidate">
                            <div class="row">
                                <div class="col-12">
                                     <div class="form-group">
                                        <input class="form-control valid" name="name" id="name" type="text" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Masukan nama Anda'" placeholder="Masukan nama Anda" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <textarea class="form-control w-100" name="message" id="message" cols="30" rows="9" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Masukan isi pesan'" placeholder=" Masukan isi pesan" required style="padding-top: 15px;"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                <button type="submit" class="button button-contactForm boxed-btn">Kirim Pesan</button>
                            </div>
                            <div id="form-messages" class="mt-3"></div>
                        </form>
                    </div>
                    <div class="col-lg-3 offset-lg-1">
                        <div class="media contact-info">
                            <span class="contact-info__icon"><i class="ti-home"></i></span>
                            <div class="media-body">
                                <h3>Balonggebang, Gondang</h3>
                                <p>Jl. Lawu Gg. 1</p>
                            </div>
                        </div>
                        <div class="media contact-info">
                            <span class="contact-info__icon"><i class="ti-tablet"></i></span>
                            <div class="media-body">
                                <h3>089525478926</h3>
                                <p>Setiap Hari 16.00 - 21.00</p>
                            </div>
                        </div>
                        <div class="media contact-info">
                            <span class="contact-info__icon"><i class="ti-email"></i></span>
                            <div class="media-body">
                                <h3>paktocukur@gmail.com</h3>
                                <p>Hubungi E-Mail Kami!</p>
                            </div>
                        </div>
                    </div>
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
    <script src="./assets/js/jquery.nice-select.min.js"></script>
    <script src="./assets/js/jquery.sticky.js"></script>
    <script src="./assets/js/jquery.magnific-popup.js"></script>
    <script src="./assets/js/jquery.form.js"></script>
    <script src="./assets/js/jquery.validate.min.js"></script>
    <script src="./assets/js/mail-script.js"></script>
    <script src="./assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="./assets/js/plugins.js"></script>
    <script src="./assets/js/main.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const contactForm = document.getElementById('contactForm');
        const formMessages = document.getElementById('form-messages');

        if (typeof $ !== 'undefined') {
            $('#contactForm').off('submit');
        }

        contactForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(contactForm);
            const action = contactForm.getAttribute('action');

            formMessages.style.color = 'black';
            formMessages.textContent = 'Mengirim pesan...';
            
            fetch(action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    formMessages.style.color = 'green';
                    formMessages.textContent = data.message;
                    contactForm.reset(); 
                } else {
                    formMessages.style.color = 'red';
                    formMessages.textContent = data.message;
                }
            })
            .catch(error => {
                formMessages.style.color = 'red';
                formMessages.textContent = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
                console.error('Error:', error);
            });
        });
    });
    </script>

</body>
</html>