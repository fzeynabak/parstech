document.addEventListener("DOMContentLoaded", function () {
    // تقویم شمسی
    if (typeof $ !== 'undefined' && $.fn.persianDatepicker) {
        // تاریخ صدور
        $('#issued_at_jalali').persianDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: !!$('#issued_at_jalali').val(),
            autoClose: true,
            onSelect: function(unix) {
                let pd = new persianDate(unix).toLocale('en').format('YYYY-MM-DD');
                $('#issued_at').val(pd);
            }
        });
        // تاریخ سررسید
        $('#due_at_jalali').persianDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: !!$('#due_at_jalali').val(),
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









});
