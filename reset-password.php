<?php
session_start();
require_once 'config/config.php';
require_once 'includes/classes/User.php';
require_once 'includes/classes/Auth.php';

// اگر کاربر لاگین کرده است
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'تنظیم رمز عبور جدید';

$database = new Database();
$db = $database->getConnection();

// بررسی اعتبار توکن
$token = $_GET['token'] ?? '';
$validToken = false;
$userId = null;

if ($token) {
    try {
        $stmt = $db->prepare("
            SELECT pr.user_id, u.username, u.email 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0
        ");
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $validToken = true;
            $userId = $result['user_id'];
            $userEmail = $result['email'];
        }
    } catch (Exception $e) {
        error_log("خطا در بررسی توکن: " . $e->getMessage());
    }
}

// پردازش فرم تغییر رمز عبور
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => ''];
    
    if (!$validToken) {
        $response['message'] = 'توکن نامعتبر است یا منقضی شده است.';
    } else {
        $password = trim($_POST['password']);
        $passwordConfirm = trim($_POST['password_confirm']);
        
        // اعتبارسنجی رمز عبور
        if (strlen($password) < 8) {
            $response['message'] = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
        } elseif ($password !== $passwordConfirm) {
            $response['message'] = 'رمز عبور و تکرار آن مطابقت ندارند.';
        } else {
            try {
                // بروزرسانی رمز عبور
                $stmt = $db->prepare("
                    UPDATE users 
                    SET password = ?, password_changed_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
                
                // غیرفعال کردن توکن
                $stmt = $db->prepare("
                    UPDATE password_resets 
                    SET used = 1, used_at = NOW() 
                    WHERE token = ?
                ");
                $stmt->execute([$token]);
                
                // ثبت لاگ
                $stmt = $db->prepare("
                    INSERT INTO activity_logs (user_id, activity_type, description, ip_address) 
                    VALUES (?, 'password_reset', 'تغییر رمز عبور از طریق بازیابی', ?)
                ");
                $stmt->execute([$userId, $_SERVER['REMOTE_ADDR']]);
                
                $response = [
                    'status' => 'success',
                    'message' => 'رمز عبور با موفقیت تغییر کرد. اکنون می‌توانید وارد شوید.'
                ];
            } catch (Exception $e) {
                error_log("خطا در تغییر رمز عبور: " . $e->getMessage());
                $response['message'] = 'خطا در تغییر رمز عبور. لطفاً دوباره تلاش کنید.';
            }
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
                    <h2 class="text-center mb-4">تنظیم رمز عبور جدید</h2>
                    
                    <?php if (!$validToken): ?>
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            لینک بازیابی نامعتبر است یا منقضی شده است.
                            <div class="mt-3">
                                <a href="forgot-password.php" class="btn btn-outline-danger">
                                    درخواست لینک جدید
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form id="resetPasswordForm" method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">رمز عبور جدید</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           required minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    رمز عبور باید حداقل ۸ کاراکتر باشد.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirm" class="form-label">تکرار رمز عبور جدید</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password_confirm" 
                                           name="password_confirm" required minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-key me-2"></i>
                                تغییر رمز عبور
                            </button>
                            
                            <div class="text-center">
                                <a href="login.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-right"></i>
                                    بازگشت به صفحه ورود
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // نمایش/مخفی کردن رمز عبور
    function togglePasswordVisibility(inputId, buttonId) {
        $(buttonId).on('click', function() {
            const input = $(inputId);
            const icon = $(this).find('i');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    }
    
    togglePasswordVisibility('#password', '#togglePassword');
    togglePasswordVisibility('#password_confirm', '#togglePasswordConfirm');

    // ارسال فرم تغییر رمز عبور
    $('#resetPasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        
        submitBtn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span>در حال تغییر رمز عبور...');
        
        $.ajax({
            url: 'reset-password.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'موفقیت',
                        text: response.message,
                        confirmButtonText: 'ورود به سیستم'
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