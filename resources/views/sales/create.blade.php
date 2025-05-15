@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/invoice-create.css') }}">
<link rel="stylesheet" href="{{ asset('css/persian-datepicker.min.css') }}">

<form id="sales-invoice-form" class="row g-4" autocomplete="off" method="POST" action="{{ route('sales.store') }}">
    @csrf
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <!-- شماره فاکتور و قابلیت قفل/ویرایش -->
    <div class="col-12 col-md-4">
        <div class="sales-form-section">
            <label class="sales-form-label mb-2" for="invoice_number">
                شماره فاکتور
                <span class="ms-1" data-bs-toggle="tooltip"
                      title="کد فاکتور به صورت خودکار تولید و قفل است. اگر سوییچ را غیرفعال کنید می‌توانید شماره دلخواه وارد کنید.">
                    <i class="fa-regular fa-circle-question text-primary"></i>
                </span>
            </label>
            <div class="input-group">
                <input type="text"
                       class="form-control sales-form-input"
                       id="invoice_number"
                       name="invoice_number"
                       value="{{ old('invoice_number', $nextNumber ?? '') }}"
                       readonly
                       required
                >
                <span class="input-group-text bg-white border-0">
                    <label class="form-switch m-0" style="cursor:pointer;">
                        <input type="checkbox" id="invoiceNumberSwitch" checked>
                        <span class="slider"></span>
                    </label>
                </span>
            </div>
            <div class="form-text text-muted mt-1">
                مثال: invoices-10001
            </div>
        </div>
    </div>

    <!-- نام مشتری -->
    <div class="col-12 col-md-4">
        <label for="customer_search" class="form-label">مشتری</label>
        <input type="text" class="form-control" id="customer_search" placeholder="جستجوی مشتری...">
        <input type="hidden" name="customer_id" id="customer_id">
        <div class="dropdown-menu" id="customer-search-results" style="width:100%"></div>
        <div class="form-text text-muted mt-1">نام مشتری را تایپ کنید و انتخاب نمایید.</div>
    </div>

    <!-- فروشنده -->
    <div class="col-12 col-md-4">
        <div class="sales-form-section">
            <label class="sales-form-label mb-2" for="seller_id">فروشنده</label>
            <select class="form-select sales-form-select" name="seller_id" id="seller_id" required>
                <option value="">انتخاب کنید</option>
                @foreach($sellers as $seller)
                    <option value="{{ $seller->id }}"
                        {{ old('seller_id') == $seller->id ? 'selected' : '' }}>
                        {{ $seller->seller_code }} -
                        {{ $seller->first_name }} {{ $seller->last_name }}
                        @if($seller->company_name)
                            ({{ $seller->company_name }})
                        @elseif($seller->title)
                            ({{ $seller->title }})
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- واحد پول و تاریخ صدور و سررسید -->
    <div class="col-12 col-lg-6">
        <div class="sales-form-section">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="sales-form-label mb-2" for="currency_id">واحد پول</label>
                    <select class="form-select sales-form-select" name="currency_id" id="currency_id" required>
                        <option value="">انتخاب کنید</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                {{ $currency->name }} ({{ $currency->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="sales-form-label mb-2" for="issued_at_jalali">تاریخ صدور</label>
                    <input type="text" class="form-control sales-form-input datepicker"
                        id="issued_at_jalali" name="issued_at_jalali"
                        value="{{ old('issued_at_jalali') ?? '' }}" placeholder="تاریخ صدور شمسی" autocomplete="off">
                    <input type="hidden" name="issued_at" id="issued_at" value="{{ old('issued_at') }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="sales-form-label mb-2" for="due_at_jalali">
                        تاریخ سررسید
                        <span class="ms-1" data-bs-toggle="tooltip" title="در این تاریخ هشدار پرداخت صادر می‌شود.">
                            <i class="fa-regular fa-bell text-primary"></i>
                        </span>
                    </label>
                    <input type="text" class="form-control sales-form-input datepicker"
                        id="due_at_jalali" name="due_at_jalali"
                        value="{{ old('due_at_jalali') ?? '' }}" placeholder="تاریخ سررسید شمسی" autocomplete="off">
                    <input type="hidden" name="due_at" id="due_at" value="{{ old('due_at') }}">
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="invoice_items" id="invoice_items_input">

    <!-- دکمه ثبت اولیه -->
    <div class="col-12 col-lg-6 d-flex align-items-center justify-content-end">
        <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm">
            <i class="fa-solid fa-floppy-disk ms-2"></i>
            ثبت فاکتور و افزودن محصولات
        </button>
    </div>
</form>
@include('sales.partials.product_list')
@include('sales.partials.invoice_items_table')
@endsection

@section('scripts')
<script src="{{ asset('js/sales-invoice-init.js') }}"></script>
<script src="{{ asset('js/sales-products.js') }}"></script>
<script src="{{ asset('js/persian-date.js') }}"></script>
<script src="{{ asset('js/persian-datepicker.min.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // جستجوی مشتری
    const customerSearchInput = document.getElementById("customer_search");
    const customerSearchResults = document.getElementById("customer-search-results");
    const customerIdInput = document.getElementById("customer_id");
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
            })
            .catch(error => {
                console.error("خطا در جستجو:", error);
            });
    });
    document.addEventListener("click", function (event) {
        if (!customerSearchResults.contains(event.target) && event.target !== customerSearchInput) {
            customerSearchResults.classList.remove("show");
        }
    });

    // تقویم شمسی
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
    if ($('#issued_at_jalali').val()) {
        let val = $('#issued_at_jalali').val().replace(/\//g, '-');
        $('#issued_at').val(val);
    }
    if ($('#due_at_jalali').val()) {
        let val = $('#due_at_jalali').val().replace(/\//g, '-');
        $('#due_at').val(val);
    }

    // شماره فاکتور اتوماتیک و سوییچ
    const invoiceNumberInput = document.getElementById('invoice_number');
    const invoiceNumberSwitch = document.getElementById('invoiceNumberSwitch');

    function setInvoiceNumberReadOnly(isAuto) {
        invoiceNumberInput.readOnly = isAuto;
        if(isAuto) {
            fetch('/api/invoices/next-number')
                .then(response => {
                    if (!response.ok) throw new Error('HTTP error ' + response.status);
                    return response.json();
                })
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
});
</script>
@endsection
