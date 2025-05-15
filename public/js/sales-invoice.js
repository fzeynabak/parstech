document.addEventListener("DOMContentLoaded", function () {
    // تقویم شمسی
    if (typeof $ !== 'undefined' && $.fn.persianDatepicker) {
        $('#issued_at_jalali').persianDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: false,
            autoClose: true,
            onSelect: function(unix) {
                let pd = new persianDate(unix).toLocale('en').format('YYYY-MM-DD');
                $('#issued_at').val(pd);
            }
        });
        $('#due_at_jalali').persianDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: false,
            autoClose: true,
            onSelect: function(unix) {
                let pd = new persianDate(unix).toLocale('en').format('YYYY-MM-DD');
                $('#due_at').val(pd);
            }
        });
    }

    // شماره فاکتور اتوماتیک و سوییچ
    const invoiceNumberInput = document.getElementById('invoice_number');
    const invoiceNumberSwitch = document.getElementById('invoiceNumberSwitch');
    if (invoiceNumberInput && invoiceNumberSwitch) {
        function setInvoiceNumberReadOnly(isAuto) {
            invoiceNumberInput.readOnly = isAuto;
            if(isAuto) {
                fetch('/api/invoices/next-number')
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
        invoiceNumberSwitch.addEventListener('change', function(){
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

    // تب‌بندی محصولات و خدمات (برای فعال‌سازی تب‌های بوت‌استرپ)
    const productTabs = document.querySelectorAll('.sales-product-tabs .nav-link');
    productTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            productTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
            const target = document.querySelector(this.dataset.bsTarget);
            if(target) {
                target.classList.add('show', 'active');
            }
        });
    });

    // بارگذاری محصولات و خدمات (Ajax)
    ['product', 'service'].forEach(type => {
        function renderRows(items) {
            let html = '';
            items.forEach(item => {
                html += `<tr>
                    <td>
                        <button class="btn btn-success btn-sm add-product-btn" data-id="${item.id}" data-type="${type}">
                            <i class="fa fa-plus"></i>
                        </button>
                    </td>
                    <td>${item.code ?? '-'}</td>
                    <td><img src="${item.image ?? ''}" class="rounded" style="width:40px;height:40px;object-fit:cover"></td>
                    <td>${item.name ?? '-'}</td>
                    <td>${item.stock ?? '-'}</td>
                    <td>${item.category ?? '-'}</td>
                    <td>${item.sell_price ? parseInt(item.sell_price).toLocaleString() : '-'}</td>
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
                    tbody.innerHTML = renderRows(data);
                });
        }

        // بارگذاری اولیه
        loadList();

        // جستجو
        const searchInput = document.getElementById(type + '-search-input');
        if(searchInput){
            searchInput.addEventListener('input', function () {
                let q = this.value.trim();
                loadList(q, true);
            });
        }
    });

    // افزودن محصول/خدمت به سبد خرید
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.add-product-btn')) {
            e.preventDefault();
            let btn = e.target.closest('.add-product-btn');
            let id = btn.dataset.id;
            let type = btn.dataset.type;
            // باید اینجا با ایجکس اطلاعات محصول را گرفته و به جدول سبد خرید اضافه کنید
            // مثال:
                fetch(`/sales/item-info?id=${id}&type=${type}`)
                    .then(response => response.json())
                    .then(item => {
                        // افزودن به جدول فاکتور
                        addToCart(item);
                    })
                    .catch(error => console.error('Error:', error));
        }
        if (e.target.closest('.remove-invoice-item')) {
            e.preventDefault();
            let row = e.target.closest('tr');
            if(row) row.remove();
            updateCartTotals();
        }
    });

    // افزودن ردیف به جدول سبد خرید (تابع نمونه)
    function addToCart(item){
        let tbody = document.getElementById('invoice-items-body');
        let row = document.createElement('tr');
        row.innerHTML = `
            <td><button type="button" class="btn btn-danger btn-sm remove-invoice-item"><i class="fa fa-trash"></i></button></td>
            <td>${item.name}</td>
            <td><input type="text" class="form-control input-sm" name="descs[]" value=""></td>
            <td>${item.unit ?? ''}</td>
            <td><input type="number" class="form-control input-sm" name="counts[]" value="1" min="1"></td>
            <td><input type="text" class="form-control input-sm" name="unit_prices[]" value="${item.sell_price ?? 0}"></td>
            <td><input type="text" class="form-control input-sm" name="discounts[]" value="0"></td>
            <td><input type="text" class="form-control input-sm" name="taxes[]" value="0"></td>
            <td class="item-total">${item.sell_price ?? 0}</td>
        `;
        tbody.appendChild(row);
        updateCartTotals();
    }

    // محاسبه جمع کل سبد خرید
    function updateCartTotals(){
        let tbody = document.getElementById('invoice-items-body');
        let total = 0;
        let count = 0;
        tbody.querySelectorAll('tr').forEach(row => {
            let qty = +row.querySelector('input[name="counts[]"]').value || 0;
            let price = +row.querySelector('input[name="unit_prices[]"]').value || 0;
            let discount = +row.querySelector('input[name="discounts[]"]').value || 0;
            let tax = +row.querySelector('input[name="taxes[]"]').value || 0;
            let itemTotal = ((qty * price) - discount) + tax;
            row.querySelector('.item-total').textContent = itemTotal.toLocaleString() + ' ریال';
            total += itemTotal;
            count += qty;
        });
        document.getElementById('total_count').textContent = count;
        document.getElementById('total_amount').textContent = total.toLocaleString() + ' ریال';
        document.getElementById('invoice-total-amount').textContent = total.toLocaleString() + ' ریال';
    }

    // تغییرات دستی مقدار‌ها در سبد خرید
    document.body.addEventListener('input', function(e){
        if (e.target.closest('#invoice-items-body input')) {
            updateCartTotals();
        }
    });
});
