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
                <h3>وضعیت فاکتور</h3>
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">بازگشت به لیست فاکتورها</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <p>
                        <strong>شماره فاکتور:</strong> {{ $invoice->invoice_number }}
                    </p>
                    <p>
                        <strong>مشتری:</strong>
                        @if($invoice->customer)
                            {{ $invoice->customer->company_name ?: ($invoice->customer->first_name . ' ' . $invoice->customer->last_name) }}
                        @else
                            -
                        @endif
                    </p>
                    <p>
                        <strong>وضعیت:</strong>
                        @if($invoice->status == 'paid')
                            <span class="badge bg-success">پرداخت شده</span>
                        @elseif($invoice->status == 'pending')
                            <span class="badge bg-warning text-dark">در انتظار پرداخت</span>
                        @else
                            <span class="badge bg-secondary">نامشخص</span>
                        @endif
                    </p>
                    <p>
                        <strong>تاریخ پرداخت:</strong>
                        @if($invoice->paid_at)
                            {{ jdate($invoice->paid_at)->format('Y/m/d') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
