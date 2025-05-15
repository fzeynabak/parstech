@extends('layouts.app')

@section('title', 'صدور فاکتور فروش جدید')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
@endpush

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
    </div>
@endif

<form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
    @csrf
    <div class="row mb-2">
        <div class="col-md-4">
            <label>تاریخ صدور</label>
            <input type="text" name="date" id="date" class="form-control datepicker" required>
        </div>
        <div class="col-md-4">
            <label>تاریخ سررسید</label>
            <input type="text" name="due_date" id="due_date" class="form-control datepicker" required>
        </div>
        <div class="col-md-4">
            <label>شماره فاکتور</label>
            <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number') }}" required>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-md-4">
            <label>مشتری</label>
            <select name="customer_id" id="customer_select" class="form-control select2" required></select>
        </div>
        <div class="col-md-4">
            <label>فروشنده</label>
            <select name="seller_id" id="seller_select" class="form-control select2" required></select>
        </div>
        <div class="col-md-4">
            <label>واحد پول</label>
            <select name="currency_id" class="form-control select2" required>
                @foreach($currencies as $cur)
                    <option value="{{ $cur->id }}">{{ $cur->title }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mb-2">
        <label>ارجاع</label>
        <input type="text" name="reference" class="form-control">
    </div>

    <hr>
    <h5>افزودن کالاها</h5>
    <div class="row mb-2">
        <div class="col-md-6">
            <input type="text" id="product_search" class="form-control" placeholder="نام یا کد کالا...">
            <div id="product_suggestions" style="background:#fff;z-index:10;position:absolute;width:100%"></div>
        </div>
        <div class="col-md-2">
            <input type="number" id="product_qty" class="form-control" placeholder="تعداد" min="1">
        </div>
        <div class="col-md-2">
            <input type="number" id="product_price" class="form-control" placeholder="قیمت واحد">
        </div>
        <div class="col-md-2">
            <button type="button" id="add_product" class="btn btn-success w-100">افزودن</button>
        </div>
    </div>
    <table class="table table-bordered" id="products_table">
        <thead>
            <tr>
                <th>کد</th><th>نام کالا</th><th>تعداد</th><th>قیمت</th><th>جمع</th><th>حذف</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="row mb-2">
        <div class="col-md-3">
            <label>درصد تخفیف</label>
            <input type="number" name="discount_percent" class="form-control" value="0">
        </div>
        <div class="col-md-3">
            <label>مبلغ تخفیف</label>
            <input type="number" name="discount_amount" class="form-control" value="0">
        </div>
        <div class="col-md-3">
            <label>درصد مالیات</label>
            <input type="number" name="tax_percent" class="form-control" value="0">
        </div>
    </div>
    <div class="mb-2">
        <button class="btn btn-primary" type="submit">ثبت فاکتور</button>
    </div>
    <input type="hidden" name="products" id="products_input">
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
<script src="/js/currency-management.js"></script>
<script>
$(function() {
    // Select2 مشتری
    $('#customer_select').select2({
        ajax: {
            url: '/api/persons/search?type=customer',
            dataType: 'json',
            processResults: data => ({ results: data }),
            delay: 250
        },
        placeholder: 'جستجوی مشتری...'
    });
    // Select2 فروشنده
    $('#seller_select').select2({
        ajax: {
            url: '/sellers/list',
            dataType: 'json',
            processResults: data => ({ results: data }),
            delay: 250
        },
        placeholder: 'جستجوی فروشنده...'
    });
    $('.select2').select2({dir: 'rtl'});
    $('.datepicker').persianDatepicker({ format: 'YYYY/MM/DD', autoClose: true });

    // محصولات انتخاب شده
    let products = [];
    // جستجوی محصول (نمونه ساده AJAX)
    $('#product_search').on('keyup', function () {
        let q = $(this).val();
        if (q.length < 2) { $('#product_suggestions').hide(); return; }
        $.getJSON('/api/products/search', {q}, function(data){
            let html = '';
            data.forEach(item => {
                html += `<div class="product-suggestion" data-id="${item.id}" data-name="${item.name}" data-price="${item.sell_price}">${item.name} (${item.code})</div>`;
            });
            $('#product_suggestions').html(html).show();
        });
    });
    // انتخاب محصول از لیست
    $(document).on('click', '.product-suggestion', function () {
        $('#product_search').val($(this).data('name'));
        $('#product_price').val($(this).data('price'));
        $('#product_suggestions').hide();
        $('#product_search').data('id', $(this).data('id'));
    });
    // افزودن محصول به جدول
    $('#add_product').on('click', function () {
        let id = $('#product_search').data('id');
        let name = $('#product_search').val();
        let qty = parseInt($('#product_qty').val());
        let price = parseFloat($('#product_price').val());
        if (id && qty > 0 && price > 0) {
            products.push({product_id:id, name, quantity:qty, unit_price:price, total:qty*price});
            renderProducts();
            $('#product_search').val('').removeData('id');
            $('#product_qty').val('');
            $('#product_price').val('');
        }
    });
    // حذف ردیف
    $(document).on('click', '.remove-product', function(){
        let index = $(this).data('index');
        products.splice(index,1);
        renderProducts();
    });
    function renderProducts() {
        let html = '';
        products.forEach((item,i) => {
            html += `<tr>
                <td>${item.product_id}</td>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>${item.unit_price}</td>
                <td>${item.total}</td>
                <td><button type="button" class="btn btn-danger btn-sm remove-product" data-index="${i}">حذف</button></td>
            </tr>`;
        });
        $('#products_table tbody').html(html);
        $('#products_input').val(JSON.stringify(products));
    }
});
</script>
@endpush
