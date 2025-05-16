// مدیریت فاکتور
const InvoiceManager = {
    // تنظیمات اولیه
    init() {
        this.initializeEventListeners();
        this.initializeFarsiNumbers();
        this.initializeMoneyFormat();
        this.initializeDateTimeFormat();
        this.initializeStatusForm();
    },

    // رویدادها
    initializeEventListeners() {
        // دکمه چاپ
        document.querySelector('.btn-print')?.addEventListener('click', () => this.printInvoice());

        // فرم تغییر وضعیت
        const statusForm = document.getElementById('statusUpdateForm');
        if (statusForm) {
            const statusSelect = statusForm.querySelector('select[name="status"]');
            statusSelect?.addEventListener('change', (e) => this.handleStatusChange(e.target.value));

            statusForm.addEventListener('submit', (e) => this.handleStatusSubmit(e));
        }
    },

    // تبدیل اعداد به فارسی
    initializeFarsiNumbers() {
        const farsiDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

        document.querySelectorAll('.farsi-number').forEach(element => {
            if (element.dataset.type === 'money') return;
            if (element.dataset.type === 'datetime') return;

            const text = element.textContent;
            element.textContent = text.replace(/\d/g, d => farsiDigits[d]);
        });
    },

    // فرمت‌بندی مبالغ
    initializeMoneyFormat() {
        document.querySelectorAll('[data-type="money"]').forEach(element => {
            const amount = parseInt(element.textContent.replace(/[^\d]/g, ''));
            if (isNaN(amount)) return;

            const formattedAmount = new Intl.NumberFormat('fa-IR').format(amount);
            element.textContent = formattedAmount;
        });
    },

    // فرمت‌بندی تاریخ و زمان
    initializeDateTimeFormat() {
        document.querySelectorAll('[data-type="datetime"]').forEach(element => {
            const timestamp = element.textContent;
            try {
                const date = new Date(timestamp);
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                const dateTimeFormat = new Intl.DateTimeFormat('fa-IR', options);
                element.textContent = dateTimeFormat.format(date);
            } catch (error) {
                console.error('Error formatting date:', error);
            }
        });
    },

    // مدیریت فرم تغییر وضعیت
    initializeStatusForm() {
        const statusSelect = document.querySelector('select[name="status"]');
        if (statusSelect) {
            this.handleStatusChange(statusSelect.value);
        }
    },

    // مدیریت تغییر وضعیت
    handleStatusChange(status) {
        const paymentFields = document.getElementById('paymentMethodFields');
        const cancellationField = document.getElementById('cancellationReasonField');

        if (paymentFields) {
            paymentFields.classList.toggle('d-none', status !== 'paid');

            const paymentInputs = paymentFields.querySelectorAll('input, select');
            paymentInputs.forEach(input => {
                input.required = status === 'paid';
            });
        }

        if (cancellationField) {
            cancellationField.classList.toggle('d-none', status !== 'cancelled');
            const reasonTextarea = cancellationField.querySelector('textarea');
            if (reasonTextarea) {
                reasonTextarea.required = status === 'cancelled';
            }
        }
    },

    // ارسال فرم تغییر وضعیت
    handleStatusSubmit(event) {
        const form = event.target;
        const status = form.querySelector('select[name="status"]').value;

        if (status === 'paid') {
            const paymentMethod = form.querySelector('select[name="payment_method"]').value;
            const paymentReference = form.querySelector('input[name="payment_reference"]').value;

            if (!paymentMethod) {
                event.preventDefault();
                alert('لطفاً روش پرداخت را انتخاب کنید');
                return;
            }

            if (!paymentReference) {
                event.preventDefault();
                alert('لطفاً شماره مرجع پرداخت را وارد کنید');
                return;
            }
        }

        if (status === 'cancelled') {
            const reason = form.querySelector('textarea[name="cancellation_reason"]').value;
            if (!reason) {
                event.preventDefault();
                alert('لطفاً دلیل لغو را وارد کنید');
                return;
            }
        }
    },

    // چاپ فاکتور
    printInvoice() {
        // قبل از چاپ
        const originalTitle = document.title;
        document.title = `فاکتور شماره ${this.getInvoiceNumber()}`;

        // چاپ
        window.print();

        // بعد از چاپ
        document.title = originalTitle;
    },

    // دریافت شماره فاکتور
    getInvoiceNumber() {
        const invoiceNumberElement = document.querySelector('.invoice-meta-item strong');
        return invoiceNumberElement ? invoiceNumberElement.textContent.trim() : '';
    }
};

// اجرای اسکریپت‌ها بعد از لود صفحه
document.addEventListener('DOMContentLoaded', () => {
    InvoiceManager.init();
});

// نمایش پیام‌های خطا
window.addEventListener('error', (event) => {
    console.error('Error:', event.error);
    alert('خطایی رخ داد. لطفاً صفحه را رفرش کنید.');
});

// مدیریت فرم‌ها
document.addEventListener('submit', (event) => {
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');

    if (submitButton) {
        // غیرفعال کردن دکمه برای جلوگیری از ارسال چندباره
        submitButton.disabled = true;

        // نمایش لودینگ
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال پردازش...';

        // فعال‌سازی مجدد دکمه بعد از ارسال
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }, 3000);
    }
});

// مدیریت المان‌های دینامیک
const DynamicUI = {
    // نمایش/مخفی کردن المان
    toggle(elementId, show) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.toggle('d-none', !show);
        }
    },

    // اضافه کردن کلاس با انیمیشن
    addClass(element, className) {
        if (element && !element.classList.contains(className)) {
            element.classList.add(className);
        }
    },

    // حذف کلاس با انیمیشن
    removeClass(element, className) {
        if (element && element.classList.contains(className)) {
            element.classList.remove(className);
        }
    }
};

