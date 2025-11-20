$(document).ready(function(){
    // Image Slider
    let currentSlide = 0;
    const slides = $('.slide');
    const slideCount = slides.length;

    if (slideCount > 0) {
        slides.eq(currentSlide).show();
        setInterval(function() {
            slides.eq(currentSlide).hide();
            currentSlide = (currentSlide + 1) % slideCount;
            slides.eq(currentSlide).show();
        }, 3000);
    }

    // Form Validation
    $('#order-form').submit(function(event) {
        let name = $('#name').val();
        let model = $('#model').val();

        if (name === '') {
            alert('Nama wajib diisi.');
            event.preventDefault();
            return;
        }

        // Validasi baru untuk model
        if (model === null || model === '') {
            alert('Anda harus memilih model potongan rambut.');
            event.preventDefault();
            return;
        }
    });
});