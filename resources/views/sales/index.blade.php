@extends('layouts.app')

@section('title', 'لیست فاکتورهای فروش')

@section('content')
<div class="container py-3">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h1 class="fw-bold mb-0">
            <i class="fa-solid fa-file-invoice-dollar text-primary"></i>
            لیست فاکتورهای فروش
        </h1>
        <a href="{{ route('sales.create') }}" class="btn btn-success shadow-sm">
            <i class="fa fa-plus"></i> ثبت فروش جدید
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- فرم جستجو و فیلتر --}}
    <form method="get" class="row g-2 align-items-end mb-4 bg-light border rounded-3 p-3">
        <div class="col-md-3">
            <label class="form-label">نام مشتری</label>
            <input type="text" name="customer" class="form-control" placeholder="جستجو نام یا نام خانوادگی" value="{{ request('customer') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">شماره فاکتور</label>
            <input type="text" name="invoice_number" class="form-control" placeholder="مثلاً 1001" value="{{ request('invoice_number') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">از تاریخ</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">تا تاریخ</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">حداقل مبلغ</label>
            <input type="number" name="min_amount" class="form-control" placeholder="ریال" value="{{ request('min_amount') }}">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-outline-primary w-100">
                <i class="fa fa-search"></i>
            </button>
        </div>
    </form>

    {{-- لیست فروش‌ها --}}
    <div class="table-responsive mb-5">
        <table class="table table-bordered align-middle shadow-sm mb-0">
            <thead class="table-primary">
                <tr>
                    <th>شماره فاکتور</th>
                    <th style="min-width: 200px;">مشتری</th>
                    <th>اطلاعات مشتری</th>
                    <th>فروشنده</th>
                    <th>تاریخ صدور</th>
                    <th>مبلغ کل</th>
                    <th>اقلام</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td class="fw-bold">{{ $sale->invoice_number }}</td>
                    <td>
                        @if($sale->customer)
                        <div class="d-flex align-items-center gap-3">
                            {{-- عکس مشتری (اگر نبود عکس پیشفرض) --}}
                            <a href="{{ route('persons.show', $sale->customer->id) }}" class="d-inline-block">
                                <img src="{{ $sale->customer->photo ? asset('storage/' . $sale->customer->photo) : asset('images/avatar-default.png') }}"
                                     alt="عکس مشتری"
                                     class="rounded-circle border shadow-sm" width="48" height="48"
                                     style="object-fit: cover;">
                            </a>
                            <div>
                                <a href="{{ route('persons.show', $sale->customer->id) }}" class="fw-bold text-decoration-none text-dark">
                                    {{ $sale->customer->first_name }} {{ $sale->customer->last_name }}
                                </a>
                                <div class="small text-muted">
                                    <i class="fa fa-id-card"></i>
                                    کد: {{ $sale->customer->accounting_code ?? '-' }}
                                </div>
                            </div>
                        </div>
                        @else
                            <span class="text-danger">نامشخص</span>
                        @endif
                    </td>
                    <td>
                        @if($sale->customer)
                        <div class="mb-2 border-bottom pb-2">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <th>موبایل</th>
                                    <td>{{ $sale->customer->mobile ?? $sale->customer->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>مجموع خرید</th>
                                    <td class="fw-bold text-success">
                                        {{ number_format($sale->customer->sales()->sum('total_price')) }} ریال
                                    </td>
                                </tr>
                                <tr>
                                    <th>تعداد خرید</th>
                                    <td class="fw-bold">
                                        {{ $sale->customer->sales()->count() }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        @else
                            <span class="text-danger">نامشخص</span>
                        @endif
                    </td>
                    <td>
                        @if($sale->seller)
                            {{ $sale->seller->first_name ?? '' }} {{ $sale->seller->last_name ?? '' }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        {{ \Morilog\Jalali\Jalalian::fromDateTime($sale->issued_at)->format('Y/m/d H:i') }}
                    </td>
                    <td class="fw-bold text-success">
                        {{ number_format($sale->total_price) }} <span class="text-muted small">ریال</span>
                    </td>
                    <td>
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>کالا</th>
                                    <th>تعداد</th>
                                    <th>جمع</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product?->name ?? '-' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->total) }} ریال</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted text-center">-</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-danger">فاکتوری ثبت نشده است.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $sales->withQueryString()->links() }}
    </div>
</div>
@endsection
