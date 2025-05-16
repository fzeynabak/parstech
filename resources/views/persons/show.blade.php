@extends('layouts.sales')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/persons_show.css') }}">
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold">اطلاعات شخص</h4>
        <ul class="list-group">
            <li class="list-group-item">نام: {{ $person->first_name }} {{ $person->last_name }} {{ $person->company_name }}</li>
            <li class="list-group-item">کد حسابداری: {{ $person->accounting_code }}</li>
            <li class="list-group-item">نوع: {{ $person->type }}</li>
            <li class="list-group-item">شماره تلفن: {{ $person->phone ?? $person->mobile }}</li>
        </ul>
    </div>
    <div class="col-md-6">
        <form action="{{ route('persons.updatePercent', $person) }}" method="POST" class="d-flex flex-column align-items-start">
            @csrf
            <label for="purchase_percent" class="fw-bold mb-2">درصد اختصاص یافته</label>
            <div class="input-group mb-2">
                <input type="number" step="0.01" min="0" max="100" name="purchase_percent" id="purchase_percent" class="form-control" value="{{ $person->purchase_percent }}">
                <span class="input-group-text">٪</span>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">ذخیره درصد</button>
        </form>
        @if(session('success'))
            <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif
    </div>
</div>

<div class="mb-4">
    <h5>جمع کل خریدها: <span class="badge bg-success">{{ number_format($totalAmount) }}</span> ریال</h5>
</div>

<div class="mb-3">
    <form id="searchForm" method="get" class="row g-2 align-items-end">
        <div class="col-auto">
            <input type="text" name="search" class="form-control" placeholder="جستجو در فاکتور، رفرنس و ..." value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-auto">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-primary">جستجو</button>
        </div>
    </form>
</div>

<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>شناسه خرید</th>
            <th>شماره فاکتور</th>
            <th>مبلغ کل</th>
            <th>تاریخ خرید</th>
        </tr>
    </thead>
    <tbody>
        @forelse($purchases as $purchase)
            <tr>
                <td>{{ $purchase->id }}</td>
                <td>{{ $purchase->invoice->reference ?? '-' }}</td>
                <td>{{ number_format($purchase->total_amount) }}</td>
                <td>{{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($purchase->purchase_date))->format('Y/m/d H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">خریدی ثبت نشده است.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<div>
    {{ $purchases->withQueryString()->links() }}
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/persons_show.js') }}"></script>
@endsection
