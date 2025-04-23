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
                'today_invoices'