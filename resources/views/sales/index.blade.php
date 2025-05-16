@extends('layouts.app')

@section('title', 'لیست فاکتورهای فروش')

@section('content')
<div class="container">
    <h1>لیست فاکتورهای فروش</h1>
    <a href="{{ route('sales.create') }}" class="btn btn-primary mb-3">ثبت فروش جدید</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>شماره فاکتور</th>
                <th>مشتری</th>
                <th>فروشنده</th>
                <th>تاریخ صدور</th>
                <th>مبلغ کل</th>
                <th>اقلام</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->invoice_number }}</td>
                <td>
                    <a href="{{ route('persons.show', $sale->person_id) }}">
                        {{ $sale->person->first_name }} {{ $sale->person->last_name }}
                    </a>
                </td>
                <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($sale->issued_at)->format('Y/m/d') }}</td>
                <td>{{ number_format($sale->total_price) }} ریال</td>
                <td>
                    <ul>
                        @foreach($sale->items as $item)
                            <li>{{ $item->product ? $item->product->name : '-' }} × {{ $item->quantity }} ({{ number_format($item->total) }} ریال)</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $sales->links() }}
</div>
@endsection
