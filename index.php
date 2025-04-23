<?php
session_start();
require_once 'config/config.php';
require_once 'includes/classes/Auth.php';
require_once 'includes/classes/Dashboard.php';
require_once 'includes/classes/ChartGenerator.php';

// بررسی لاگین بودن کاربر
$auth = new Auth(new Database());
$auth->requireLogin();

// دریافت اطلاعات داشبورد
$dashboard = new Dashboard(new Database());
$stats = $dashboard->getStats();
$recentTransactions = $dashboard->getRecentTransactions();
$recentInvoices = $dashboard->getRecentInvoices();
$cashFlow = $dashboard->getCashFlow();
$topProducts = $dashboard->getTopProducts();
$accountBalances = $dashboard->getAccountBalances();

$pageTitle = 'داشبورد';
require_once 'includes/header.php';
?>

<!-- شروع محتوای اصلی -->
<div class="content-wrapper">
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="content">
        <!-- نوار بالایی -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white mb-4">
            <div class="container-fluid">
                <button class="btn btn-link sidebar-toggler" type="button">
                    <i class="fas fa-bars"></i>
                </button>
                
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" 
                           data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <?php if ($stats['unread_notifications'] > 0): ?>
                                <span class="badge bg-danger"><?php echo $stats['unread_notifications']; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <h6 class="dropdown-header">اعلان‌ها</h6>
                            <?php foreach ($dashboard->getNotifications() as $notification): ?>
                                <a class="dropdown-item" href="<?php echo $notification['link']; ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="notification-icon">
                                            <i class="<?php echo $notification['icon']; ?>"></i>
                                        </div>
                                        <div class="ms-3">
                                            <div class="notification-title"><?php echo $notification['title']; ?></div>
                                            <div class="notification-desc small text-muted">
                                                <?php echo $notification['description']; ?>
                                            </div>
                                            <div class="notification-time small text-muted">
                                                <?php echo $notification['time']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            <a class="dropdown-item text-center small text-muted" href="notifications.php">
                                نمایش همه اعلان‌ها
                            </a>
                        </div>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown">
                            <img src="<?php echo $_SESSION['user_avatar'] ?? 'assets/images/default-avatar.png'; ?>" 
                                 class="rounded-circle" width="32" height="32" alt="تصویر کاربر">
                            <span class="ms-2"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <h6 class="dropdown-header">سلام، <?php echo $_SESSION['first_name']; ?></h6>
                            <a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i>
                                پروفایل من
                            </a>
                            <a class="dropdown-item" href="settings.php">
                                <i class="fas fa-cog me-2"></i>
                                تنظیمات
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="#" id="logoutBtn">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                خروج
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- کارت‌های آمار -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">درآمد امروز</h6>
                                <h4 class="card-title mb-0">
                                    <?php echo number_format($stats['today_income']); ?> 
                                    <small>تومان</small>
                                </h4>
                                <div class="trend <?php echo $stats['income_trend'] >= 0 ? 'up' : 'down'; ?>">
                                    <i class="fas fa-arrow-<?php echo $stats['income_trend'] >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($stats['income_trend']); ?>%
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">فاکتورهای امروز</h6>
                                <h4 class="card-title mb-0"><?php echo $stats['today_invoices']; ?></h4>
                                <div class="trend <?php echo $stats['invoices_trend'] >= 0 ? 'up' : 'down'; ?>">
                                    <i class="fas fa-arrow-<?php echo $stats['invoices_trend'] >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($stats['invoices_trend']); ?>%
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">مشتریان جدید</h6>
                                <h4 class="card-title mb-0"><?php echo $stats['new_customers']; ?></h4>
                                <div class="trend <?php echo $stats['customers_trend'] >= 0 ? 'up' : 'down'; ?>">
                                    <i class="fas fa-arrow-<?php echo $stats['customers_trend'] >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($stats['customers_trend']); ?>%
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">سود خالص</h6>
                                <h4 class="card-title mb-0">
                                    <?php echo number_format($stats['net_profit']); ?>
                                    <small>تومان</small>
                                </h4>
                                <div class="trend <?php echo $stats['profit_trend'] >= 0 ? 'up' : 'down'; ?>">
                                    <i class="fas fa-arrow-<?php echo $stats['profit_trend'] >= 0 ? 'up' : 'down'; ?>"></i>
                                    <?php echo abs($stats['profit_trend']); ?>%
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- نمودارها -->
        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">نمودار درآمد و هزینه</h5>
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" type="button" id="chartPeriodDropdown" 
                                    data-bs-toggle="dropdown">
                                ماه جاری
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#" data-period="week">هفته جاری</a>
                                <a class="dropdown-item active" href="#" data-period="month">ماه جاری</a>
                                <a class="dropdown-item" href="#" data-period="year">سال جاری</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">وضعیت درآمد</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="incomeDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- تراکنش‌های اخیر و فاکتورها -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">تراکنش‌های اخیر</h5>
                        <a href="transactions.php" class="btn btn-link">نمایش همه</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>تاریخ</th>
                                    <th>شرح</th>
                                    <th>مبلغ</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $transaction): ?>
                                <tr>
                                    <td><?php echo $transaction['date']; ?></td>
                                    <td><?php echo $transaction['description']; ?></td>
                                    <td class="<?php echo $transaction['amount'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo number_format(abs($transaction['amount'])); ?> تومان
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $transaction['status_color']; ?>">
                                            <?php echo $transaction['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">فاکتورهای اخیر</h5>
                        <a href="invoices.php" class="btn btn-link">نمایش همه</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>شماره</th>
                                    <th>مشتری</th>
                                    <th>تاریخ</th>
                                    <th>مبلغ</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInvoices as $invoice): ?>
                                <tr>
                                    <td>
                                        <a href="invoice.php?id=<?php echo $invoice['id']; ?>">
                                            <?php echo $invoice['number']; ?>
                                        </a>
                                    </td>
                                    <td><?php echo $invoice['customer']; ?></td>
                                    <td><?php echo $invoice['date']; ?></td>
                                    <td><?php echo number_format($invoice['amount']); ?> تومان</td>
                                    <td>
                                        <span class="badge bg-<?php echo $invoice['status_color']; ?>">
                                            <?php echo $invoice['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- محصولات پرفروش و موجودی حساب‌ها -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">محصولات پرفروش</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>محصول</th>
                                    <th>تعداد فروش</th>
                                    <th>درآمد</th>
                                    <th>موجودی</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $product['image']; ?>" 
                                                 alt="<?php echo $product['name']; ?>" 
                                                 class="product-image me-2">
                                            <?php echo $product['name']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($product['sales_count']); ?></td>
                                    <td><?php echo number_format($product['revenue']); ?> تومان</td>
                                    <td>
                                        <?php if ($product['stock'] <= $product['stock_warning_level']): ?>
                                            <span class="text-danger">
                                                <?php echo $product['stock']; ?>
                                                <i class="fas fa-exclamation-triangle ms-1" 
                                                   data-bs-toggle="tooltip" 
                                                   title="موجودی کم"></i>
                                            </span>
                                        <?php else: ?>
                                            <?php echo $product['stock']; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">موجودی حساب‌ها</h5>
                        <a href="accounts.php" class="btn btn-link">مدیریت حساب‌ها</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>حساب</th>
                                    <th>شماره حساب</th>
                                    <th>موجودی</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accountBalances as $account): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $account['bank_logo']; ?>" 
                                                 alt="<?php echo $account['bank_name']; ?>" 
                                                 class="bank-logo me-2">
                                            <div>
                                                <?php echo $account['bank_name']; ?>
                                                <div class="small text-muted">
                                                    <?php echo $account['account_type']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $account['account_number']; ?></td>
                                    <td><?php echo number_format($account['balance']); ?> تومان</td>
                                    <td>
                                        <span class="badge bg-<?php echo $account['status_color']; ?>">
                                            <?php echo $account['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال خروج -->
<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">خروج از سیستم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                آیا مطمئن هستید که می‌خواهید از سیستم خارج شوید؟
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <a href="logout.php" class="btn btn-danger">خروج</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // تنظیم نمودار درآمد و هزینه
    const incomeExpenseChart = new Chart(
        document.getElementById('incomeExpenseChart').getContext('2d'),
        {
            type: 'line',
            data: {
                labels: <?php echo json_encode($cashFlow['labels']); ?>,
                datasets: [
                    {
                        label: 'درآمد',
                        data: <?php echo json_encode($cashFlow['income']); ?>,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'هزینه',
                        data: <?php echo json_encode($cashFlow['expense']); ?>,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return number_format(value) + ' تومان';
                            }
                        }
                    }
                }
            }
        }
    );

    // تنظیم نمودار توزیع درآمد
    const incomeDistributionChart = new Chart(
        document.getElementById('incomeDistributionChart').getContext('2d'),
        {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($stats['income_distribution'], 'label')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($stats['income_distribution'], 'value')); ?>,
                    backgroundColor: [
                        '#28a745',
                        '#17a2b8',
                        '#ffc107',
                        '#dc3545',
                        '#6c757d'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        }
    );

    // تغییر دوره زمانی نمودار
    $('.dropdown-item[data-period]').click(function(e) {
        e.preventDefault();
        const period = $(this).data('period');
        
        // بروزرسانی نمودار با AJAX
        $.get('api/chart-data.php', { period: period }, function(data) {
            incomeExpenseChart.data.labels = data.labels;
            incomeExpenseChart.data.datasets[0].data = data.income;
            incomeExpenseChart.data.datasets[1].data = data.expense;
            incomeExpenseChart.update();
            
            // بروزرسانی متن دکمه
            $('#chartPeriodDropdown').text($(e.target).text());
            
            // تغییر کلاس active
            $('.dropdown-item[data-period]').removeClass('active');
            $(e.target).addClass('active');
        });
    });

    // نمایش مودال خروج
    $('#logoutBtn').click(function(e) {
        e.preventDefault();
        $('#logoutModal').modal('show');
    });

    // فعال‌سازی تولتیپ‌ها
    $('[data-bs-toggle="tooltip"]').tooltip();

    // تابع فرمت‌کردن اعداد
    function number_format(number) {
        return new Intl.NumberFormat('fa-IR').format(number);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>