@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/invoice-create.css') }}" rel="stylesheet" />
@endpush

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
                <h3>جزئیات فاکتور شماره {{ $invoice->invoice_number }}</h3>
                <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-secondary">چاپ فاکتور</a>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <p><strong>مشتری:</strong>
                        @if($invoice->customer)
                            {{ $invoice->customer->company_name ?: ($invoice->customer->first_name . ' ' . $invoice->customer->last_name) }}
                        @else
                            -
                        @endif
                    </p>
                    <p><strong>تاریخ صدور:</strong> {{ jdate($invoice->date)->format('Y/m/d') }}</p>
                    <p><strong>تاریخ سررسید:</strong> {{ jdate($invoice->due_date)->format('Y/m/d') }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>واحد پول:</strong>
                        @if($invoice->currency)
                            {{ $invoice->currency->title }} {{ $invoice->currency->symbol ? '(' . $invoice->currency->symbol . ')' : '' }}
                        @else
                            -
                        @endif
                    </p>
                    <p><strong>فروشنده:</strong>
                        @if($invoice->seller)
                            {{ $invoice->seller->company_name ?: ($invoice->seller->first_name . ' ' . $invoice->seller->last_name) }}
                        @else
                            -
                        @endif
                    </p>
                    <p><strong>ارجاع:</strong> {{ $invoice->reference ?? '-' }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>شماره فاکتور:</strong> {{ $invoice->invoice_number }}</p>
                    <p><strong>وضعیت:</strong>
                        @if($invoice->status == 'paid')
                            <span class="badge bg-success">پرداخت شده</span>
                        @else
                            <span class="badge bg-warning text-dark">در انتظار پرداخت</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="table-responsive my-4">
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

            <div class="row mb-3">
                <div class="col-md-4">
                    <p><strong>تخفیف:</strong> {{ number_format($invoice->discount) }} ریال</p>
                </div>
                <div class="col-md-4">
                    <p><strong>مالیات:</strong> {{ $invoice->tax }}%</p>
                </div>
                <div class="col-md-4 text-end">
                    <h5>جمع کل: <span class="fw-bold">{{ number_format($invoice->total_amount) }} ریال</span></h5>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('invoices.index') }}" class="btn btn-link">بازگشت به لیست فاکتورها</a>
                <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning">ویرایش فاکتور</a>
            </div>
        </div>
    </div>
</div>
@endsection
