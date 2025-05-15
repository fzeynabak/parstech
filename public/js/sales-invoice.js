document.addEventListener("DOMContentLoaded", function () {
    // آرایه اقلام فاکتور
    let invoiceItems = [];

    // افزودن محصول/خدمت به فاکتور
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.add-product-btn')) {
            e.preventDefault();
            let btn = e.target.closest('.add-product-btn');
            let id = String(btn.dataset.id).trim();
            let type = String(btn.dataset.type).trim();

            // دریافت اطلاعات محصول با ایجکس
            fetch(`/sales/item-info?id=${id}&type=${type}`)
                .then(response => response.json())
                .then(item => {
                    // بررسی تکراری بودن
                    let idx = invoiceItems.findIndex(x => x.id == id && x.type == type);
                    if (idx > -1) {
                        // اگر وجود داشت، فقط تعداد را زیاد کن
                        if (invoiceItems[idx].count + 1 > item.stock) {
                            showAlert(`تعداد موجودی محصول "${item.name}" ${item.stock} عدد است. بیش از این تعداد نمی‌توانید اضافه کنید.`);
                        } else {
                            invoiceItems[idx].count += 1;
                            renderInvoiceItemsTable();
                            showAlert('تعداد این محصول در فاکتور افزایش یافت.', 'info');
                        }
                    } else {
                        // اگر نبود، به آرایه اضافه کن
                        item.count = 1; // مقدار اولیه تعداد
                        item.id = id;
                        item.type = type;
                        invoiceItems.push(item);
                        renderInvoiceItemsTable();
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    });

    // حذف آیتم
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.remove-invoice-item')) {
            e.preventDefault();
            let row = e.target.closest('tr');
            if (row) {
                let idx = Array.from(row.parentNode.children).indexOf(row);
                invoiceItems.splice(idx, 1);
                renderInvoiceItemsTable();
            }
        }
    });

    // ساخت جدول فاکتور بر اساس آرایه
    function renderInvoiceItemsTable() {
        let tbody = document.getElementById('invoice-items-body');
        tbody.innerHTML = '';
        let total = 0, count = 0;

        invoiceItems.forEach((item, idx) => {
            const itemTotal = (item.count * (parseInt(item.sell_price) || 0)) - (parseInt(item.discount) || 0) + (parseInt(item.tax) || 0);
            total += itemTotal;
            count += item.count;

            let row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-invoice-item">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
                <td>${item.name ?? ''}</td>
                <td><input type="text" class="form-control input-sm" name="descs[]" value="${item.desc ?? ''}"></td>
                <td>${item.unit ?? ''}</td>
                <td>
                    <input type="number" class="form-control input-sm item-count-input" name="counts[]" value="${item.count}" min="1" max="${item.stock}" data-idx="${idx}">
                </td>
                <td><input type="text" class="form-control input-sm" name="unit_prices[]" value="${item.sell_price ?? 0}"></td>
                <td><input type="text" class="form-control input-sm" name="discounts[]" value="${item.discount ?? 0}"></td>
                <td><input type="text" class="form-control input-sm" name="taxes[]" value="${item.tax ?? 0}"></td>
                <td class="item-total">${itemTotal.toLocaleString()} ریال</td>
            `;
            tbody.appendChild(row);
        });

        document.getElementById('total_count').textContent = count;
        document.getElementById('total_amount').textContent = total.toLocaleString() + ' ریال';
        document.getElementById('invoice-total-amount').textContent = total.toLocaleString() + ' ریال';
    }

    // تغییر تعداد هر ردیف
    document.body.addEventListener('input', function (e) {
        if (e.target.classList.contains('item-count-input')) {
            let idx = parseInt(e.target.dataset.idx);
            let val = parseInt(e.target.value);
            if (isNaN(val) || val < 1) {
                val = 1;
            } else if (val > invoiceItems[idx].stock) {
                val = invoiceItems[idx].stock;
                showAlert(`تعداد موجودی محصول "${invoiceItems[idx].name}" ${invoiceItems[idx].stock} عدد است. بیش از این تعداد نمی‌توانید اضافه کنید.`);
            }
            invoiceItems[idx].count = val;
            renderInvoiceItemsTable();
        }
    });

    // نمایش پیام هشدار با SweetAlert2
    function showAlert(message, icon = 'warning') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: icon,
                text: message,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            alert(message); // اگر SweetAlert2 موجود نبود، از alert ساده استفاده کن
        }
    }
});
