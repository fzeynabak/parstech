    <!-- Core JS Files -->
    <script src="<?php echo asset('js', 'jquery'); ?>"></script>
    <script src="<?php echo asset('js', 'bootstrap_js'); ?>"></script>
    <script src="<?php echo asset('js', 'sweetalert2_js'); ?>"></script>
    <script src="<?php echo asset('js', 'aos_js'); ?>"></script>

    <!-- Initialize AOS -->
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    </script>

    <!-- Global App Script -->
    <script>
        // تابع نمایش لودینگ
        function showLoading() {
            document.getElementById('loading').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        // تنظیم AJAX برای نمایش لودینگ
        $(document).ajaxStart(function() {
            showLoading();
        }).ajaxStop(function() {
            hideLoading();
        });

        // تنظیم CSRF token برای درخواست‌های AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // نمایش پیام‌های خطا
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: message,
                confirmButtonText: 'باشه'
            });
        }

        // نمایش پیام‌های موفقیت
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'موفقیت',
                text: message,
                confirmButtonText: 'باشه'
            });
        }

        // تابع تایید عملیات
        function confirmAction(message, callback) {
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'بله',
                cancelButtonText: 'خیر'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        }

        // فرمت کردن اعداد به فارسی
        function formatNumber(num) {
            return new Intl.NumberFormat('fa-IR').format(num);
        }

        // تبدیل تاریخ میلادی به شمسی
        function toJalali(gregorianDate) {
            // اینجا می‌توانید از کتابخانه moment-jalaali استفاده کنید
            return gregorianDate; // فعلاً به صورت ساده برمی‌گردانیم
        }

        // آماده‌سازی اولیه
        $(document).ready(function() {
            // مخفی کردن لودینگ اولیه
            hideLoading();

            // فعال‌سازی تولتیپ‌ها
            $('[data-bs-toggle="tooltip"]').tooltip();

            // فعال‌سازی پاپ‌اورها
            $('[data-bs-toggle="popover"]').popover();

            // بستن منوی موبایل با کلیک روی لینک‌ها
            $('.navbar-nav>li>a').on('click', function(){
                $('.navbar-collapse').collapse('hide');
            });
        });
    </script>

    <?php
    // اضافه کردن اسکریپت‌های اضافی صفحه
    if (isset($pageScripts)) {
        foreach ($pageScripts as $script) {
            echo '<script src="' . $script . '"></script>';
        }
    }
    ?>

</body>
</html>