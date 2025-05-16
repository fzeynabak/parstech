// Sales List JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeDateRangePicker();
    initializeTooltips();
    initializeSelectAll();
    setupFilters();
    setupSortable();
    setupExport();
    setupDeleteConfirmation();
    setupPriceFormatting();
    setupSearch();
    setupResponsiveTable();
    initializeCustomSelect();
    setupNotifications();
    setupRefreshData();
    setupBulkActions();
    setupCharts();
    setupPrintInvoice();
});

// تنظیمات DateRangePicker
function initializeDateRangePicker() {
    const dateRangeConfig = {
        opens: 'left',
        autoUpdateInput: false,
        locale: {
            format: 'YYYY/MM/DD',
            separator: ' - ',
            applyLabel: 'اعمال',
            cancelLabel: 'انصراف',
            fromLabel: 'از',
            toLabel: 'تا',
            customRangeLabel: 'بازه دلخواه',
            weekLabel: 'هفته',
            daysOfWeek: ['ی', 'د', 'س', 'چ', 'پ', 'ج', 'ش'],
            monthNames: [
                'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
            ],
            firstDay: 6
        },
        ranges: {
            'امروز': [moment(), moment()],
            'دیروز': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '7 روز اخیر': [moment().subtract(6, 'days'), moment()],
            '30 روز اخیر': [moment().subtract(29, 'days'), moment()],
            'این ماه': [moment().startOf('month'), moment().endOf('month')],
            'ماه قبل': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    };

    // اعمال DateRangePicker به فیلتر تاریخ
    $('#dateRange').daterangepicker(dateRangeConfig, function(start, end, label) {
        // به‌روزرسانی مقدار input
        $('#dateRange').val(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
        // اعمال فیلتر به صورت خودکار
        $('#salesFilterForm').submit();
    });

    // DateRangePicker برای مودال خروجی
    $('#exportDateRange').daterangepicker(dateRangeConfig);
}

// تنظیمات Tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });
}

// انتخاب همه
function initializeSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.getElementsByClassName('sale-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            Array.from(itemCheckboxes).forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateBulkActionsVisibility();
        });
    }

    Array.from(itemCheckboxes).forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionsVisibility);
    });
}

// تنظیمات فیلترها
function setupFilters() {
    const filterForm = document.getElementById('salesFilterForm');
    const filterInputs = filterForm.querySelectorAll('select, input:not([type="checkbox"])');

    filterInputs.forEach(input => {
        input.addEventListener('change', () => {
            filterForm.submit();
        });
    });

    // دکمه پاک کردن فیلترها
    const clearFiltersBtn = document.querySelector('[href*="sales.index"]');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', (e) => {
            e.preventDefault();
            filterInputs.forEach(input => {
                input.value = '';
            });
            filterForm.submit();
        });
    }
}

// تنظیمات مرتب‌سازی
function setupSortable() {
    const sortableHeaders = document.querySelectorAll('th[data-sortable]');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const currentSort = header.getAttribute('data-sort');
            const newSort = currentSort === 'asc' ? 'desc' : 'asc';

            // پاک کردن sort قبلی
            sortableHeaders.forEach(h => h.removeAttribute('data-sort'));

            // اعمال sort جدید
            header.setAttribute('data-sort', newSort);

            // اضافه کردن پارامترهای sort به URL
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('sort_by', header.getAttribute('data-field'));
            urlParams.set('sort_order', newSort);
            window.location.search = urlParams.toString();
        });
    });
}

