$(document).ready(function() {
    // راه‌اندازی AOS برای انیمیشن‌ها
    AOS.init({
        duration: 800,
        offset: 100,
        once: true
    });

    // تغییر استایل نوار ناوبری هنگام اسکرول
    $(window).scroll(function() {
        if ($(window).scrollTop() > 50) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });

    // اسکرول نرم برای لینک‌های داخل صفحه
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.hash);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 800);
        }
    });

    // ارسال فرم تماس
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        // اینجا می‌توانید کد ارسال فرم به سرور را اضافه کنید
        
        Swal.fire({
            icon: 'success',
            title: 'پیام شما با موفقیت ارسال شد',
            text: 'به زودی با شما تماس خواهیم گرفت.',
            confirmButtonText: 'باشه'
        });
        
        this.reset();
    });

    // نمایش انیمیشن برای تصاویر
    $('.screenshot-card').hover(
        function() {
            $(this).find('img').css('transform', 'scale(1.1)');
        },
        function() {
            $(this).find('img').css('transform', 'scale(1)');
        }
    );
});