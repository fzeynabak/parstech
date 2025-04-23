<?php
session_start();
require_once 'config/config.php';
require_once 'includes/classes/User.php';
require_once 'includes/classes/Auth.php';

// اگر کاربر قبلاً لاگین کرده است
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// پردازش فرم ورود
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);
    
    $response = ['status' => 'error', 'message' => ''];
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    try {
        if ($auth->login($username, $password, $remember)) {
            $response = [
                'status' => 'success',
                'message' => 'ورود موفقیت‌آمیز',
                'redirect' => 'index.php'
            ];
        } else {
            $response['message'] = 'نام کاربری یا رمز عبور اشتباه است.';
        }
    } catch (Exception $e) {
        $response['message'] = 'خطا در ورود به سیستم. لطفاً دوباره تلاش کنید.';
        error_log($e->getMessage());
    }
    
    echo json_encode($response);
    exit;
}

$pageTitle = 'ورود به سیستم';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="text-center mb-4">
                <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" height="60">
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">ورود به سیستم</h2>
                    
                    <form id="loginForm" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">نام کاربری یا ایمیل</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="password" class="form-label">رمز عبور</label>
                                <a href="forgot-password.php" class="text-decoration-none small">
                                    رمز عبور را فراموش کرده‌اید؟
                                </a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    مرا به خاطر بسپار
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            ورود به سیستم
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">حساب کاربری ندارید؟ 
                                <a href="register.php" class="text-decoration-none">ثبت نام کنید</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="text-decoration-none">
                    <i class="fas fa-arrow-right"></i>
                    بازگشت به صفحه اصلی
                </a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // نمایش/مخفی کردن رمز عبور
    $('#togglePassword').on('click', function() {
        const passwordInput = $('#password');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // ارسال فرم ورود
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>در حال ورود...');
        
        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'خوش آمدید!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: response.message,
                        confirmButtonText: 'باشه'
                    });
                    submitBtn.prop('disabled', false).html('<i class="fas fa-sign-in-alt me-2"></i>ورود به سیستم');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'مشکلی در ارتباط با سرور پیش آمده است.',
                    confirmButtonText: 'باشه'
                });
                submitBtn.prop('disabled', false).html('<i class="fas fa-sign-in-alt me-2"></i>ورود به سیستم');
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>