// تنظیمات خروجی گرفتن
function setupExport() {
    const exportForm = document.getElementById('exportForm');
    if (exportForm) {
        exportForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(exportForm);
            try {
                const response = await fetch(exportForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `sales-export-${moment().format('YYYY-MM-DD-HH-mm')}.${formData.get('format')}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    // بستن مودال
                    bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();

                    // نمایش پیام موفقیت
                    showNotification('خروجی با موفقیت دانلود شد.', 'success');
                } else {
                    throw new Error('خطا در دانلود فایل');
                }
            } catch (error) {
                showNotification('خطا در دانلود فایل. لطفاً مجدداً تلاش کنید.', 'error');
            }
        });
    }
}

// تنظیمات تایید حذف
function setupDeleteConfirmation() {
    window.deleteSale = function(saleId) {
        Swal.fire({
            title: 'آیا مطمئن هستید؟',
            text: "این عملیات قابل بازگشت نیست!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'انصراف'
        }).then((result) => {
            if (result.isConfirmed) {
                // ارسال درخواست حذف
                fetch(`/sales/${saleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // حذف ردیف از جدول
                        document.querySelector(`tr[data-sale-id="${saleId}"]`).remove();
                        showNotification('فاکتور با موفقیت حذف شد.', 'success');
                    } else {
                        throw new Error(data.message || 'خطا در حذف فاکتور');
                    }
                })
                .catch(error => {
                    showNotification(error.message, 'error');
                });
            }
        });
    };
}

// فرمت‌بندی قیمت‌ها
function setupPriceFormatting() {
    function formatPrice(price) {
        return new Intl.NumberFormat('fa-IR').format(price);
    }

    // فرمت‌بندی همه قیمت‌های جدول
    document.querySelectorAll('[data-price]').forEach(element => {
        const price = element.getAttribute('data-price');
        element.textContent = formatPrice(price) + ' تومان';
    });
}

// تنظیمات جستجو
function setupSearch() {
    const searchInput = document.getElementById('search');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchForm = searchInput.closest('form');
                if (searchForm) {
                    searchForm.submit();
                }
            }, 500);
        });
    }
}

// تنظیمات جدول ریسپانسیو
function setupResponsiveTable() {
    function adjustTable() {
        const table = document.querySelector('.sales-table-container table');
        if (table) {
            const container = table.parentElement;
            const isScrollable = table.offsetWidth > container.offsetWidth;

            if (isScrollable && !container.querySelector('.scroll-indicator')) {
                const indicator = document.createElement('div');
                indicator.className = 'scroll-indicator';
                indicator.innerHTML = '<i class="fas fa-arrows-left-right"></i>';
                container.appendChild(indicator);

                // حذف indicator بعد از 3 ثانیه
                setTimeout(() => {
                    indicator.remove();
                }, 3000);
            }
        }
    }

    // تنظیم جدول در لود صفحه و تغییر سایز پنجره
    adjustTable();
    window.addEventListener('resize', adjustTable);
}

// تنظیمات Select2
function initializeCustomSelect() {
    // اگر Select2 لود شده باشد
    if (typeof $.fn.select2 !== 'undefined') {
        $('.form-select').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dir: 'rtl',
            language: {
                noResults: function() {
                    return "نتیجه‌ای یافت نشد";
                }
            }
        });
    }
}

// تنظیمات نوتیفیکیشن
function setupNotifications() {
    window.showNotification = function(message, type = 'info') {
        const icons = {
            success: 'check-circle',
            error: 'times-circle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${icons[type]} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(container);
        }

        document.querySelector('.toast-container').appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, {
            animation: true,
            autohide: true,
            delay: 5000
        });
        bsToast.show();

        // حذف toast بعد از بسته شدن
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    };
}

// به‌روزرسانی خودکار داده‌ها
function setupRefreshData() {
    let refreshInterval;
    const REFRESH_INTERVAL = 300000; // 5 دقیقه

    function startAutoRefresh() {
        refreshInterval = setInterval(refreshData, REFRESH_INTERVAL);
    }

    function stopAutoRefresh() {
        clearInterval(refreshInterval);
    }

    async function refreshData() {
        try {
            const response = await fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');

                // به‌روزرسانی جدول
                const newTable = doc.querySelector('.sales-table-container');
                const currentTable = document.querySelector('.sales-table-container');
                if (newTable && currentTable) {
                    currentTable.innerHTML = newTable.innerHTML;
                }

                // به‌روزرسانی آمار
                const newStats = doc.querySelector('.sales-stats');
                const currentStats = document.querySelector('.sales-stats');
                if (newStats && currentStats) {
                    currentStats.innerHTML = newStats.innerHTML;
                }

                // راه‌اندازی مجدد توابع
                initializeTooltips();
                setupPriceFormatting();
            }
        } catch (error) {
            console.error('خطا در به‌روزرسانی داده‌ها:', error);
        }
    }

    // شروع به‌روزرسانی خودکار
    startAutoRefresh();

    // توقف به‌روزرسانی در تب غیرفعال
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
            refreshData(); // به‌روزرسانی فوری در بازگشت به تب
        }
    });
}

