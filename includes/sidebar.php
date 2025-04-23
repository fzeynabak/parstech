<nav class="sidebar">
    <div class="sidebar-header">
        <h3>پارس تک</h3>
    </div>

    <ul class="list-unstyled components">
        <li class="<?php echo ($page == 'dashboard') ? 'active' : ''; ?>">
            <a href="index.php?page=dashboard">
                <i class="fas fa-home"></i> داشبورد
            </a>
        </li>

        <li class="sidebar-dropdown">
            <a href="#">
                <i class="fas fa-users"></i> اشخاص
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li><a href="index.php?page=persons/new">شخص جدید</a></li>
                    <li><a href="index.php?page=persons/list">لیست اشخاص</a></li>
                    <li><a href="index.php?page=persons/payments">دریافت و پرداخت</a></li>
                </ul>
            </div>
        </li>

        <li class="sidebar-dropdown">
            <a href="#">
                <i class="fas fa-box"></i> کالاها و خدمات
            </a>
            <div class="sidebar-submenu">
                <ul>
                    <li><a href="index.php?page=products/new">کالای جدید</a></li>
                    <li><a href="index.php?page=products/services">خدمات جدید</a></li>
                    <li><a href="index.php?page=products/list">لیست کالاها و خدمات</a></li>
                    <li><a href="index.php?page=products/prices">به‌روزرسانی قیمت</a></li>
                </ul>
            </div>
        </li>

        <!-- Add other menu items following the same pattern -->
    </ul>
</nav>