<?php
// تنظیمات پایه
define('BASE_URL', 'https://parstech.com');
define('SITE_NAME', 'پارس تک');
define('ADMIN_EMAIL', 'admin@parstech.com');

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'parstech_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// تنظیمات امنیتی
define('CSRF_TOKEN_SECRET', 'your-secret-key');
define('PASSWORD_PEPPER', 'your-pepper-key');
define('SESSION_LIFETIME', 3600); // 1 hour

// آدرس‌های CDN
define('CDN_URLS', [
    'bootstrap_css' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css',
    'bootstrap_js' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'jquery' => 'https://code.jquery.com/jquery-3.7.0.min.js',
    'fontawesome' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'sweetalert2_css' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css',
    'sweetalert2_js' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js',
    'aos_css' => 'https://unpkg.com/aos@2.3.1/dist/aos.css',
    'aos_js' => 'https://unpkg.com/aos@2.3.1/dist/aos.js'
]);

// تنظیمات آپلود
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/uploads/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// تنظیمات ایمیل
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@parstech.com');
define('SMTP_FROM_NAME', 'پارس تک');

// مسیرهای سیستم
define('INCLUDES_PATH', __DIR__ . '/../includes/');
define('MODULES_PATH', __DIR__ . '/../modules/');
define('TEMPLATES_PATH', __DIR__ . '/../templates/');
define('LOGS_PATH', __DIR__ . '/../logs/');

// تنظیمات سیستم
define('DEFAULT_LANGUAGE', 'fa');
define('DEFAULT_TIMEZONE', 'Asia/Tehran');
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('RECORDS_PER_PAGE', 20);

// کلاس تنظیمات
class Config {
    private static $instance = null;
    private $settings = [];

    private function __construct() {
        // بارگذاری تنظیمات از دیتابیس
        $this->loadSettingsFromDatabase();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadSettingsFromDatabase() {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->query("SELECT * FROM settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->settings[$row['key']] = $row['value'];
            }
        } catch (PDOException $e) {
            error_log("خطا در بارگذاری تنظیمات: " . $e->getMessage());
        }
    }

    public function get($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }

    public function set($key, $value) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("INSERT INTO settings (key, value) VALUES (?, ?) 
                                 ON DUPLICATE KEY UPDATE value = ?");
            $stmt->execute([$key, $value, $value]);
            
            $this->settings[$key] = $value;
            return true;
        } catch (PDOException $e) {
            error_log("خطا در ذخیره تنظیمات: " . $e->getMessage());
            return false;
        }
    }

    public function getAll() {
        return $this->settings;
    }

    public static function getCdnUrl($key) {
        $cdnUrls = CDN_URLS;
        return $cdnUrls[$key] ?? null;
    }
}

// تنظیم timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// تنظیم error reporting
if ($_SERVER['SERVER_NAME'] === 'localhost' || strpos($_SERVER['SERVER_NAME'], 'dev.') === 0) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// تنظیم session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// تابع helper برای لود کردن assets
function asset($type, $key) {
    return Config::getCdnUrl($key);
}
?>