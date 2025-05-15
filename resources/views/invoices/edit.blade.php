@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('css/invoice-create.css') }}" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/invoice-create.js') }}"></script>
@endpush

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="page-wrapper">
    <div class="sidebar">
        <h3>منو</h3>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}">داشبورد</a></li>
                <li><a href="{{ route('invoices.index') }}">لیست فاکتورها</a></li>
                <li><a href="{{ route('products.index') }}">محصولات</a></li>
                <li><a href="{{ route('persons.customers') }}">مشتریان</a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="container py-4">
            <div class="invoice-header">
                <h3>ویرایش فاکتور شماره {{ $invoice->invoice_number }}</h3>
            </div>

            <form id="invoiceForm" action="{{ route('invoices.update', $invoice->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="invoiceNumber" class="form-label required-field">شماره فاکتور</label>
                            <input type="text" id="invoiceNumber" name="invoiceNumber" class="form-control"
                                   value="{{ old('invoiceNumber', $invoice->invoice_number) }}" required>
                        </div>
                        @error('invoiceNumber')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- ارجاع -->
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="reference">ارجاع</label>
                            <input type="text" id="reference" name="reference"
                                   class="form-control" placeholder="شماره ارجاع را وارد کنید"
                                   value="{{ old('reference', $invoice->reference) }}">
                            @error('reference')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- تاریخ -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label required-field">تاریخ صدور فاکتور</label>
                            <input type="text" name="date" id="date" class="form-control datepicker"
                                value="{{ old('date', $invoice->date) }}" required autocomplete="off">
                        </div>
                    </div>

                    <!-- تاریخ سررسید -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label required-field">تاریخ سررسید</label>
                            <input type="text" name="dueDate" id="dueDate" class="form-control datepicker"
                                value="{{ old('dueDate', $invoice->due_date) }}" required autocomplete="off">
                        </div>
                    </div>
                </div>

                <!-- مشتری -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label required-field" for="customer_select">مشتری</label>
                            <select id="customer_select" name="customer_id" class="form-control select2" required>
                                <option value="">انتخاب مشتری...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->company_name ? $customer->company_name : ($customer->first_name . ' ' . $customer->last_name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- واحد پول -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="currency_id" class="form-label required-field">واحد پول</label>
                            <select name="currency_id" id="currency_id" class="form-control select2" required>
                                <option value="">انتخاب واحد پول ...</option>
                                @foreach($currencies as $cur)
                                    <option value="{{ $cur->id }}"
                                        {{ old('currency_id', $invoice->currency_id) == $cur->id ? 'selected' : '' }}>
                                        {{ $cur->title }} {{ $cur->symbol ? '(' . $cur->symbol . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- فروشنده -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="seller" class="form-label required-field">فروشنده</label>
                            <select id="seller" name="seller" class="form-control select2" required>
                                <option value="">انتخاب فروشنده...</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}"
                                        {{ old('seller', $invoice->seller_id) == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->company_name ? $seller->company_name : ($seller->first_name . ' ' . $seller->last_name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- جستجوی محصولات -->
                <div class="product-search-container">
                    <label for="productSearch" class="form-label">جستجوی محصولات</label>
                    <input type="text" id="productSearch" class="form-control"
                           placeholder="نام یا کد محصول را وارد کنید...">
                    <div id="productList" class="product-list mt-2" style="display: none;"></div>
                </div>

                <!-- جدول محصولات -->
                <div class="table-responsive">
                    <table class="selected-products-table">
                        <thead>
                            <tr>
                                <th>تصویر</th>
                                <th>کد کالا</th>
                                <th>نام محصول</th>
                                <th>دسته‌بندی</th>
                                <th>موجودی</th>
                                <th>قیمت (ریال)</th>
                                <th>تعداد</th>
                                <th>مجموع</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="selectedProducts">
                        @foreach($invoice->items as $item)
                            <tr data-product-id="{{ $item->product_id }}">
                                <td>
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="تصویر محصول" width="40">
                                    @endif
                                </td>
                                <td>{{ $item->product->code ?? '-' }}</td>
                                <td>{{ $item->product->name ?? '-' }}</td>
                                <td>{{ $item->product->category->title ?? '-' }}</td>
                                <td>{{ $item->product->stock ?? '-' }}</td>
                                <td>{{ number_format($item->unit_price) }}</td>
                                <td>
                                    <input type="number" name="quantities[{{ $item->product_id }}]" value="{{ $item->quantity }}" min="1" class="form-control form-control-sm">
                                </td>
                                <td>{{ number_format($item->total_price) }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-product">حذف</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row my-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount">تخفیف (ریال)</label>
                            <input type="number" name="discount" id="discount" class="form-control"
                                value="{{ old('discount', $invoice->discount) }}" min="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tax">مالیات (درصد)</label>
                            <input type="number" name="tax" id="tax" class="form-control"
                                value="{{ old('tax', $invoice->tax) }}" min="0" max="100">
                        </div>
                    </div>
                </div>

                <!-- نمایش جمع کل -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group text-end">
                            <label class="form-label">جمع کل (ریال):</label>
                            <span id="finalAmount" class="fw-bold">{{ number_format($invoice->total_amount) }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('invoices.index') }}" class="btn btn-link">بازگشت به لیست فاکتورها</a>
                    <button type="submit" class="btn btn-success">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
