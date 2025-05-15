document.addEventListener("DOMContentLoaded", function () {
    // آرایه اقلام فاکتور
    let invoiceItems = [];

    function showAlert(message, icon = 'error') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: icon,
                html: `<div style="font-size:1.2rem;"><b>${message}</b></div>`,
                timer: 3000,
                showConfirmButton: false,
                position: 'top',
                background: '#fff3f3',
                color: '#d33',
                customClass: {
                    popup: 'swal2-border-radius-lg'
                }
            });
        } else {
            alert(message);
        }
    }

    // افزودن محصول یا خدمت
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.add-product-btn')) {
            e.preventDefault();
            let btn = e.target.closest('.add-product-btn');
            let id = String(btn.dataset.id).trim();
            let type = String(btn.dataset.type).trim();

            fetch(`/sales/item-info?id=${id}&type=${type}`)
                .then(response => response.json())
                .then(item => {
                    let stock = parseInt(item.stock) || 0;
                    // اگر موجودی صفر باشد هیچ ردیفی اضافه نشود
                    if (stock < 1) {
                        showAlert(`محصول "${item.name}" موجودی ندارد و قابل افزودن نیست!`);
                        return; // اضافه نشود
                    }
                    let idx = invoiceItems.findIndex(x => x.id == id && x.type == type);
                    if (idx > -1) {
                        if (invoiceItems[idx].count >= stock) {
                            invoiceItems[idx].count = stock;
                            renderInvoiceItemsTable();
                            showAlert(`این محصول "${item.name}" فقط ${stock} عدد موجودی دارد و بیش از این نمی‌توانید اضافه کنید.`);
                            return;
                        }
                        invoiceItems[idx].count += 1;
                        renderInvoiceItemsTable();
                        return;
                    } else {
                        item.count = 1;
                        item.id = id;
                        item.type = type;
                        item.desc = "";
                        item.discount = 0;
                        item.tax = 0;
                        invoiceItems.push(item);
                        renderInvoiceItemsTable();
                        return;
                    }
                })
                .catch(() => showAlert('خطا در دریافت اطلاعات محصول!'));
        }
    });

    // حذف ردیف
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

    // رندر جدول و کنترل شرط‌ها
    function renderInvoiceItemsTable() {
        let tbody = document.getElementById('invoice-items-body');
        tbody.innerHTML = '';
        let total = 0, count = 0;
        invoiceItems.forEach((item, idx) => {
            let itemDiscount = parseInt(item.discount) || 0;
            let itemTax = parseInt(item.tax) || 0;
            let itemCount = parseInt(item.count) || 1;
            let itemPrice = parseInt(item.sell_price) || 0;
            let itemStock = parseInt(item.stock) || 1;
            if (itemCount > itemStock) {
                itemCount = itemStock;
                invoiceItems[idx].count = itemStock;
                showAlert(`این محصول "${item.name}" فقط ${itemStock} عدد موجودی دارد و بیش از این نمی‌توانید اضافه کنید.`);
            }
            if (itemCount < 1) {
                itemCount = 1;
                invoiceItems[idx].count = 1;
            }
            if (itemPrice < 0) {
                itemPrice = 0;
                invoiceItems[idx].sell_price = 0;
            }
            if (itemDiscount < 0) {
                itemDiscount = 0;
                invoiceItems[idx].discount = 0;
            }
            if (itemDiscount > (itemCount * itemPrice)) {
                itemDiscount = itemCount * itemPrice;
                invoiceItems[idx].discount = itemDiscount;
            }
            if (itemTax < 0) {
                itemTax = 0;
                invoiceItems[idx].tax = 0;
            }
            let itemTotal = (itemCount * itemPrice) - itemDiscount + itemTax;
            total += itemTotal;
            count += itemCount;

            let row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-invoice-item">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
                <td>${item.name ?? ''}</td>
                <td><input type="text" class="form-control input-sm item-desc-input" name="descs[]" value="${item.desc ?? ''}" data-idx="${idx}" maxlength="255"></td>
                <td>${item.unit ?? ''}</td>
                <td>
                    <input type="number" class="form-control input-sm item-count-input" name="counts[]" value="${itemCount}" min="1" max="${itemStock}" data-idx="${idx}">
                </td>
                <td><input type="number" class="form-control input-sm item-price-input" name="unit_prices[]" value="${itemPrice}" min="0" step="1" data-idx="${idx}"></td>
                <td><input type="number" class="form-control input-sm item-discount-input" name="discounts[]" value="${itemDiscount}" min="0" max="${itemCount*itemPrice}" step="1" data-idx="${idx}"></td>
                <td><input type="number" class="form-control input-sm item-tax-input" name="taxes[]" value="${itemTax}" min="0" step="1" data-idx="${idx}"></td>
                <td class="item-total">${itemTotal.toLocaleString()} ریال</td>
            `;
            tbody.appendChild(row);
        });
        document.getElementById('total_count').textContent = count;
        document.getElementById('total_amount').textContent = total.toLocaleString() + ' ریال';
        document.getElementById('invoice-total-amount').textContent = total.toLocaleString() + ' ریال';
    }

    // کنترل شرط‌ها روی ورودی‌های جدول
    document.body.addEventListener('input', function (e) {
        if (e.target.classList.contains('item-count-input')) {
            let idx = parseInt(e.target.dataset.idx);
            let val = parseInt(e.target.value);
            let max = parseInt(invoiceItems[idx].stock || 1);
            if (isNaN(val) || val < 1) {
                showAlert('تعداد باید حداقل ۱ باشد.');
                val = 1;
            } else if (val > max) {
                showAlert(`این محصول "${invoiceItems[idx].name}" فقط ${max} عدد موجودی دارد و بیش از این نمی‌توانید وارد کنید.`);
                val = max;
            }
            invoiceItems[idx].count = val;
            renderInvoiceItemsTable();
        }
        if (e.target.classList.contains('item-price-input')) {
            let idx = parseInt(e.target.dataset.idx);
            let val = parseInt(e.target.value) || 0;
            if (val < 0) {
                val = 0;
            }
            invoiceItems[idx].sell_price = val;
            renderInvoiceItemsTable();
        }
        if (e.target.classList.contains('item-discount-input')) {
            let idx = parseInt(e.target.dataset.idx);
            let val = parseInt(e.target.value) || 0;
            let maxDiscount = parseInt(invoiceItems[idx].sell_price) * invoiceItems[idx].count;
            if (val < 0) {
                val = 0;
            } else if (val > maxDiscount) {
                val = maxDiscount;
            }
            invoiceItems[idx].discount = val;
            renderInvoiceItemsTable();
        }
        if (e.target.classList.contains('item-tax-input')) {
            let idx = parseInt(e.target.dataset.idx);
            let val = parseInt(e.target.value) || 0;
            if (val < 0) {
                val = 0;
            }
            invoiceItems[idx].tax = val;
            renderInvoiceItemsTable();
        }
        if (e.target.classList.contains('item-desc-input')) {
            let idx = parseInt(e.target.dataset.idx);
            invoiceItems[idx].desc = e.target.value;
        }
    });

    // سایر بخش‌ها مثل تقویم، شماره فاکتور و جستجو ـ بدون تغییر و مطابق قبل
});
