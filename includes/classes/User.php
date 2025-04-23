<?php
class User {
    private $db;
    private $id;
    private $username;
    private $email;
    private $role_id;
    private $permissions = [];

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.username = ? AND u.is_active = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // بروزرسانی آخرین ورود
                $this->updateLastLogin($user['id']);

                // ذخیره اطلاعات در سشن
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role_name'];
                
                // لود کردن دسترسی‌ها
                $this->loadPermissions($user['id']);
                
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("خطا در ورود: " . $e->getMessage());
            return false;
        }
    }

    public function register($data) {
        try {
            // بررسی یکتا بودن نام کاربری و ایمیل
            if ($this->isUsernameTaken($data['username']) || $this->isEmailTaken($data['email'])) {
                return false;
            }

            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, first_name, last_name, mobile, role_id) 
                VALUES (?, ?, ?, ?, ?, ?, (SELECT id FROM roles WHERE name = 'user'))
            ");

            return $stmt->execute([
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['first_name'],
                $data['last_name'],
                $data['mobile']
            ]);
        } catch (PDOException $e) {
            error_log("خطا در ثبت نام: " . $e->getMessage());
            return false;
        }
    }

    public function logout() {
        session_destroy();
        return true;
    }

    private function updateLastLogin($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("خطا در بروزرسانی آخرین ورود: " . $e->getMessage());
            return false;
        }
    }

    private function loadPermissions($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT p.name 
                FROM permissions p 
                JOIN role_permissions rp ON p.id = rp.permission_id 
                JOIN users u ON u.role_id = rp.role_id 
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            
            $this->permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $_SESSION['permissions'] = $this->permissions;
            
            return true;
        } catch (PDOException $e) {
            error_log("خطا در بارگذاری دسترسی‌ها: " . $e->getMessage());
            return false;
        }
    }

    public function hasPermission($permission) {
        return in_array($permission, $this->permissions);
    }

    private function isUsernameTaken($username) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }

    private function isEmailTaken($email) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function updateProfile($userId, $data) {
        try {
            $sql = "UPDATE users SET 
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    mobile = ?";
            
            $params = [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['mobile']
            ];

            // اگر رمز عبور ارائه شده باشد
            if (!empty($data['password'])) {
                $sql .= ", password = ?";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $userId;

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("خطا در بروزرسانی پروفایل: " . $e->getMessage());
            return false;
        }
    }

    public function getUserById($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطا در دریافت اطلاعات کاربر: " . $e->getMessage());
            return false;
        }
    }

    public function getAllUsers($page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                ORDER BY u.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("خطا در دریافت لیست کاربران: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalUsers() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("خطا در دریافت تعداد کل کاربران: " . $e->getMessage());
            return 0;
        }
    }

    public function changeUserStatus($userId, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            return $stmt->execute([$status, $userId]);
        } catch (PDOException $e) {
            error_log("خطا در تغییر وضعیت کاربر: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("خطا در حذف کاربر: " . $e->getMessage());
            return false;
        }
    }
}
?>