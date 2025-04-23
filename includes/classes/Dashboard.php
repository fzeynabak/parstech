<?php
class Dashboard {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getStats() {
        try {
            // آمار امروز
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            
            // درآمد امروز
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(amount), 0) as today_income 
                FROM transactions 
                WHERE type = 'income' 
                AND DATE(created_at) = ?
            ");
            $stmt->execute([$today]);
            $todayIncome = $stmt->fetchColumn();
            
            // درآمد دیروز
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(amount), 0) as yesterday_income 
                FROM transactions 
                WHERE type = 'income' 
                AND DATE(created_at) = ?
            ");
            $stmt->execute([$yesterday]);
            $yesterdayIncome = $stmt->fetchColumn();
            
            // محاسبه درصد تغییر درآمد
            $incomeTrend = $yesterdayIncome != 0 ? 
                          (($todayIncome - $yesterdayIncome) / $yesterdayIncome) * 100 : 
                          100;
            
            // تعداد فاکتورهای امروز
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as today_invoices 
                FROM invoices 
                WHERE DATE(created_at) = ?
            ");
            $stmt->execute([$today]);
            $todayInvoices = $stmt->fetchColumn();
            
            // تعداد فاکتورهای دیروز
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as yesterday_invoices 
                FROM invoices 
                WHERE DATE(created_at) = ?
            ");
            $stmt->execute([$yesterday]);
            $yesterdayInvoices = $stmt->fetchColumn();
            
            // محاسبه درصد تغییر فاکتورها
            $invoicesTrend = $yesterdayInvoices != 0 ? 
                            (($todayInvoices - $yesterdayInvoices) / $yesterdayInvoices) * 100 : 
                            100;
            
            // مشتریان جدید امروز
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as new_customers 
                FROM customers 
                WHERE DATE(created_at) = ?
            ");
            $stmt->execute([$today]);
            $newCustomers = $stmt->fetchColumn();
            
            // مشتریان جدید دیروز
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as yesterday_customers 
                FROM customers 
                WHERE DATE(created_at) = ?
            ");
            $stmt->execute([$yesterday]);
            $yesterdayCustomers = $stmt->fetchColumn();
            
            // محاسبه درصد تغییر مشتریان
            $customersTrend = $yesterdayCustomers != 0 ? 
                             (($newCustomers - $yesterdayCustomers) / $yesterdayCustomers) * 100 : 
                             100;
            
            // سود خالص ماه جاری
            $firstDayOfMonth = date('Y-m-01');
            $stmt = $this->db->prepare("
                SELECT 
                    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as net_profit
                FROM transactions 
                WHERE DATE(created_at) >= ?
            ");
            $stmt->execute([$firstDayOfMonth]);
            $netProfit = $stmt->fetchColumn();
            
            // سود خالص ماه قبل
            $firstDayOfLastMonth = date('Y-m-01', strtotime('-1 month'));
            $lastDayOfLastMonth = date('Y-m-t', strtotime('-1 month'));
            $stmt = $this->db->prepare("
                SELECT 
                    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as last_month_profit
                FROM transactions 
                WHERE DATE(created_at) BETWEEN ? AND ?
            ");
            $stmt->execute([$firstDayOfLastMonth, $lastDayOfLastMonth]);
            $lastMonthProfit = $stmt->fetchColumn();
            
            // محاسبه درصد تغییر سود
            $profitTrend = $lastMonthProfit != 0 ? 
                          (($netProfit - $lastMonthProfit) / $lastMonthProfit) * 100 : 
                          100;
            
            // اعلان‌های خوانده نشده
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as unread_notifications 
                FROM notifications 
                WHERE user_id = ? AND is_read = 0
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $unreadNotifications = $stmt->fetchColumn();
            
            // توزیع درآمد
            $stmt = $this->db->prepare("
                SELECT 
                    category,
                    COALESCE(SUM(amount), 0) as total_amount
                FROM transactions 
                WHERE type = 'income' 
                AND DATE(created_at) >= ?
                GROUP BY category
                ORDER BY total_amount DESC
                LIMIT 5
            ");
            $stmt->execute([$firstDayOfMonth]);
            $incomeDistribution = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $incomeDistribution[] = [
                    'label' => $row['category'],
                    'value' => $row['total_amount']
                ];
            }
            
            return [
                'today_income' => $todayIncome,
                'income_trend' => round($incomeTrend, 1),
                'today_invoices' => $todayInvoices,
                'invoices_trend' => round($invoicesTrend, 1),
                'new_customers' => $newCustomers,
                'customers_trend' => round($customersTrend, 1),
                'net_profit' => $netProfit,
                'profit_trend' => round($profitTrend, 1),
                'unread_notifications' => $unreadNotifications,
                'income_distribution' => $incomeDistribution
            ];
        } catch (Exception $e) {
            error_log("خطا در دریافت آمار داشبورد: " . $e->getMessage());
            return [];
        }
    }
    
