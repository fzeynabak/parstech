<?php
class AuthMiddleware {
    private $auth;
    private $publicPaths = [
        'login.php',
        'register.php',
        'forgot-password.php',
        'reset-password.php',
        'landing.php',
        'api/public'
    ];
    
    public function __construct($auth) {
        $this->auth = $auth;
    }
    
    public function handle() {
        // بررسی مسیرهای عمومی
        $currentPath = basename($_SERVER['PHP_SELF']);
        if (in_array($currentPath, $this->publicPaths)) {
            return true;
        }
        
        // بررسی API path
        if (strpos($_SERVER['REQUEST_URI'], 'api/public') !== false) {
            return true;
        }
        
        // بررسی وضعیت ورود
        if (!$this->auth->isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'نشست شما منقضی شده است. لطفاً دوباره وارد شوید.'
                ]);
                exit;
            } else {
                header('Location: login.php');
                exit;
            }
        }
        
        // بررسی تایم‌اوت سشن
        $this->auth->checkSessionTimeout();
        
        // بررسی CSRF برای درخواست‌های POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$this->isApiRequest()) {
            $this->validateCSRF();
        }
        
        return true;
    }
    
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    private function isApiRequest() {
        return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
    }
    
    private function validateCSRF() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !$this->auth->validateCSRF($token)) {
            if ($this->isAjaxRequest()) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'توکن CSRF نامعتبر است.'
                ]);
                exit;
            } else {
                header('Location: errors/403.php');
                exit;
            }
        }
    }
}
?>