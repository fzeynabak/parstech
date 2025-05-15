@extends('layouts.app')

@section('title', 'لیست فروش‌ها')

@section('content')
<div class="container">
    <h1>لیست فروش‌ها</h1>
    <a href="{{ route('sales.create') }}" class="btn btn-primary mb-3">ثبت فروش جدید</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>نام فروشنده</th>
                <th>محصول</th>
                <th>تعداد</th>
                <th>قیمت کل</th>
                <th>تاریخ فروش</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->seller->first_name }} {{ $sale->seller->last_name }}</td>
                <td>{{ $sale->product->name }}</td>
                <td>{{ $sale->quantity }}</td>
                <td>{{ $sale->total_price }} تومان</td>
                <td>{{ $sale->sale_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $sales->links() }}
</div>
@endsection
