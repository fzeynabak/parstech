const InvoicePrint = {
    init() {
        this.setupEventListeners();
        this.initializeQRCode();
        this.initializeAnimations();
        this.setupPriceFormatting();
    },

    setupEventListeners() {
        // دکمه پرینت
        const printButton = document.querySelector('.print-button');
        if (printButton) {
            printButton.addEventListener('click', this.handlePrint.bind(this));
        }

        // دکمه دانلود PDF
        const pdfButton = document.querySelector('.pdf-button');
        if (pdfButton) {
            pdfButton.addEventListener('click', this.handlePDFDownload.bind(this));
        }

        // برای نمایش تصویر بزرگ لوگو
        const logo = document.querySelector('.company-logo');
        if (logo) {
            logo.addEventListener('click', this.handleLogoClick.bind(this));
        }
    },

    handlePrint() {
        window.print();
    },

    handlePDFDownload() {
        const invoiceNumber = document.querySelector('.invoice-number')?.textContent || 'invoice';
        html2pdf()
            .from(document.querySelector('.invoice-container'))
            .save(`${invoiceNumber}.pdf`);
    },

    handleLogoClick(e) {
        const modal = document.createElement('div');
        modal.className = 'logo-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <img src="${e.target.src}" alt="Company Logo">
                <button class="close-modal">×</button>
            </div>
        `;
        document.body.appendChild(modal);

        modal.querySelector('.close-modal').onclick = () => modal.remove();
        modal.onclick = (e) => {
            if (e.target === modal) modal.remove();
        };
    },

    initializeQRCode() {
        const qrContainer = document.getElementById('invoice-qr');
        if (qrContainer && window.QRCode) {
            const invoiceData = {
                number: document.querySelector('.invoice-number')?.textContent,
                date: document.querySelector('.invoice-date')?.textContent,
                total: document.querySelector('.total-amount')?.textContent
            };

            new QRCode(qrContainer, {
                text: JSON.stringify(invoiceData),
                width: 100,
                height: 100
            });
        }
    },

    initializeAnimations() {
        const elements = document.querySelectorAll('.animate-fade-in');
        elements.forEach((el, index) => {
            el.style.animationDelay = `${index * 0.1}s`;
        });
    },

    setupPriceFormatting() {
        const priceElements = document.querySelectorAll('.price-format');
        priceElements.forEach(el => {
            const value = parseInt(el.textContent.replace(/[^0-9]/g, ''));
            el.textContent = this.formatPrice(value);
        });
    },

    formatPrice(price) {
        return new Intl.NumberFormat('fa-IR').format(price);
    },

    calculateTotals() {
        const rows = document.querySelectorAll('.item-row');
        let subtotal = 0;

        rows.forEach(row => {
            const quantity = parseInt(row.querySelector('.quantity').textContent);
            const price = parseInt(row.querySelector('.price').textContent.replace(/[^0-9]/g, ''));
            const discount = parseInt(row.querySelector('.discount').textContent.replace(/[^0-9]/g, '')) || 0;

            const total = (quantity * price) - discount;
            row.querySelector('.total').textContent = this.formatPrice(total);
            subtotal += total;
        });

        // به‌روزرسانی جمع‌ها
        const tax = Math.round(subtotal * 0.09); // 9% مالیات
        const total = subtotal + tax;

        document.querySelector('.subtotal-amount').textContent = this.formatPrice(subtotal);
        document.querySelector('.tax-amount').textContent = this.formatPrice(tax);
        document.querySelector('.total-amount').textContent = this.formatPrice(total);
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => InvoicePrint.init());
