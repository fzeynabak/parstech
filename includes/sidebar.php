<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get current page for active menu highlighting
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <h3>پارس تک</h3>
        <div class="user-info">
            <img src="assets/images/avatar.png" class="user-avatar" alt="تصویر کاربر">
            <div class="user-details">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <span class="user-role">مدیر سیستم</span>
            </div>
        </div>
    </div>

    <ul class="list-unstyled components">
        <!-- داشبورد -->
        <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
            <a href="index.php?page=dashboard">
                <i class="fas fa-home"></i>
                <span>داشبورد</span>
            </a>
        </li>

        <!-- اشخاص -->
        <li class="sidebar-dropdown <?php echo (strpos($current_page, 'persons/') !== false) ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-users"></i>
                <span>اشخاص</span>
                <i class="fas fa-chevron-left submenu-icon"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li class="<?php echo ($current_page == 'persons/new') ? 'active' : ''; ?>">
                        <a href="index.php?page=persons/new">شخص جدید</a>
                    </li>
                    <li class="<?php echo ($current_page == 'persons/list') ? 'active' : ''; ?>">
                        <a href="index.php?page=persons/list">اشخاص</a>
                    </li>
                    <li class="<?php echo ($current_page == 'persons/receive') ? 'active' : ''; ?>">
                        <a href="index.php?page=persons/receive">دریافت</a>
                    </li>
                    <li class="<?php echo ($current_page == 'persons/receive-list') ? 'active' : ''; ?>">
                        <a href="index.php?page=persons/receive-list">لیست دریافت‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'persons/payment') ? 'active' : ''; ?>">
                        <a href="index.php?page=persons/payment">پرداخت</a>
                    </li>
                    <li class="<?php echo ($current_page == 'persons/payment-list') ? 'active' : ''; ?>">
                        <a href="index.php?page=persons/payment-list">لیست پرداخت‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'persons/shareholders') ? 'active' : ''; ?>">
                        <a href="index.php?page=persons/shareholders">سهامداران</a>
                    </li>
                    <li class="<?php echo ($current_page == 'persons/vendors') ? 'active' : ''; ?>">
                        <a href="index.php?page=persons/vendors">فروشندگان</a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- کالاها و خدمات -->
        <li class="sidebar-dropdown <?php echo (strpos($current_page, 'products/') !== false) ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-box"></i>
                <span>کالاها و خدمات</span>
                <i class="fas fa-chevron-left submenu-icon"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li class="<?php echo ($current_page == 'products/new') ? 'active' : ''; ?>">
                        <a href="index.php?page=products/new">کالای جدید</a>
                    </li>
                    <li class="<?php echo ($current_page == 'products/service-new') ? 'active' : ''; ?>">
                        <a href="index.php?page=products/service-new">خدمات جدید</a>
                    </li>
                    <li class="<?php echo ($current_page == 'products/list') ? 'active' : ''; ?>">
                        <a href="index.php?page=products/list">کالاها و خدمات</a>
                    </li>
                    <li class="<?php echo ($current_page == 'products/price-update') ? 'active' : ''; ?>">
                        <a href="index.php?page=products/price-update">به‌روزرسانی لیست قیمت</a>
                    </li>
                    <li class="<?php echo ($current_page == 'products/barcode') ? 'active' : ''; ?>">
                        <a href="index.php?page=products/barcode">چاپ بارکد</a>
                    </li>
                    <li class="<?php echo ($current_page == 'products/barcode-bulk') ? 'active' : ''; ?>">
                        <a href="index.php?page=products/barcode-bulk">چاپ بارکد تعدادی</a>
                    </li>
                    <li class="<?php echo ($current_page == 'products/price-list') ? 'active' : ''; ?>">
                        <a href="index.php?page=products/price-list">صفحه لیست قیمت کالا</a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- بانکداری -->
        <li class="sidebar-dropdown <?php echo (strpos($current_page, 'banking/') !== false) ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-university"></i>
                <span>بانکداری</span>
                <i class="fas fa-chevron-left submenu-icon"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li class="<?php echo ($current_page == 'banking/banks') ? 'active' : ''; ?>">
                        <a href="index.php?page=banking/banks">بانک‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'banking/funds') ? 'active' : ''; ?>">
                        <a href="index.php?page=banking/funds">صندوق‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'banking/petty-cash') ? 'active' : ''; ?>">
                        <a href="index.php?page=banking/petty-cash">تنخواه‌گردان‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'banking/transfer') ? 'active' : ''; ?>">
                        <a href="index.php?page=banking/transfer">انتقال</a>
                    </li>
                    <li class="<?php echo ($current_page == 'banking/transfer-list') ? 'active' : ''; ?>">
                        <a href="index.php?page=banking/transfer-list">لیست انتقال‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'banking/checks-received') ? 'active' : ''; ?>">
                        <a href="index.php?page=banking/checks-received">لیست چک‌های دریافتی</a>
                    </li>
                    <li class="<?php echo ($current_page == 'banking/checks-paid') ? 'active' : ''; ?>">
                        <a href="index.php?page=banking/checks-paid">لیست چک‌های پرداختی</a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- فروش و درآمد -->
        <li class="sidebar-dropdown <?php echo (strpos($current_page, 'sales/') !== false) ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-shopping-cart"></i>
                <span>فروش و درآمد</span>
                <i class="fas fa-chevron-left submenu-icon"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li class="<?php echo ($current_page == 'sales/new') ? 'active' : ''; ?>">
                        <a href="index.php?page=sales/new">فروش جدید</a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales/quick') ? 'active' : ''; ?>">
                        <a href="index.php?page=sales/quick">فاکتور سریع</a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales/return') ? 'active' : ''; ?>">
                        <a href="index.php?page=sales/return">برگشت از فروش</a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales/invoices') ? 'active' : ''; ?>">
                        <a href="index.php?page=sales/invoices">فاکتورهای فروش</a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales/return-invoices') ? 'active' : ''; ?>">
                        <a href="index.php?page=sales/return-invoices">فاکتورهای برگشت از فروش</a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales/income') ? 'active' : ''; ?>">
                        <a href="index.php?page=sales/income">درآمد</a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales/income-list') ? 'active' : ''; ?>">
                        <a href="index.php?page=sales/income-list">لیست درآمدها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales/installment') ? 'active' : ''; ?>">
                        <a href="index.php?page=sales/installment">قرارداد فروش اقساطی</a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales/installment-list') ? 'active' : ''; ?>">
                        <a href="index.php?page=sales/installment-list">لیست فروش اقساطی</a>
                    </li>
                    <li class="<?php echo ($current_page == 'sales/discounted') ? 'active' : ''; ?>">
                        <a href="index.php?page=sales/discounted">اقلام تخفیف‌دار</a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- خرید و هزینه -->
        <li class="sidebar-dropdown <?php echo (strpos($current_page, 'purchases/') !== false) ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-shopping-basket"></i>
                <span>خرید و هزینه</span>
                <i class="fas fa-chevron-left submenu-icon"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li class="<?php echo ($current_page == 'purchases/new') ? 'active' : ''; ?>">
                        <a href="index.php?page=purchases/new">خرید جدید</a>
                    </li>
                    <li class="<?php echo ($current_page == 'purchases/return') ? 'active' : ''; ?>">
                        <a href="index.php?page=purchases/return">برگشت از خرید</a>
                    </li>
                    <li class="<?php echo ($current_page == 'purchases/invoices') ? 'active' : ''; ?>">
                        <a href="index.php?page=purchases/invoices">فاکتورهای خرید</a>
                    </li>
                    <li class="<?php echo ($current_page == 'purchases/return-invoices') ? 'active' : ''; ?>">
                        <a href="index.php?page=purchases/return-invoices">فاکتورهای برگشت از خرید</a>
                    </li>
                    <li class="<?php echo ($current_page == 'purchases/expenses') ? 'active' : ''; ?>">
                        <a href="index.php?page=purchases/expenses">هزینه</a>
                    </li>
                    <li class="<?php echo ($current_page == 'purchases/expenses-list') ? 'active' : ''; ?>">
                        <a href="index.php?page=purchases/expenses-list">لیست هزینه‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'purchases/waste') ? 'active' : ''; ?>">
                        <a href="index.php?page=purchases/waste">ضایعات</a>
                    </li>
                    <li class="<?php echo ($current_page == 'purchases/waste-list') ? 'active' : ''; ?>">
                        <a href="index.php?page=purchases/waste-list">لیست ضایعات</a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- انبارداری -->
        <li class="sidebar-dropdown <?php echo (strpos($current_page, 'inventory/') !== false) ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-warehouse"></i>
                <span>انبارداری</span>
                <i class="fas fa-chevron-left submenu-icon"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li class="<?php echo ($current_page == 'inventory/warehouses') ? 'active' : ''; ?>">
                        <a href="index.php?page=inventory/warehouses">انبارها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'inventory/new-transfer') ? 'active' : ''; ?>">
                        <a href="index.php?page=inventory/new-transfer">حواله جدید</a>
                    </li>
                    <li class="<?php echo ($current_page == 'inventory/transfers') ? 'active' : ''; ?>">
                        <a href="index.php?page=inventory/transfers">رسید و حواله‌های انبار</a>
                    </li>
                    <li class="<?php echo ($current_page == 'inventory/stock') ? 'active' : ''; ?>">
                        <a href="index.php?page=inventory/stock">موجودی کالا</a>
                    </li>
                    <li class="<?php echo ($current_page == 'inventory/all-stock') ? 'active' : ''; ?>">
                        <a href="index.php?page=inventory/all-stock">موجودی تمامی انبارها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'inventory/stocktaking') ? 'active' : ''; ?>">
                        <a href="index.php?page=inventory/stocktaking">انبارگردانی</a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- حسابداری -->
        <li class="sidebar-dropdown <?php echo (strpos($current_page, 'accounting/') !== false) ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-calculator"></i>
                <span>حسابداری</span>
                <i class="fas fa-chevron-left submenu-icon"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li class="<?php echo ($current_page == 'accounting/new-document') ? 'active' : ''; ?>">
                        <a href="index.php?page=accounting/new-document">سند جدید</a>
                    </li>
                    <li class="<?php echo ($current_page == 'accounting/documents') ? 'active' : ''; ?>">
                        <a href="index.php?page=accounting/documents">لیست اسناد</a>
                    </li>
                    <li class="<?php echo ($current_page == 'accounting/opening-balance') ? 'active' : ''; ?>">
                        <a href="index.php?page=accounting/opening-balance">تراز افتتاحیه</a>
                    </li>
                    <li class="<?php echo ($current_page == 'accounting/close-fiscal-year') ? 'active' : ''; ?>">
                        <a href="index.php?page=accounting/close-fiscal-year">بستن سال مالی</a>
                    </li>
                    <li class="<?php echo ($current_page == 'accounting/chart-accounts') ? 'active' : ''; ?>">
                        <a href="index.php?page=accounting/chart-accounts">جدول حساب‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'accounting/merge-documents') ? 'active' : ''; ?>">
                        <a href="index.php?page=accounting/merge-documents">تجمیع اسناد</a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- سایر -->
        <li class="sidebar-dropdown <?php echo (strpos($current_page, 'other/') !== false) ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-ellipsis-h"></i>
                <span>سایر</span>
                <i class="fas fa-chevron-left submenu-icon"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li class="<?php echo ($current_page == 'other/archive') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/archive">آرشیو</a>
                    </li>
                    <li class="<?php echo ($current_page == 'other/sms') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/sms">پنل پیامک</a>
                    </li>
                    <li class="<?php echo ($current_page == 'other/inquiry') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/inquiry">استعلام</a>
                    </li>
                    <li class="<?php echo ($current_page == 'other/other-receive') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/other-receive">دریافت سایر</a>
                    </li>
                    <li class="<?php echo ($current_page == 'other/receive-list') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/receive-list">لیست دریافت‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'other/other-payment') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/other-payment">پرداخت سایر</a>
                    </li>
                    <li class="<?php echo ($current_page == 'other/payment-list') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/payment-list">لیست پرداخت‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'other/currency-exchange') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/currency-exchange">سند تسعیر ارز</a>
                    </li>
                    <li class="<?php echo ($current_page == 'other/persons-balance') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/persons-balance">سند توازن اشخاص</a>
                    </li>
                    <li class="<?php echo ($current_page == 'other/products-balance') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/products-balance">سند توازن کالاها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'other/salary') ? 'active' : ''; ?>">
                        <a href="index.php?page=other/salary">سند حقوق</a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- گزارش‌ها -->
        <li class="sidebar-dropdown <?php echo (strpos($current_page, 'reports/') !== false) ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-chart-bar"></i>
                <span>گزارش‌ها</span>
                <i class="fas fa-chevron-left submenu-icon"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li class="<?php echo ($current_page == 'reports/all') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/all">تمام گزارش‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/balance-sheet') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/balance-sheet">ترازنامه</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/profit-loss') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/profit-loss">صورت سود و زیان</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/capital') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/capital">صورتحساب سرمایه</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/journal') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/journal">دفتر روزنامه</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/ledger') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/ledger">دفتر کل</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/trial-balance') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/trial-balance">تراز آزمایشی</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/accounts-review') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/accounts-review">مرور حساب‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/detail-accounts') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/detail-accounts">مرور حساب‌های تفصیل</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/journal-merge') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/journal-merge">دفتر روزنامه (تجمیعی)</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/ledger-merge') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/ledger-merge">دفتر کل (تجمیعی)</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/debtors-creditors') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/debtors-creditors">بدهکاران و بستانکاران</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/persons-card') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/persons-card">کارت حساب اشخاص</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/sales-by-product') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/sales-by-product">فروش به تفکیک کالا</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/purchases-by-product') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/purchases-by-product">خرید به تفکیک کالا</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/sales-by-invoice') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/sales-by-invoice">فروش به تفکیک فاکتور</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/purchases-by-invoice') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/purchases-by-invoice">خرید به تفکیک فاکتور</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/tax') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/tax">گزارش مالیات</a>
                    </li>
                    <li class="<?php echo ($current_page == 'reports/invoice-profit') ? 'active' : ''; ?>">
                        <a href="index.php?page=reports/invoice-profit">سود فاکتور</a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- تنظیمات -->
        <li class="sidebar-dropdown <?php echo (strpos($current_page, 'settings/') !== false) ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-cog"></i>
                <span>تنظیمات</span>
                <i class="fas fa-chevron-left submenu-icon"></i>
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li class="<?php echo ($current_page == 'settings/projects') ? 'active' : ''; ?>">
                        <a href="index.php?page=settings/projects">پروژه‌ها</a>
                    </li>
                    <li class="<?php echo ($current_page == 'settings/business') ? 'active' : ''; ?>">
                        <a href="index.php?page=settings/business">اطلاعات کسب و کار</a>
                    </li>
                    <li class="<?php echo ($current_page == 'settings/financial') ? 'active' : ''; ?>">
                        <a href="index.php?page=settings/financial">تنظیمات مالی</a>
                    </li>
                    <li class="<?php echo ($current_page == 'settings/exchange-rates') ? 'active' : ''; ?>">
                        <a href="index.php?page=settings/exchange-rates">جدول تبدیل نرخ ارز</a>
                    </li>
                    <li class="<?php echo ($current_page == 'settings/users') ? 'active' : ''; ?>">
                        <a href="index.php?page=settings/users">مدیریت کاربران</a>
                    </li>
                    <li class="<?php echo ($current_page == 'settings/print') ? 'active' : ''; ?>">
                        <a href="index.php?page=settings/print">تنظیمات چاپ</a>
                    </li>
                    <li class="<?php echo ($current_page == 'settings/form-builder') ? 'active' : ''; ?>">
                        <a href="index.php?page=settings/form-builder">فرم ساز</a>
                    </li>
                    <li class="<?php echo ($current_page == 'settings/notifications') ? 'active' : ''; ?>">
                        <a href="index.php?page=settings/notifications">اعلانات</a>
                    </li>
                </ul>
            </div>
        </li>

    </ul>

    <div class="sidebar-footer">
        <div class="version">نسخه ۱.۰.۰</div>
        <div class="copyright">تمامی حقوق محفوظ است &copy; <?php echo date('Y'); ?></div>
    </div>
</nav>