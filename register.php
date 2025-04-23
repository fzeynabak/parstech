<?php
session_start();
require_once 'config/config.php';
require_once 'includes/classes/User.php';

// اگر کاربر لاگین کرده است، به داشبورد هدایت شود
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'ثبت نام';
require_once 'includes/header.php';

// پردازش فرم ثبت نام
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    $response = ['status' => 'error', 'message' => ''];

    // اعتبارسنجی داده‌ها
    if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
        $response['message'] = 'لطفاً تمام فیلدهای الزامی را پر کنید.';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'لطفاً یک ایمیل معتبر وارد کنید.';
    } elseif (strlen($_POST['password']) < 6) {
        $response['message'] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    } elseif ($_POST['password'] !== $_POST['password_confirm']) {
        $response['message'] = 'رمز عبور و تکرار آن مطابقت ندارند.';
    } else {
        // ثبت نام کاربر
        $result = $user->register([
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'mobile' => $_POST['mobile'] ?? ''
        ]);

        if ($result) {
            $response = [
                'status' => 'success',
                'message' => 'ثبت نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید.'
            ];
        } else {
            $response['message'] = 'خطا در ثبت نام. لطفاً دوباره تلاش کنید.';
        }
    }

    echo json_encode($response);
    exit;
}
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">ثبت نام در پارس تک</h2>
                    
                    <form id="registerForm" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">نام</label>
                                <input type="text" class="form-control" id="first_name" name="first_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">نام خانوادگی</label>
                                <input type="text" class="form-control" id="last_name" name="last_name">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">نام کاربری <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">ایمیل <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label">شماره موبایل</label>
                            <input type="tel" class="form-control" id="mobile" name="mobile" 
                                   pattern="^09[0-9]{9}$" placeholder="مثال: 09123456789">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">رمز عبور <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">رمز عبور باید حداقل ۶ کاراکتر باشد.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">تکرار رمز عبور <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">شرایط و قوانین</a> را می‌پذیرم
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">ثبت نام</button>
                        
                        <div class="text-center">
                            <p class="mb-0">قبلاً ثبت نام کرده‌اید؟ <a href="login.php">ورود به سیستم</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال شرایط و قوانین -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">شرایط و قوانین استفاده</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- محتوای شرایط و قوانین -->
                <h6>۱. پذیرش شرایط استفاده</h6>
                <p>با استفاده از خدمات پارس تک، شما موافقت می‌کنید که...</p>
                
                <h6>۲. حریم خصوصی</h6>
                <p>اطلاعات شخصی شما مطابق با سیاست حفظ حریم خصوصی ما محافظت می‌شود...</p>
                
                <!-- ادامه شرایط و قوانین -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'register.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'تبریک!',
                        text: response.message,
                        confirmButtonText: 'ورود به سیستم'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: response.message,
                        confirmButtonText: 'باشه'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'مشکلی در ارتباط با سرور پیش آمده است.',
                    confirmButtonText: 'باشه'
                });
            }
        });
    });

    // اعتبارسنجی فرم
    const validateForm = () => {
        const password = $('#password').val();
        const confirmPassword = $('#password_confirm').val();
        
        if (password !== confirmPassword) {
            $('#password_confirm')[0].setCustomValidity('رمز عبور و تکرار آن مطابقت ندارند');
        } else {
            $('#password_confirm')[0].setCustomValidity('');
        }
    };

    $('#password, #password_confirm').on('input', validateForm);
});
</script>

<?php require_once 'includes/footer.php'; ?>