// تنظیمات عملیات گروهی
function setupBulkActions() {
    let selectedItems = [];

    function updateBulkActionsVisibility() {
        const bulkActionsContainer = document.querySelector('.bulk-actions');
        const checkedItems = document.querySelectorAll('.sale-checkbox:checked');
        selectedItems = Array.from(checkedItems).map(item => item.value);

        if (bulkActionsContainer) {
            if (selectedItems.length > 0) {
                bulkActionsContainer.classList.remove('d-none');
                document.querySelector('.selected-count').textContent = selectedItems.length;
            } else {
                bulkActionsContainer.classList.add('d-none');
            }
        }
    }

    // عملیات حذف گروهی
    window.bulkDelete = function() {
        if (selectedItems.length === 0) return;

        Swal.fire({
            title: 'حذف گروهی',
            text: `آیا از حذف ${selectedItems.length} فاکتور انتخاب شده اطمینان دارید؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'انصراف'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/sales/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ ids: selectedItems })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        selectedItems.forEach(id => {
                            document.querySelector(`tr[data-sale-id="${id}"]`).remove();
                        });
                        showNotification(`${selectedItems.length} فاکتور با موفقیت حذف شد.`, 'success');
                        selectedItems = [];
                        updateBulkActionsVisibility();
                    } else {
                        throw new Error(data.message || 'خطا در حذف فاکتورها');
                    }
                })
                .catch(error => {
                    showNotification(error.message, 'error');
                });
            }
        });
    };

    // عملیات خروجی گروهی
    window.bulkExport = function() {
        if (selectedItems.length === 0) return;

        const modal = document.getElementById('exportModal');
        if (modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            // اضافه کردن آیدی‌های انتخاب شده به فرم
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_ids';
            input.value = selectedItems.join(',');
            document.getElementById('exportForm').appendChild(input);
        }
    };
}

// تنظیمات نمودارها
function setupCharts() {
    // اگر Chart.js لود شده باشد
    if (typeof Chart !== 'undefined') {
        // نمودار فروش روزانه
        const dailySalesCtx = document.getElementById('dailySalesChart');
        if (dailySalesCtx) {
            new Chart(dailySalesCtx, {
                type: 'line',
                data: {
                    labels: dailySalesData.labels,
                    datasets: [{
                        label: 'فروش روزانه',
                        data: dailySalesData.values,
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('fa-IR').format(value) + ' تومان';
                                }
                            }
                        }
                    }
                }
            });
        }

        // نمودار توزیع فروش
        const salesDistributionCtx = document.getElementById('salesDistributionChart');
        if (salesDistributionCtx) {
            new Chart(salesDistributionCtx, {
                type: 'doughnut',
                data: {
                    labels: salesDistributionData.labels,
                    datasets: [{
                        data: salesDistributionData.values,
                        backgroundColor: [
                            '#4361ee',
                            '#3f37c9',
                            '#3a0ca3',
                            '#480ca8',
                            '#560bad',
                            '#7209b7'
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
            });
        }
    }
}

// تنظیمات چاپ فاکتور
function setupPrintInvoice() {
    window.printInvoice = function(saleId) {
        // باز کردن صفحه چاپ در پنجره جدید
        const printWindow = window.open(`/sales/${saleId}/print`, '_blank', 'width=800,height=600');

        // اضافه کردن event listener برای چاپ خودکار
        printWindow.addEventListener('load', () => {
            printWindow.print();
            // بستن پنجره بعد از چاپ
            printWindow.addEventListener('afterprint', () => {
                printWindow.close();
            });
        });
    };
}

// تغییر تعداد آیتم در صفحه
function changePerPage(select) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('per_page', select.value);
    window.location.search = urlParams.toString();
}
