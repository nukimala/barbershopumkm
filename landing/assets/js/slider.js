$(document).ready(function() {
        var owl = $('.slider').owlCarousel({
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