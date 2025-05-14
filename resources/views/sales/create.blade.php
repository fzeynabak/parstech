@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/persianDatepicker-melon.css') }}">
<link rel="stylesheet" href="{{ asset('css/sales-invoice.css') }}">
@endsection

@section('content')
<div class="sales-invoice-container">
    <div class="sales-invoice-header d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0"><i class="fa fa-file-invoice-dollar ms-2"></i> فاکتور فروش</h2>
        <div class="sales-invoice-actions">
            <button type="button" class="btn btn-light" title="ذخیره پیش‌نویس"><i class="fa fa-save"></i></button>
            <button type="button" class="btn btn-light" title="ایجاد جدید"><i class="fa fa-plus-square"></i></button>
        </div>
    </div>

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
                <label>پروژه</label>
                <select class="form-select" name="project_id" id="project_id">
                    <option value="">انتخاب کنید...</option>
                    @isset($projects)
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div class="col-md-2">
                <label>واحد پول</label>
                <select class="form-select" name="currency_id" id="currency_id">
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
                    <input type="hidden" name="customer_id" id="customer_id">
                    <button type="button" class="btn btn-outline-success" id="addCustomerBtn"><i class="fa fa-plus"></i></button>
                </div>
                <div class="dropdown-menu" id="customer-search-results" style="width:100%"></div>
            </div>
            <div class="col-md-3">
                <label>عنوان</label>
                <input type="text" class="form-control" name="title" id="invoice_title" placeholder="عنوان...">
            </div>
            <div class="col-md-3">
                <label>فروشنده</label>
                <select class="form-select" name="seller_id" id="seller_id">
                    <option value="">انتخاب کنید...</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}">{{ $seller->seller_code }} - {{ $seller->first_name }} {{ $seller->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>درصد مالیات</label>
                <input type="number" class="form-control" name="tax_percent" id="tax_percent" min="0" max="100" value="{{ old('tax_percent', 0) }}">
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

        <div class="row align-items-center mt-4">
            <div class="col-md-6 d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary" id="btnAddRow"><i class="fa fa-plus"></i> افزودن ردیف</button>
                <button type="button" class="btn btn-outline-success" id="btnAddCategory"><i class="fa fa-folder-plus"></i> دسته‌بندی</button>
                <button type="button" class="btn btn-outline-success" id="btnBarcode"><i class="fa fa-barcode"></i> بارکد</button>
                <button type="button" class="btn btn-outline-success" id="btnSerial"><i class="fa fa-hashtag"></i> شماره سریال</button>
                <button type="button" class="btn btn-outline-info" id="btnDiscount"><i class="fa fa-percent"></i> تخفیف</button>
                <button type="button" class="btn btn-outline-warning" id="btnTax"><i class="fa fa-money-bill"></i> مالیات</button>
                <button type="button" class="btn btn-outline-primary" id="btnNotes"><i class="fa fa-comment"></i> توضیحات</button>
                <button type="button" class="btn btn-outline-dark" id="btnShipping"><i class="fa fa-truck"></i> حمل و نقل</button>
            </div>
            <div class="col-md-3 text-end">
                <div>تعداد کل: <span id="total_count">۰</span></div>
                <div>مبلغ کل: <span id="total_amount">۰ ریال</span></div>
            </div>
            <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm"><i class="fa fa-check ms-2"></i> ثبت فاکتور فروش</button>
                <button type="button" class="btn btn-outline-dark" id="btnExportExcel"><i class="fa fa-file-excel"></i> اکسل</button>
                <button type="button" class="btn btn-outline-secondary" id="btnOtherActions"><i class="fa fa-ellipsis-h"></i> سایر</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/persian-date.js') }}"></script>
<script src="{{ asset('js/persian-datepicker.min.js') }}"></script>
<script src="{{ asset('js/sales-invoice.js') }}"></script>
<script src="{{ asset('js/sales-invoice-items.js') }}"></script>
@endsection
