/**
 * تبدیل اعداد انگلیسی به فارسی و فرمت‌بندی مبالغ
 */
class PersianFormatter {
    static digits = {
        0: '۰', 1: '۱', 2: '۲', 3: '۳', 4: '۴',
        5: '۵', 6: '۶', 7: '۷', 8: '۸', 9: '۹'
    };

    static toPersianDigits(input) {
        return String(input).replace(/\d/g, d => this.digits[d]);
    }

    static formatMoney(amount) {
        return this.toPersianDigits(
            new Intl.NumberFormat('fa-IR').format(amount)
        );
    }

    static formatDate(date) {
        return this.toPersianDigits(
            new Intl.DateTimeFormat('fa-IR').format(new Date(date))
        );
    }

    static formatDateTime(datetime) {
        const date = new Date(datetime);
        const persianDate = this.toPersianDigits(
            new Intl.DateTimeFormat('fa-IR').format(date)
        );
        const time = this.toPersianDigits(
            date.toLocaleTimeString('fa-IR', {
                hour: '2-digit',
                minute: '2-digit'
            })
        );
        return `${persianDate} ${time}`;
    }
}

/**
 * مدیریت انیمیشن‌ها و افکت‌های بصری
 */
class UIAnimator {
    static initialize() {
        this.initializeAnimations();
        this.initializeTooltips();
    }

    static initializeAnimations() {
        document.querySelectorAll('.animate-fade-in').forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(10px)';

            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 100);
        });
    }

    static initializeTooltips() {
        const tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * مدیریت عملیات‌های فاکتور
 */
class InvoiceManager {
    static initialize() {
        this.initializePrintButton();
        this.initializeStatusUpdater();
        this.initializeDeleteConfirmation();
        this.convertAllNumbers();
    }

    static initializePrintButton() {
        const printButton = document.querySelector('.btn-print');
        if (printButton) {
            printButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.printInvoice();
            });
        }
    }

    static initializeStatusUpdater() {
        const statusForm = document.getElementById('statusUpdateForm');
        if (statusForm) {
            statusForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateStatus(statusForm);
            });
        }
    }

    static initializeDeleteConfirmation() {
        const deleteButton = document.querySelector('.btn-delete');
        if (deleteButton) {
            deleteButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.confirmDelete(deleteButton.dataset.id);
            });
        }
    }

    static convertAllNumbers() {
        document.querySelectorAll('.farsi-number').forEach(element => {
            if (element.dataset.type === 'money') {
                element.textContent = PersianFormatter.formatMoney(
                    element.textContent.replace(/[^\d.-]/g, '')
                );
            } else if (element.dataset.type === 'date') {
                element.textContent = PersianFormatter.formatDate(element.textContent);
            } else if (element.dataset.type === 'datetime') {
                element.textContent = PersianFormatter.formatDateTime(element.textContent);
            } else {
                element.textContent = PersianFormatter.toPersianDigits(element.textContent);
            }
        });
    }

    static printInvoice() {
        window.print();
    }

    static async updateStatus(form) {
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                location.reload();
            } else {
                throw new Error('خطا در به‌روزرسانی وضعیت');
            }
        } catch (error) {
            alert(error.message);
        }
    }

    static confirmDelete(id) {
        if (confirm('آیا از حذف این فاکتور اطمینان دارید؟')) {
            this.deleteInvoice(id);
        }
    }

    static async deleteInvoice(id) {
        try {
            const response = await fetch(`/sales/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                window.location.href = '/sales';
            } else {
                throw new Error('خطا در حذف فاکتور');
            }
        } catch (error) {
            alert(error.message);
        }
    }
}

/**
 * تنظیمات چاپ
 */
class PrintManager {
    static initialize() {
        this.setupPrintStyles();
        this.handlePrintEvents();
    }

    static setupPrintStyles() {
        const style = document.createElement('style');
        style.media = 'print';
        style.textContent = `
            @page {
                size: A4;
                margin: 1cm;
            }
            @media print {
                body * {
                    visibility: hidden;
                }
                .sales-show-container,
                .sales-show-container * {
                    visibility: visible;
                }
                .sales-show-container {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                }
                .no-print {
                    display: none !important;
                }
            }
        `;
        document.head.appendChild(style);
    }

    static handlePrintEvents() {
        window.addEventListener('beforeprint', () => {
            document.body.classList.add('printing');
        });

        window.addEventListener('afterprint', () => {
            document.body.classList.remove('printing');
        });
    }
}

/**
 * راه‌اندازی اولیه
 */
document.addEventListener('DOMContentLoaded', () => {
    UIAnimator.initialize();
    InvoiceManager.initialize();
    PrintManager.initialize();
});
