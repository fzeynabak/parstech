@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/persianDatepicker-melon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sales-invoice.css') }}">
    <style>
        .form-switch .slider {
            display: inline-block;
            width: 38px;
            height: 20px;
            background: #eee;
            border-radius: 20px;
            position: relative;
            transition: background 0.2s;
        }
        .form-switch input[type=checkbox] {
            display: none;
        }
        .form-switch input[type=checkbox]:checked + .slider {
            background: #4caf50;
        }
        .form-switch .slider:before {
            content: "";
            position: absolute;
            left: 3px;
            top: 3px;
            width: 14px;
            height: 14px;
            background: #fff;
            border-radius: 50%;
            transition: 0.2s;
        }
        .form-switch input[type=checkbox]:checked + .slider:before {
            left: 21px;
        }
    </style>
@endsection

@section('content')
<div class="sales-invoice-container">
    <div class="sales-invoice-header d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0"><i class="fa fa-file-invoice-dollar ms-2"></i> فاکتور فروش</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ implode('، ', $errors->all()) }}</div>
    @endif

    <form id="sales-invoice-form" class="row g-3" autocomplete="off" method="POST" action="{{ route('sales.store') }}">
        @csrf

        <div class="row g-2 mb-2">
            <div class="col-md-2">
                <label>شماره</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="invoice_number" id="invoice_number" value="{{ old('invoice_number', $nextNumber ?? '') }}" readonly required>
                    <span class="input-group-text bg-white border-0">
                        <label class="form-switch m-0" style="cursor:pointer;">
                            <input type="checkbox" id="invoiceNumberSwitch" checked>
                            <span class="slider"></span>
                        </label>
                    </span>
                </div>
            </div>
            <div class="col-md-2">
                <label>ارجاع</label>
                <input type="text" class="form-control" name="reference" id="reference">
            </div>
            <div class="col-md-2">
                <label>تاریخ</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker" name="issued_at_jalali" id="issued_at_jalali" value="{{ old('issued_at_jalali') ?? '' }}" autocomplete="off">
                    <input type="hidden" name="issued_at" id="issued_at" value="{{ old('issued_at') }}">
                    <button type="button" class="btn btn-outline-secondary" id="openIssuedDatePicker"><i class="fa fa-calendar"></i></button>
                </div>
            </div>
            <div class="col-md-2">
                <label>تاریخ سررسید</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker" name="due_at_jalali" id="due_at_jalali" value="{{ old('due_at_jalali') ?? '' }}" autocomplete="off">
                    <input type="hidden" name="due_at" id="due_at" value="{{ old('due_at') }}">
                    <button type="button" class="btn btn-outline-secondary" id="openDueDatePicker"><i class="fa fa-calendar"></i></button>
                </div>
            </div>
            <div class="col-md-2">
                <label>واحد پول</label>
                <select class="form-select" name="currency_id" id="currency_id" required>
                    <option value="">انتخاب کنید...</option>
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->id }}">{{ $currency->name }} - {{ $currency->code }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <label>مشتری <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="customer_search" placeholder="انتخاب کنید...">
                    <input type="hidden" name="customer_id" id="customer_id" required>
                    <button type="button" class="btn btn-outline-success" id="addCustomerBtn"><i class="fa fa-plus"></i></button>
                </div>
                <div class="dropdown-menu" id="customer-search-results" style="width:100%"></div>
            </div>
            <div class="col-md-3">
                <label>عنوان</label>
                <input type="text" class="form-control" name="title" id="invoice_title" placeholder="عنوان...">
            </div>
            <div class="col-md-3">
                <label>فروشنده <span class="text-danger">*</span></label>
                <select class="form-select" name="seller_id" id="seller_id" required>
                    <option value="">انتخاب کنید...</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}">{{ $seller->seller_code }} - {{ $seller->first_name }} {{ $seller->last_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                @include('sales.partials.product_list')
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                @include('sales.partials.invoice_items_table')
            </div>
        </div>

        <input type="hidden" name="products_input" id="products_input">

        <div class="row align-items-center mt-4">
            <div class="col-md-9 text-end">
                <div>تعداد کل: <span id="total_count">۰</span></div>
                <div>مبلغ کل: <span id="total_amount">۰ ریال</span></div>
            </div>
            <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm"><i class="fa fa-check ms-2"></i> ثبت فاکتور فروش</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/sales-invoice-items.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/persian-date.js') }}"></script>
    <script src="{{ asset('js/persian-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/sales-invoice.js') }}"></script>
    <script>
    // ذخیره اقلام به input مخفی و مدیریت شماره فاکتور دستی/خودکار
    let isAutoInvoiceNumber = true;
    document.addEventListener('DOMContentLoaded', function() {
        // سوییچ شماره فاکتور
        const switchInput = document.getElementById('invoiceNumberSwitch');
        const invoiceInput = document.getElementById('invoice_number');
        switchInput.addEventListener('change', function() {
            if (this.checked) {
                invoiceInput.setAttribute('readonly', 'readonly');
                // درخواست شماره جدید از سرور
                fetch('/sales/next-invoice-number')
                    .then(response => response.json())
                    .then(data => {
                        invoiceInput.value = data.number;
                    });
                isAutoInvoiceNumber = true;
            } else {
                invoiceInput.removeAttribute('readonly');
                isAutoInvoiceNumber = false;
                invoiceInput.focus();
            }
        });

        document.getElementById('sales-invoice-form').addEventListener('submit', function(e) {
            let errors = [];
            if(!document.getElementById('customer_id').value) errors.push('مشتری را انتخاب کنید.');
            if(!document.getElementById('seller_id').value) errors.push('فروشنده را انتخاب کنید.');
            if(!document.getElementById('currency_id').value) errors.push('واحد پول را انتخاب کنید.');
            let hasItems = (typeof invoiceItems !== 'undefined' && invoiceItems.length > 0);
            if(!hasItems) errors.push('حداقل یک محصول یا خدمت به فاکتور اضافه کنید.');
            if(!invoiceInput.value) errors.push('شماره فاکتور را وارد کنید.');
            if(errors.length) {
                e.preventDefault();
                Swal.fire({icon:'error', html: errors.join('<br>')});
            } else {
                document.getElementById('products_input').value = JSON.stringify(invoiceItems);
            }
        });
    });
    </script>
@endsection