    public function getRecentTransactions($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    CASE 
                        WHEN t.status = 'completed' THEN 'success'
                        WHEN t.status = 'pending' THEN 'warning'
                        WHEN t.status = 'failed' THEN 'danger'
                        ELSE 'secondary'
                    END as status_color
                FROM transactions t
                ORDER BY t.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            $transactions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $transactions[] = [
                    'id' => $row['id'],
                    'date' => $this->formatDate($row['created_at']),
                    'description' => $row['description'],
                    'amount' => $row['amount'],
                    'type' => $row['type'],
                    'status' => $this->translateStatus($row['status']),
                    'status_color' => $row['status_color']
                ];
            }
            
            return $transactions;
        } catch (Exception $e) {
            error_log("خطا در دریافت تراکنش‌های اخیر: " . $e->getMessage());
            return [];
        }
    }
    
    public function getRecentInvoices($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    i.*,
                    c.name as customer_name,
                    CASE 
                        WHEN i.status = 'paid' THEN 'success'
                        WHEN i.status = 'pending' THEN 'warning'
                        WHEN i.status = 'overdue' THEN 'danger'
                        ELSE 'secondary'
                    END as status_color
                FROM invoices i
                JOIN customers c ON i.customer_id = c.id
                ORDER BY i.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            $invoices = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $invoices[] = [
                    'id' => $row['id'],
                    'number' => $row['invoice_number'],
                    'customer' => $row['customer_name'],
                    'date' => $this->formatDate($row['created_at']),
                    'amount' => $row['total_amount'],
                    'status' => $this->translateStatus($row['status']),
                    'status_color' => $row['status_color']
                ];
            }
            
            return $invoices;
        } catch (Exception $e) {
            error_log("خطا در دریافت فاکتورهای اخیر: " . $e->getMessage());
            return [];
        }
    }
    
    public function getCashFlow($period = 'month') {
        try {
            $dates = $this->getDateRangeForPeriod($period);
            
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
                FROM transactions
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$dates['start'], $dates['end']]);
            
            $labels = [];
            $income = [];
            $expense = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $labels[] = $this->formatDate($row['date'], 'j F');
                $income[] = $row['income'];
                $expense[] = $row['expense'];
            }
            
            return [
                'labels' => $labels,
                'income' => $income,
                'expense' => $expense
            ];
        } catch (Exception $e) {
            error_log("خطا در دریافت نمودار جریان نقدی: " . $e->getMessage());
            return [
                'labels' => [],
                'income' => [],
                'expense' => []
            ];
        }
    }
    
    public function getTopProducts($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.*,
                    COUNT(DISTINCT s.id) as sales_count,
                    COALESCE(SUM(s.quantity * s.unit_price), 0) as revenue
                FROM products p
                LEFT JOIN sales s ON p.id = s.product_id
                GROUP BY p.id
                ORDER BY revenue DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            $products = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'image' => $row['image_url'] ?: 'assets/images/default-product.png',
                    'sales_count' => $row['sales_count'],
                    'revenue' => $row['revenue'],
                    'stock' => $row['stock_quantity'],
                    'stock_warning_level' => $row['stock_warning_level']
                ];
            }
            
            return $products;
        } catch (Exception $e) {
            error_log("خطا در دریافت محصولات پرفروش: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAccountBalances() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    a.*,
                    b.name as bank_name,
                    b.logo as bank_logo,
                    CASE 
                        WHEN a.status = 'active' THEN 'success'
                        WHEN a.status = 'inactive' THEN 'danger'
                        ELSE 'secondary'
                    END as status_color
                FROM accounts a
                JOIN banks b ON a.bank_id = b.id
                ORDER BY a.balance DESC
            ");
            $stmt->execute();
            
            $accounts = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $accounts[] = [
                    'id' => $row['id'],
                    'bank_name' => $row['bank_name'],
                    'bank_logo' => $row['bank_logo'],
                    'account_type' => $this->translateAccountType($row['account_type']),
                    'account_number' => $this->formatAccountNumber($row['account_number']),
                    'balance' => $row['balance'],
                    'status' => $this->translateStatus($row['status']),
                    'status_color' => $row['status_color']
                ];
            }
            
            return $accounts;
        } catch (Exception $e) {
            error_log("خطا در دریافت موجودی حساب‌ها: " . $e->getMessage());
            return [];
        }
    }
    
    public function getNotifications($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT *
                FROM notifications
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$_SESSION['user_id'], $limit]);
            
            $notifications = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $notifications[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'icon' => $this->getNotificationIcon($row['type']),
                    'link' => $row['link'],
                    'time' => $this->formatTimeAgo($row['created_at'])
                ];
            }
            
            return $notifications;
        } catch (Exception $e) {
            error_log("خطا در دریافت اعلان‌ها: " . $e->getMessage());
            return [];
        }
    }
    
    private function getDateRangeForPeriod($period) {
        switch ($period) {
            case 'week':
                return [
                    'start' => date('Y-m-d', strtotime('-6 days')),
                    'end' => date('Y-m-d')
                ];
            case 'month':
                return [
                    'start' => date('Y-m-01'),
                    'end' => date('Y-m-d')
                ];
            case 'year':
                return [
                    'start' => date('Y-01-01'),
                    'end' => date('Y-m-d')
                ];
            default:
                return [
                    'start' => date('Y-m-01'),
                    'end' => date('Y-m-d')
                ];
        }
    }
    
    private function formatDate($date, $format = 'Y/m/d') {
        return date($format, strtotime($date));
    }
    
    private function formatTimeAgo($datetime) {
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        
        if ($diff->y > 0) {
            return $diff->y . ' سال پیش';
        }
        if ($diff->m > 0) {
            return $diff->m . ' ماه پیش';
        }
        if ($diff->d > 0) {
            return $diff->d . ' روز پیش';
        }
        if ($diff->h > 0) {
            return $diff->h . ' ساعت پیش';
        }
        if ($diff->i > 0) {
            return $diff->i . ' دقیقه پیش';
        }
        return 'چند لحظه پیش';
    }
    
    private function translateStatus($status) {
        $translations = [
            'completed' => 'تکمیل شده',
            'pending' => 'در انتظار',
            'failed' => 'ناموفق',
            'paid' => 'پرداخت شده',
            'overdue' => 'سررسید گذشته',
            'active' => 'فعال',
            'inactive' => 'غیرفعال'
        ];
        
        return $translations[$status] ?? $status;
    }
    
    private function translateAccountType($type) {
        $translations = [
            'checking' => 'جاری',
            'savings' => 'پس‌انداز',
            'business' => 'تجاری'
        ];
        
        return $translations[$type] ?? $type;
    }
    
    private function formatAccountNumber($number) {
        // فرمت شماره حساب به صورت XXXX-XXXX-XXXX-XXXX
        return implode('-', str_split($number, 4));
    }
    
    private function getNotificationIcon($type) {
        $icons = [
            'invoice' => 'fas fa-file-invoice',
            'payment' => 'fas fa-money-bill-wave',
            'customer' => 'fas fa-user',
            'product' => 'fas fa-box',
            'stock' => 'fas fa-warehouse',
            'system' => 'fas fa-cog'
        ];
        
        return $icons[$type] ?? 'fas fa-bell';
    }
}
?>