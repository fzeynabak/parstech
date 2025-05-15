@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('css/invoice-create.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="page-wrapper">
    <div class="sidebar">
        <h3>منو</h3>
        <nav>
            <ul>
                <li><a href="{{ route('dashboard') }}">داشبورد</a></li>
                <li><a href="{{ route('invoices.index') }}" class="active">لیست فاکتورها</a></li>
                <li><a href="{{ route('products.index') }}">محصولات</a></li>
                <li><a href="{{ route('persons.customers') }}">مشتریان</a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>لیست فاکتورها</h3>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">صدور فاکتور جدید</a>
            </div>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>شماره فاکتور</th>
                            <th>مشتری</th>
                            <th>تاریخ صدور</th>
                            <th>تاریخ سررسید</th>
                            <th>جمع کل (ریال)</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>
                                @if($invoice->customer)
                                    {{ $invoice->customer->company_name ?: ($invoice->customer->first_name . ' ' . $invoice->customer->last_name) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ jdate($invoice->date)->format('Y/m/d') }}</td>
                            <td>{{ jdate($invoice->due_date)->format('Y/m/d') }}</td>
                            <td>{{ number_format($invoice->total_amount) }}</td>
                            <td>
                                @if($invoice->status == 'paid')
                                    <span class="badge bg-success">پرداخت شده</span>
                                @else
                                    <span class="badge bg-warning text-dark">در انتظار پرداخت</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-info">نمایش</a>
                                <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-sm btn-warning">ویرایش</a>
                                <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('آیا مطمئن هستید که می‌خواهید این فاکتور را حذف کنید؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">هیچ فاکتوری ثبت نشده است.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