// مدیریت انیمیشن‌ها
const AnimationManager = {
    // تنظیم تأخیر برای انیمیشن‌های ورودی
    init() {
        const elements = document.querySelectorAll('.animate-fade-in');
        elements.forEach((element, index) => {
            element.style.animationDelay = `${index * 0.1}s`;
        });
    }
};

// اجرای انیمیشن‌ها
document.addEventListener('DOMContentLoaded', () => {
    AnimationManager.init();
});

// مدیریت اعداد فارسی
const FarsiNumber = {
    // تبدیل عدد به فارسی
    convert(number) {
        const farsiDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return number.toString().replace(/\d/g, d => farsiDigits[d]);
    },

    // تبدیل تاریخ به فارسی
    convertDate(date) {
        try {
            return new Intl.DateTimeFormat('fa-IR').format(new Date(date));
        } catch (error) {
            console.error('Error converting date:', error);
            return date;
        }
    },

    // تبدیل مبلغ به فارسی با جداکننده
    convertMoney(amount) {
        try {
            return new Intl.NumberFormat('fa-IR').format(amount);
        } catch (error) {
            console.error('Error converting amount:', error);
            return amount;
        }
    }
};
// مدیریت فرم‌های پرداخت
const PaymentManager = {
    init() {
        this.setupPaymentMethodSelect();
        this.setupMultiPayment();
        this.setupFormValidation();
    },

    // تنظیم انتخاب روش پرداخت
    setupPaymentMethodSelect() {
        const select = document.getElementById('paymentMethodSelect');
        if (!select) return;

        select.addEventListener('change', () => {
            // مخفی کردن همه فرم‌ها
            document.querySelectorAll('.payment-form').forEach(form => {
                form.classList.add('d-none');
            });

            // نمایش فرم مربوط به روش انتخاب شده
            const selectedForm = document.getElementById(`${select.value}PaymentForm`);
            if (selectedForm) {
                selectedForm.classList.remove('d-none');
            }
        });
    },

    // تنظیم پرداخت چند روشه
    setupMultiPayment() {
        // چک‌باکس‌های روش پرداخت
        const checkboxes = {
            cash: document.getElementById('multiCashCheck'),
            card: document.getElementById('multiCardCheck'),
            cheque: document.getElementById('multiChequeCheck')
        };

        // فیلدهای مربوط به هر روش
        const fields = {
            cash: document.getElementById('multiCashFields'),
            card: document.getElementById('multiCardFields'),
            cheque: document.getElementById('multiChequeFields')
        };

        // رویداد تغییر برای هر چک‌باکس
        Object.keys(checkboxes).forEach(key => {
            const checkbox = checkboxes[key];
            if (!checkbox) return;

            checkbox.addEventListener('change', () => {
                const field = fields[key];
                if (field) {
                    field.classList.toggle('d-none', !checkbox.checked);

                    // پاک کردن مقادیر در صورت غیرفعال شدن
                    if (!checkbox.checked) {
                        field.querySelectorAll('input').forEach(input => {
                            input.value = '';
                        });
                        this.updateMultiPaymentTotal();
                    }
                }
            });
        });

        // بروزرسانی جمع کل با تغییر مبالغ
        document.querySelectorAll('.multi-amount').forEach(input => {
            input.addEventListener('input', () => this.updateMultiPaymentTotal());
        });
    },

    // بروزرسانی جمع کل پرداخت چندروشه
    updateMultiPaymentTotal() {
        const amounts = document.querySelectorAll('.multi-amount');
        let total = 0;

        amounts.forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });

        const totalElement = document.getElementById('multiPaymentTotal');
        if (totalElement) {
            totalElement.textContent = this.formatMoney(total) + ' تومان';
        }

        // بروزرسانی مانده حساب
        const remainingElement = document.getElementById('multiPaymentRemaining');
        if (remainingElement) {
            const totalDue = parseFloat(remainingElement.dataset.total) || 0;
            const remaining = totalDue - total;
            remainingElement.textContent = this.formatMoney(remaining) + ' تومان';
            remainingElement.classList.toggle('text-danger', remaining > 0);
            remainingElement.classList.toggle('text-success', remaining <= 0);
        }
    },

    // اعتبارسنجی فرم
    setupFormValidation() {
        const form = document.getElementById('statusUpdateForm');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            const method = document.getElementById('paymentMethodSelect').value;

            if (!method) {
                e.preventDefault();
                alert('لطفاً روش پرداخت را انتخاب کنید');
                return;
            }

            if (method === 'multi') {
                const totalAmount = this.calculateMultiPaymentTotal();
                const requiredAmount = parseFloat(document.getElementById('multiPaymentRemaining').dataset.total) || 0;

                if (totalAmount === 0) {
                    e.preventDefault();
                    alert('لطفاً حداقل یک مبلغ پرداختی وارد کنید');
                    return;
                }

                if (totalAmount > requiredAmount) {
                    e.preventDefault();
                    alert('مجموع مبالغ وارد شده بیشتر از مبلغ باقیمانده است');
                    return;
                }
            }
        });
    },

    // محاسبه جمع کل پرداخت چندروشه
    calculateMultiPaymentTotal() {
        const amounts = document.querySelectorAll('.multi-amount');
        let total = 0;

        amounts.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        return total;
    },

    // فرمت‌بندی مبلغ
    formatMoney(amount) {
        return new Intl.NumberFormat('fa-IR').format(amount);
    }
};

// اجرای اسکریپت‌ها بعد از لود صفحه
document.addEventListener('DOMContentLoaded', () => {
    PaymentManager.init();
});
