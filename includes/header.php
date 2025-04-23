<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="سیستم حسابداری پارس تک - مدیریت هوشمند کسب و کار">
    <meta name="keywords" content="حسابداری، مدیریت مالی، نرم افزار حسابداری، پارس تک">
    <meta name="author" content="Parstech">
    
    <title><?php echo SITE_NAME; ?> - <?php echo $pageTitle ?? 'سیستم حسابداری هوشمند'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="<?php echo asset('css', 'bootstrap_css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css', 'fontawesome'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css', 'sweetalert2_css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css', 'aos_css'); ?>">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    
    <!-- Custom Styles for RTL -->
    <style>
        @font-face {
            font-family: 'Vazir';
            src: url('<?php echo BASE_URL; ?>/assets/fonts/Vazir.eot');
            src: url('<?php echo BASE_URL; ?>/assets/fonts/Vazir.eot?#iefix') format('embedded-opentype'),
                 url('<?php echo BASE_URL; ?>/assets/fonts/Vazir.woff2') format('woff2'),
                 url('<?php echo BASE_URL; ?>/assets/fonts/Vazir.woff') format('woff'),
                 url('<?php echo BASE_URL; ?>/assets/fonts/Vazir.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Vazir', Tahoma, Arial, sans-serif;
        }
    </style>
    
    <?php
    // اضافه کردن استایل‌های اضافی صفحه
    if (isset($pageStyles)) {
        foreach ($pageStyles as $style) {
            echo '<link rel="stylesheet" href="' . $style . '">';
        }
    }
    ?>
</head>
<body class="<?php echo $bodyClass ?? ''; ?>">
    
    <!-- Loading Indicator -->
    <div id="loading" class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">در حال بارگذاری...</span>
        </div>
    </div>

    <?php
    // نمایش پیام‌های سیستم
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{$message['type']}',
                    title: '{$message['title']}',
                    text: '{$message['text']}',
                    confirmButtonText: 'باشه'
                });
            });
        </script>";
    }
    ?>