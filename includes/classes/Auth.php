<?php
class Auth {
    private $db;
    private $user;
    private $sessionDuration = 3600; // 1 hour
    private $rememberMeDuration = 2592000; // 30 days
    
    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
        
        // شروع یا ادامه سشن
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // بررسی Remember Me
        $this->checkRememberMeToken();
    }
    
    public function login($username, $password, $remember = false) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // ایجاد سشن
                $this->createSession($user);
                
                // ایجاد توکن Remember Me
                if ($remember) {
                    $this->createRememberMeToken($user['id']);
                }
                
                // بروزرسانی آخرین ورود
                $this->updateLastLogin($user['id']);
                
                // ثبت لاگ ورود
                $this->logLogin($user['id'], true);
                
                return true;
            }
            
            // ثبت تلاش ناموفق
            $this->logFailedAttempt($username);
            
            return false;
        } catch (Exception $e) {
            error_log("خطا در ورود: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function logout() {
        // حذف توکن Remember Me
        if (isset($_COOKIE['remember_token'])) {
            $this->removeRememberMeToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // حذف سشن
        session_destroy();
        
        return true;
    }
    
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role_name'];
        $_SESSION['last_activity'] = time();
        
        // ایجاد CSRF توکن
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // لود کردن دسترسی‌ها
        $this->loadUserPermissions($user['id']);
    }
    
    private function loadUserPermissions($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT p.name 
                FROM permissions p 
                JOIN role_permissions rp ON p.id = rp.permission_id 
                JOIN users u ON u.role_id = rp.role_id 
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $_SESSION['permissions'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("خطا در بارگذاری دسترسی‌ها: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function createRememberMeToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + $this->rememberMeDuration);
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO remember_tokens (user_id, token, expires_at) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$userId, $token, $expiry]);
            
            setcookie('remember_token', $token, time() + $this->rememberMeDuration, '/', '', true, true);
        } catch (Exception $e) {
            error_log("خطا در ایجاد توکن Remember Me: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function checkRememberMeToken() {
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
            try {
                $stmt = $this->db->prepare("
                    SELECT u.* 
                    FROM users u 
                    JOIN remember_tokens rt ON u.id = rt.user_id 
                    WHERE rt.token = ? AND rt.expires_at > NOW() AND u.is_active = 1
                ");
                $stmt->execute([$_COOKIE['remember_token']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    $this->createSession($user);
                    
                    // تمدید توکن
                    $this->refreshRememberMeToken($_COOKIE['remember_token']);
                }
            } catch (Exception $e) {
                error_log("خطا در بررسی توکن Remember Me: " . $e->getMessage());
            }
        }
    }
    
    private function refreshRememberMeToken($token) {
        try {
            $expiry = date('Y-m-d H:i:s', time() + $this->rememberMeDuration);
            
            $stmt = $this->db->prepare("
                UPDATE remember_tokens 
                SET expires_at = ? 
                WHERE token = ?
            ");
            $stmt->execute([$expiry, $token]);
        } catch (Exception $e) {
            error_log("خطا در تمدید توکن Remember Me: " . $e->getMessage());
        }
    }
    
    private function removeRememberMeToken($token) {
        try {
            $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE token = ?");
            $stmt->execute([$token]);
        } catch (Exception $e) {
            error_log("خطا در حذف توکن Remember Me: " . $e->getMessage());
        }
    }
    
    private function updateLastLogin($userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET last_login = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("خطا در بروزرسانی آخرین ورود: " . $e->getMessage());
        }
    }
    
    private function logLogin($userId, $success) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_logs (user_id, ip_address, user_agent, success) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'],
                $success
            ]);
        } catch (Exception $e) {
            error_log("خطا در ثبت لاگ ورود: " . $e->getMessage());
        }
    }
    
    private function logFailedAttempt($username) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_attempts (username, ip_address, attempted_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$username, $_SERVER['REMOTE_ADDR']]);
        } catch (Exception $e) {
            error_log("خطا در ثبت تلاش ناموفق: " . $e->getMessage());
        }
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public function hasPermission($permission) {
        return isset($_SESSION['permissions']) && in_array($permission, $_SESSION['permissions']);
    }
    
    public function requirePermission($permission) {
        if (!$this->hasPermission($permission)) {
            header('Location: errors/403.php');
            exit();
        }
    }
    
    public function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public function regenerateSession() {
        // ذخیره داده‌های مهم سشن
        $temp = [];
        foreach (['user_id', 'username', 'role', 'permissions'] as $key) {
            if (isset($_SESSION[$key])) {
                $temp[$key] = $_SESSION[$key];
            }
        }
        
        // بازسازی سشن
        session_regenerate_id(true);
        
        // بازگرداندن داده‌ها
        foreach ($temp as $key => $value) {
            $_SESSION[$key] = $value;
        }
        
        // بروزرسانی زمان آخرین فعالیت
        $_SESSION['last_activity'] = time();
    }
    
    public function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > $this->sessionDuration)) {
            $this->logout();
            header('Location: login.php?timeout=1');
            exit();
        }
        $_SESSION['last_activity'] = time();
    }
}
?>