@extends('layouts.app')

@section('content')
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>لیست آیتم‌های فاکتور شماره {{ $invoice->invoice_number }}</h3>
                <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-secondary">بازگشت به جزئیات فاکتور</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>نام محصول</th>
                            <th>کد کالا</th>
                            <th>تعداد</th>
                            <th>قیمت واحد (ریال)</th>
                            <th>مجموع (ریال)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? '-' }}</td>
                                <td>{{ $item->product->code ?? '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price) }}</td>
                                <td>{{ number_format($item->total_price) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('invoices.index') }}" class="btn btn-link">بازگشت به لیست فاکتورها</a>
                <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning">ویرایش فاکتور</a>
            </div>
        </div>
    </div>
</div>
@endsection
