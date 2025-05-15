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
            <div class="alert alert-danger">
                <h4>حذف فاکتور</h4>
                <p>آیا مطمئن هستید که می‌خواهید فاکتور شماره <strong>{{ $invoice->invoice_number }}</strong> را حذف کنید؟ این عملیات غیرقابل بازگشت است.</p>
            </div>
            <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="d-flex justify-content-between">
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">انصراف</a>
                    <button type="submit" class="btn btn-danger">حذف فاکتور</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
