@php
    $company = config('app.company_name', 'شرکت نمونه');
    $company_address = config('app.company_address', 'آدرس شرکت');
    $company_phone = config('app.company_phone', 'شماره تماس');
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>چاپ فاکتور شماره {{ $invoice->invoice_number }}</title>
    <link href="{{ asset('css/invoice-create.css') }}" rel="stylesheet" />
    <style>
        body { background: #fff !important; }
        .invoice-box {
            max-width: 900px;
            margin: 30px auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            font-size: 16px;
            color: #333;
            background: #fff;
        }
        .rtl {
            direction: rtl;
            text-align: right;
        }
        .invoice-table th, .invoice-table td { padding: 10px; }
        .text-left { text-align: left !important; }
        .no-print { display: none !important; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="rtl">
<div class="invoice-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $company }}</h2>
        <button class="btn btn-primary no-print" onclick="window.print()">چاپ فاکتور</button>
    </div>
    <div class="mb-3">
        <strong>آدرس:</strong> {{ $company_address }}<br>
        <strong>تلفن:</strong> {{ $company_phone }}
    </div>
    <hr>
    <div class="row mb-4">
        <div class="col-md-6">
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
        <div class="col-md-6">
            <p><strong>شماره فاکتور:</strong> {{ $invoice->invoice_number }}</p>
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
        </div>
    </div>
    <table class="table table-bordered table-hover invoice-table" width="100%">
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
    <div class="row mt-4">
        <div class="col-md-6">
            <p><strong>تخفیف:</strong> {{ number_format($invoice->discount) }} ریال</p>
            <p><strong>مالیات:</strong> {{ $invoice->tax }}%</p>
        </div>
        <div class="col-md-6 text-left">
            <h5>جمع کل: <span class="fw-bold">{{ number_format($invoice->total_amount) }} ریال</span></h5>
        </div>
    </div>
    <div class="text-center mt-5">
        <small>این فاکتور توسط سیستم به صورت خودکار صادر شده است.</small>
    </div>
</div>
</body>
</html>
