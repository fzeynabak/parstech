@extends('layouts.app')

@section('title', 'چاپ فاکتور #' . $sale->invoice_number)

@section('head')
<style>
    @media print {
        body {
            background: white !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }
        .sidebar,
        .no-print,
        .btn,
        .modal,
        #sidebar {
            display: none !important;
        }
    }

    .invoice-print {
        background: white;
        padding: 20px;
        max-width: 800px;
        margin: 20px auto;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .invoice-header {
        text-align: center;
        padding-bottom: 20px;
        border-bottom: 2px solid #00bcd4;
        margin-bottom: 20px;
    }

    .invoice-header h1 {
        font-size: 24px;
        color: #333;
        margin: 10px 0;
    }

    .invoice-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .party-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        gap: 20px;
    }

    .info-box {
        flex: 1;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
    }

    .info-box h4 {
        color: #00bcd4;
        margin-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 5px;
    }

    .table {
        width: 100%;
        margin-bottom: 20px;
    }

    .table th {
        background: #00bcd4;
        color: white;
    }

    .table th,
    .table td {
        padding: 10px;
        text-align: center;
    }

    .payment-summary {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }

    .payment-methods,
    .payment-totals {
        flex: 1;
        max-width: 48%;
    }

    .payment-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .payment-box h4 {
        color: #00bcd4;
        margin-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 5px;
    }

    .footer {
        text-align: center;
        margin-top: 40px;
        padding-top: 20px;
        border-top: 2px solid #00bcd4;
        color: #666;
    }
</style>
@endsection

@section('content')
<div class="invoice-print">
    <!-- هدر فاکتور -->
    <div class="invoice-header">
        <img src="{{ asset('images/logo.png') }}" alt="لوگو" style="max-width: 200px;">
        <h1>مرکز خدمات (کامپیوتر،کافی نت، موبایل)</h1>
        <div>پارس تک</div>
    </div>

    <!-- اطلاعات فاکتور -->
    <div class="invoice-meta">
        <div>
            <strong>شماره فاکتور:</strong>
            <span>{{ $sale->invoice_number }}</span>
        </div>
        <div>
            <strong>تاریخ:</strong>
            <span>{{ jdate($sale->created_at)->format('Y/m/d') }}</span>
        </div>
    </div>

    <!-- اطلاعات طرفین -->
    <div class="party-info">
        <div class="info-box">
            <h4>مشخصات خریدار</h4>
            <p><strong>نام:</strong> {{ $sale->customer->full_name }}</p>
            @if($sale->customer->mobile)
            <p><strong>موبایل:</strong> {{ $sale->customer->mobile }}</p>
            @endif
            @if($sale->customer->address)
            <p><strong>آدرس:</strong> {{ $sale->customer->address }}</p>
            @endif
        </div>
        <div class="info-box">
            <h4>مشخصات فروشنده</h4>
            <p><strong>نام:</strong> {{ $sale->seller->full_name }}</p>
            <p><strong>کد فروشنده:</strong> {{ $sale->seller->seller_code }}</p>
        </div>
    </div>

    <!-- جدول اقلام -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>شرح کالا</th>
                <th>تعداد</th>
                <th>قیمت واحد</th>
                <th>تخفیف</th>
                <th>مالیات</th>
                <th>قیمت کل</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->title }}</td>
                <td>{{ number_format($item->quantity) }}</td>
                <td>{{ number_format($item->unit_price) }}</td>
                <td>{{ number_format($item->discount) }}</td>
                <td>{{ number_format($item->tax) }}</td>
                <td>{{ number_format($item->total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- خلاصه پرداخت -->
    <div class="payment-summary">
        <!-- روش‌های پرداخت -->
        <div class="payment-methods">
            <div class="payment-box">
                <h4>روش‌های پرداخت</h4>
                @if($sale->cash_amount > 0)
                <p>
                    <strong>نقدی:</strong>
                    <span>{{ number_format($sale->cash_amount) }} تومان</span>
                </p>
                @endif

                @if($sale->card_amount > 0)
                <p>
                    <strong>کارت به کارت:</strong>
                    <span>{{ number_format($sale->card_amount) }} تومان</span>
                    @if($sale->card_reference)
                    <br>
                    <small>شماره پیگیری: {{ $sale->card_reference }}</small>
                    @endif
                </p>
                @endif

                @if($sale->pos_amount > 0)
                <p>
                    <strong>کارتخوان:</strong>
                    <span>{{ number_format($sale->pos_amount) }} تومان</span>
                    @if($sale->pos_reference)
                    <br>
                    <small>شماره پیگیری: {{ $sale->pos_reference }}</small>
                    @endif
                </p>
                @endif

                @if($sale->cheque_amount > 0)
                <p>
                    <strong>چک:</strong>
                    <span>{{ number_format($sale->cheque_amount) }} تومان</span>
                    @if($sale->cheque_number)
                    <br>
                    <small>شماره چک: {{ $sale->cheque_number }}</small>
                    @endif
                </p>
                @endif
            </div>
        </div>

        <!-- جمع کل -->
        <div class="payment-totals">
            <div class="payment-box">
                <h4>خلاصه مالی</h4>
                <p>
                    <strong>جمع کل:</strong>
                    <span>{{ number_format($sale->total_price) }} تومان</span>
                </p>
                <p>
                    <strong>تخفیف:</strong>
                    <span>{{ number_format($sale->discount) }} تومان</span>
                </p>
                <p>
                    <strong>مالیات:</strong>
                    <span>{{ number_format($sale->tax) }} تومان</span>
                </p>
                <p>
                    <strong>مبلغ نهایی:</strong>
                    <span>{{ number_format($sale->final_amount) }} تومان</span>
                </p>
                <p>
                    <strong>پرداخت شده:</strong>
                    <span>{{ number_format($sale->paid_amount) }} تومان</span>
                </p>
                <p>
                    <strong>مانده:</strong>
                    <span>{{ number_format($sale->remaining_amount) }} تومان</span>
                </p>
            </div>
        </div>
    </div>

    <!-- پاورقی -->
    <div class="footer">
        <p>مرکز خدمات پارس تک</p>
        <p>تلفن: 09380074019</p>
        <p>www.tepars.ir | tepars.ir@gmail.com</p>
    </div>
</div>

<!-- دکمه پرینت -->
<div class="text-center mt-4 mb-4 no-print">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i>
        چاپ فاکتور
    </button>
</div>
@endsection

@section('scripts')
<script>
document.title = 'چاپ فاکتور #{{ $sale->invoice_number }}';
</script>
@endsection
