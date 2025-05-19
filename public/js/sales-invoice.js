let invoiceItems = [];

document.addEventListener("DOMContentLoaded", function () {

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

    // شماره فاکتور اتوماتیک/دستی
    const invoiceNumberInput = document.getElementById('invoice_number');
    const invoiceNumberSwitch = document.getElementById('invoiceNumberSwitch');
    if (invoiceNumberInput && invoiceNumberSwitch) {
        function setInvoiceNumberReadOnly(isAuto) {
            invoiceNumberInput.readOnly = isAuto;
            if (isAuto) {
                fetch('/sales/next-invoice-number')
                    .then(response => response.json())
                    .then(data => {
                        invoiceNumberInput.value = data.number;
                    })
                    .catch(() => {
                        invoiceNumberInput.value = 'invoices-10001';
                    });
            } else {
                invoiceNumberInput.value = '';
                invoiceNumberInput.focus();
            }
        }
        setInvoiceNumberReadOnly(invoiceNumberSwitch.checked);
        invoiceNumberSwitch.addEventListener('change', function () {
            setInvoiceNumberReadOnly(this.checked);
        });
    }

    // جستجوی مشتری
    const customerSearchInput = document.getElementById("customer_search");
    const customerSearchResults = document.getElementById("customer-search-results");
    const customerIdInput = document.getElementById("customer_id");
    if (customerSearchInput && customerSearchResults && customerIdInput) {
        customerSearchInput.addEventListener("input", function () {
            const query = customerSearchInput.value.trim();
            if (query.length === 0) {
                customerSearchResults.classList.remove("show");
                customerSearchResults.innerHTML = "";
                return;
            }
            fetch(`/customers/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    customerSearchResults.innerHTML = "";
                    if (data.length > 0) {
                        data.forEach(customer => {
                            const item = document.createElement("div");
                            item.className = "dropdown-item";
                            item.textContent = customer.name;
                            item.dataset.id = customer.id;
                            item.addEventListener("click", function () {
                                customerSearchInput.value = customer.name;
                                customerIdInput.value = customer.id;
                                customerSearchResults.classList.remove("show");
                            });
                            customerSearchResults.appendChild(item);
                        });
                        customerSearchResults.classList.add("show");
                    } else {
                        customerSearchResults.innerHTML = "<div class='dropdown-item text-muted'>موردی یافت نشد.</div>";
                        customerSearchResults.classList.add("show");
                    }
                });
        });
        document.addEventListener("click", function (event) {
            if (!customerSearchResults.contains(event.target) && event.target !== customerSearchInput) {
                customerSearchResults.classList.remove("show");
            }
        });
    }

    // بارگذاری و رندر لیست محصولات و خدمات
    ['product', 'service'].forEach(type => {
        function renderRows(items) {
            let html = '';
            items.forEach(item => {
                let addBtn = `<button class="btn btn-success btn-sm add-product-btn" data-id="${item.id}" data-type="${type}"><i class="fa fa-plus"></i></button>`;
                html += `<tr>
                    <td>${addBtn}</td>
                    <td>${item.code ?? '-'}</td>
                    <td>${item.name ?? '-'}</td>
                    <td>${item.category ?? '-'}</td>
                    ${type === 'product' ? `<td>${item.stock ?? '-'}</td>` : ''}
                    <td>${item.sell_price ? parseInt(item.sell_price).toLocaleString() : '-'}</td>
                    <td>${item.description ?? ''}</td>
                </tr>`;
            });
            return html;
        }

        function loadList(query = '', reset = true) {
            let url = type === 'product'
                ? '/products/ajax-list'
                : '/services/ajax-list';
            let params = '?limit=10';
            if (query) {
                params += '&q=' + encodeURIComponent(query);
            }
            fetch(url + params)
                .then(r => r.json())
                .then(data => {
                    let tbody = document.getElementById(type + '-table-body');
                    if (tbody) tbody.innerHTML = renderRows(data);
                });
        }
        loadList();

        const searchInput = document.getElementById(type + '-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                let q = this.value.trim();
                loadList(q, true);
            });
        }
    });

    // افزودن محصول یا خدمت به فاکتور
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.add-product-btn')) {
            let btn = e.target.closest('.add-product-btn');
            if (btn.classList.contains('d-none') || btn.disabled) return;
            e.preventDefault();
            let id = String(btn.dataset.id).trim();
            let type = String(btn.dataset.type).trim();

            fetch(`/sales/item-info?id=${id}&type=${type}`)
                .then(response => response.json())
                .then(item => {
                    if (type === 'service') item.stock = 1;
                    let stock = parseInt(item.stock) || 0;
                    if (stock < 1) return;
                    let idx = invoiceItems.findIndex(x => x.id == id && x.type == type);
                    if (idx > -1) {
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
                .catch(() => showAlert('خطا در دریافت اطلاعات!'));
        }
    });

    // حذف ردیف از فاکتور
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

    // رندر جدول فاکتور و کنترل شرط‌ها (حفظ فوکوس و کرسر)
    function renderInvoiceItemsTable() {
        let tbody = document.getElementById('invoice-items-body');
        if (!tbody) return;

        // ذخیره input فوکوس‌شده و موقعیت کرسر
        let active = document.activeElement;
        let focusInfo = null;
        if (active && active.classList && typeof active.selectionStart === 'number') {
            focusInfo = {
                name: active.name,
                idx: active.dataset.idx,
                pos: active.selectionStart
            };
        }

        tbody.innerHTML = '';
        let total = 0, count = 0;
        invoiceItems.forEach((item, idx) => {
            let itemDiscount = parseFloat(item.discount) || 0;
            let itemTax = parseFloat(item.tax) || 0;
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
            let itemTotal = (itemCount * itemPrice) - itemDiscount + ((itemTax / 100) * ((itemCount * itemPrice) - itemDiscount));
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
                <td><input type="number" class="form-control input-sm item-discount-input" name="discounts[]" value="${itemDiscount}" min="0" max="${itemCount*itemPrice}" step="0.01" data-idx="${idx}"></td>
                <td>
                    <input type="number" class="form-control input-sm item-tax-input" name="taxes[]" value="${itemTax}" min="0" max="100" step="0.01" data-idx="${idx}">
                    <span class="small">%</span>
                </td>
                <td class="item-total">${itemTotal.toLocaleString()} ریال</td>
            `;
            tbody.appendChild(row);
        });

        let totalCountEl = document.getElementById('total_count');
        let totalAmountEl = document.getElementById('total_amount');
        let invoiceTotalEl = document.getElementById('invoice-total-amount');
        if (totalCountEl) totalCountEl.textContent = count;
        if (totalAmountEl) totalAmountEl.textContent = total.toLocaleString() + ' ریال';
        if (invoiceTotalEl) invoiceTotalEl.textContent = total.toLocaleString() + ' ریال';

        // بعد از رندر، فوکوس و کرسر را بازگردان
        if (focusInfo) {
            let selector = `[name="${focusInfo.name}"][data-idx="${focusInfo.idx}"]`;
            let input = tbody.querySelector(selector);
            if (input) {
                input.focus();
                input.setSelectionRange(focusInfo.pos, focusInfo.pos);
            }
        }
    }

    // کنترل شرط‌ها روی ورودی‌های جدول
    document.body.addEventListener('input', function (e) {
        let classList = e.target.classList;

        if (classList.contains('item-count-input')) {
            let idx = parseInt(e.target.dataset.idx);
            let val = parseInt(e.target.value);
            let max = parseInt(invoiceItems[idx].stock || 1);
            if (isNaN(val) || val < 1) {
                showAlert('تعداد باید حداقل ۱ باشد.');
                val = 1;
            } else if (val > max) {
                showAlert(`این محصول "${invoiceItems[idx].name}" فقط ${max} عدد موجودی دارد و بیش از این تعداد نمی‌توانید وارد کنید.`);
                val = max;
            }
            invoiceItems[idx].count = val;
            renderInvoiceItemsTable();
        }
        if (classList.contains('item-price-input')) {
            let idx = parseInt(e.target.dataset.idx);
            let val = parseInt(e.target.value) || 0;
            if (val < 0) val = 0;
            invoiceItems[idx].sell_price = val;
            renderInvoiceItemsTable();
        }
        if (classList.contains('item-discount-input')) {
            let idx = parseInt(e.target.dataset.idx);
            let val = parseFloat(e.target.value) || 0;
            let maxDiscount = parseInt(invoiceItems[idx].sell_price) * invoiceItems[idx].count;
            if (val < 0) val = 0;
            else if (val > maxDiscount) val = maxDiscount;
            invoiceItems[idx].discount = val;
            renderInvoiceItemsTable();
        }
        if (classList.contains('item-tax-input')) {
            let idx = parseInt(e.target.dataset.idx);
            let val = parseFloat(e.target.value) || 0;
            if (val < 0) val = 0;
            if (val > 100) val = 100;
            invoiceItems[idx].tax = val;
            renderInvoiceItemsTable();
        }
        if (classList.contains('item-desc-input')) {
            let idx = parseInt(e.target.dataset.idx);
            invoiceItems[idx].desc = e.target.value;
        }
    });

    // ذخیره اقلام به input مخفی هنگام ثبت فرم
    let salesForm = document.getElementById('sales-invoice-form');
    if (salesForm) {
        salesForm.addEventListener('submit', function(e) {
            let productsInput = document.getElementById('products_input');
            if(productsInput) {
                productsInput.value = JSON.stringify(invoiceItems);
            }
        });
    }
});
