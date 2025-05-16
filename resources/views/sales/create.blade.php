@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/persian-datepicker.min.css') }}">
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
        .form-switch input[type=checkbox] { display: none; }
        .form-switch input[type=checkbox]:checked + .slider { background: #4caf50; }
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
        .form-switch input[type=checkbox]:checked + .slider:before { left: 21px; }
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
                <input type="text" class="form-control" name="reference" id="reference" value="{{ old('reference') }}">
            </div>
            <div class="col-md-3">
                <label>تاریخ صدور</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="issued_at_jalali" id="issued_at_jalali" value="{{ old('issued_at_jalali') }}" readonly autocomplete="off">
                </div>
            </div>
            <div class="col-md-2">
                <label>واحد پول</label>
                <select class="form-select" name="currency_id" id="currency_id" required>
                    <option value="">انتخاب کنید...</option>
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                            {{ $currency->name }} - {{ $currency->code }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <label>مشتری <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="customer_search" placeholder="انتخاب کنید..." value="{{ old('customer_name') }}">
                    <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}" required>
                    <button type="button" class="btn btn-outline-success" id="addCustomerBtn"><i class="fa fa-plus"></i></button>
                </div>
                <div class="dropdown-menu" id="customer-search-results" style="width:100%"></div>
            </div>
            <div class="col-md-3">
                <label>عنوان</label>
                <input type="text" class="form-control" name="title" id="invoice_title" placeholder="عنوان..." value="{{ old('title') }}">
            </div>
            <div class="col-md-3">
                <label>فروشنده <span class="text-danger">*</span></label>
                <select class="form-select" name="seller_id" id="seller_id" required>
                    <option value="">انتخاب کنید...</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}" {{ old('seller_id') == $seller->id ? 'selected' : '' }}>
                            {{ $seller->seller_code }} - {{ $seller->first_name }} {{ $seller->last_name }}
                        </option>
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

        <input type="hidden" name="products_input" id="products_input" value="{{ old('products_input') }}">

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
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/persian-date.min.js') }}"></script>
    <script src="{{ asset('js/persian-datepicker.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/sales-invoice.js') }}"></script>
    <script>
    $(function() {
        // مقداردهی فقط برای نمایش (سمت سرور ثبت نمی‌شود!)
        if (typeof persianDate !== "undefined") {
            var now = new persianDate();
            var jalali = now.format('YYYY/MM/DD');
            $('#issued_at_jalali').val(jalali);
        } else {
            var date = new Date();
            var pad = n => n < 10 ? '0'+n : n;
            var miladi = date.getFullYear() + '-' + pad(date.getMonth()+1) + '-' + pad(date.getDate());
            $('#issued_at_jalali').val(miladi);
        }
        $('#issued_at_jalali').prop('readonly', true).css('background', '#eee').css('cursor', 'not-allowed');
    });
    </script>
@endsection
