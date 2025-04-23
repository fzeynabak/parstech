<?php
session_start();
require_once 'config/config.php';
require_once 'includes/classes/User.php';
require_once 'includes/classes/Auth.php';
require_once 'includes/classes/Mailer.php';

// اگر کاربر لاگین کرده است
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'بازیابی رمز عبور';

// پردازش درخواست بازیابی رمز عبور
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $auth = new Auth($db);
    $mailer = new Mailer();
    
    $response = ['status' => 'error', 'message' => ''];
    
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        $response['message'] = 'لطفاً یک ایمیل معتبر وارد کنید.';
    } else {
        try {
            // بررسی وجود کاربر
            $stmt = $db->prepare("SELECT id, username, first_name, last_name FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // ایجاد توکن بازیابی
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $stmt = $db->prepare("
                    INSERT INTO password_resets (user_id, token, expires_at) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$user['id'], $token, $expiry]);
                
                // ارسال ایمیل بازیابی
                $resetLink = BASE_URL . '/reset-password.php?token=' . $token;
                $fullName = trim($user['first_name'] . ' ' . $user['last_name']);
                
                $emailBody = "
                    <h2>درخواست بازیابی رمز عبور</h2>
                    <p>سلام {$fullName} عزیز،</p>
                    <p>درخواست بازیابی رمز عبور برای حساب کاربری شما در سیستم حسابداری پارس تک ثبت شده است.</p>
                    <p>برای تنظیم رمز عبور جدید، لطفاً روی لینک زیر کلیک کنید:</p>
                    <p><a href='{$resetLink}'>{$resetLink}</a></p>
                    <p>این لینک تا یک ساعت معتبر است.</p>
                    <p>اگر شما درخواست بازیابی رمز عبور نداده‌اید، می‌توانید این ایمیل را نادیده بگیرید.</p>
                    <br>
                    <p>با احترام،<br>تیم پشتیبانی پارس تک</p>
                ";
                
                if ($mailer->sendMail(
                    $email,
                    $fullName,
                    'بازیابی رمز عبور - سیستم حسابداری پارس تک',
                    $emailBody
                )) {
                    $response = [
                        'status' => 'success',
                        'message' => 'لینک بازیابی رمز عبور به ایمیل شما ارسال شد. لطفاً صندوق ورودی خود را بررسی کنید.'
                    ];
                    
                    // ثبت لاگ
                    $stmt = $db->prepare("
                        INSERT INTO activity_logs (user_id, activity_type, description, ip_address) 
                        VALUES (?, 'password_reset_request', 'درخواست بازیابی رمز عبور', ?)
                    ");
                    $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);
                } else {
                    $response['message'] = 'خطا در ارسال ایمیل. لطفاً دوباره تلاش کنید.';
                }
            } else {
                // برای امنیت بیشتر، همان پیام موفقیت را نمایش می‌دهیم
                $response = [
                    'status' => 'success',
                    'message' => 'اگر این ایمیل در سیستم ثبت شده باشد، لینک بازیابی برای شما ارسال خواهد شد.'
                ];
            }
        } catch (Exception $e) {
            error_log("خطا در بازیابی رمز عبور: " . $e->getMessage());
            $response['message'] = 'خطایی رخ داده است. لطفاً دوباره تلاش کنید.';
        }
    }
    
    echo json_encode($response);
    exit;
}

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
                    <h2 class="text-center mb-4">بازیابی رمز عبور</h2>
                    
                    <form id="forgotPasswordForm" method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label">ایمیل</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       required autofocus placeholder="ایمیل خود را وارد کنید">
                            </div>
                            <div class="form-text">
                                لینک بازیابی رمز عبور به این ایمیل ارسال خواهد شد.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-paper-plane me-2"></i>
                            ارسال لینک بازیابی
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">
                                <a href="login.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-right"></i>
                                    بازگشت به صفحه ورود
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#forgotPasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        
        submitBtn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span>در حال ارسال...');
        
        $.ajax({
            url: 'forgot-password.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'ایمیل ارسال شد',
                        text: response.message,
                        confirmButtonText: 'باشه'
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: response.message,
                        confirmButtonText: 'باشه'
                    });
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'مشکلی در ارتباط با سرور پیش آمده است.',
                    confirmButtonText: 'باشه'
                });
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>