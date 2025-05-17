<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاکتور {{ $sale->invoice_number }}</title>
    <style>
        @font-face {
            font-family: 'IranSans';
            src: url('{{ public_path('fonts/IRANSans.ttf') }}') format('truetype');
        }

        body {
            font-family: 'IranSans', sans-serif;
            background-color: white;
            padding: 20px;
            margin: 0;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #00bcd4;
            padding-bottom: 20px;
        }

        .company-info {
            text-align: right;
        }

        .company-logo {
            width: 150px;
        }

        .invoice-title {
            text-align: center;
            font-size: 24px;
            color: #00bcd4;
            margin-bottom: 20px;
        }

        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .meta-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background: #00bcd4;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .items-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .payment-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .payment-details {
            margin-top: 20px;
        }

        .payment-method {
            margin-bottom: 10px;
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .total-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #00bcd4;
        }

        .qr-code {
            text-align: left;
            margin-top: 20px;
        }

        @media print {
            body {
                background: white;
            }
            .invoice-container {
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- هدر فاکتور -->
        <div class="invoice-header">
            <div class="company-info">
                <img src="{{ public_path('images/logo.png') }}" alt="لوگو" class="company-logo">
                <h2>مرکز خدمات (کامپیوتر، کافی نت، موبایل)</h2>
            </div>
            <div class="invoice-number">
                <h3>فاکتور فروش</h3>
                <p>شماره: {{ $sale->invoice_number }}</p>
                <p>تاریخ: {{ verta($sale->created_at)->format('Y/m/d') }}</p>
            </div>
        </div>

        <!-- اطلاعات مشتری و فروشنده -->
        <div class="invoice-meta">
            <div class="meta-item">
                <h4>مشخصات خریدار</h4>
                <p>{{ $sale->customer->full_name }}</p>
                @if($sale->customer->mobile)
                <p>شماره تماس: {{ $sale->customer->mobile }}</p>
                @endif
                @if($sale->customer->address)
                <p>آدرس: {{ $sale->customer->address }}</p>
                @endif
            </div>
            <div class="meta-item">
                <h4>مشخصات فروشنده</h4>
                <p>{{ $sale->seller->full_name }}</p>
                <p>کد فروشنده: {{ $sale->seller->seller_code }}</p>
            </div>
        </div>

        <!-- جدول اقلام -->
        <table class="items-table">
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

        <!-- اطلاعات پرداخت -->
        <div class="payment-info">
            <h4>جزئیات پرداخت</h4>
            <div class="payment-details">
                @if($sale->cash_amount > 0)
                <div class="payment-method">
                    <strong>پرداخت نقدی:</strong>
                    <p>مبلغ: {{ number_format($sale->cash_amount) }} تومان</p>
                    @if($sale->cash_reference)
                    <p>شماره رسید: {{ $sale->cash_reference }}</p>
                    @endif
                    <p>تاریخ: {{ verta($sale->cash_paid_at)->format('Y/m/d') }}</p>
                </div>
                @endif

                @if($sale->card_amount > 0)
                <div class="payment-method">
                    <strong>کارت به کارت:</strong>
                    <p>مبلغ: {{ number_format($sale->card_amount) }} تومان</p>
                    @if($sale->card_number)
                    <p>شماره کارت: {{ $sale->card_number }}</p>
                    @endif
                    @if($sale->card_reference)
                    <p>شماره پیگیری: {{ $sale->card_reference }}</p>
                    @endif
                    <p>تاریخ: {{ verta($sale->card_paid_at)->format('Y/m/d') }}</p>
                </div>
                @endif

                @if($sale->pos_amount > 0)
                <div class="payment-method">
                    <strong>کارتخوان:</strong>
                    <p>مبلغ: {{ number_format($sale->pos_amount) }} تومان</p>
                    @if($sale->pos_terminal)
                    <p>شماره پایانه: {{ $sale->pos_terminal }}</p>
                    @endif
                    @if($sale->pos_reference)
                    <p>شماره پیگیری: {{ $sale->pos_reference }}</p>
                    @endif
                    <p>تاریخ: {{ verta($sale->pos_paid_at)->format('Y/m/d') }}</p>
                </div>
                @endif

                @if($sale->cheque_amount > 0)
                <div class="payment-method">
                    <strong>چک:</strong>
                    <p>مبلغ: {{ number_format($sale->cheque_amount) }} تومان</p>
                    @if($sale->cheque_number)
                    <p>شماره چک: {{ $sale->cheque_number }}</p>
                    @endif
                    @if($sale->cheque_bank)
                    <p>بانک: {{ $sale->cheque_bank }}</p>
                    @endif
                    <p>تاریخ سررسید: {{ verta($sale->cheque_due_date)->format('Y/m/d') }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- جمع کل -->
        <div class="total-section">
            <div>
                <p>جمع کل: {{ number_format($sale->total_price) }} تومان</p>
                <p>تخفیف: {{ number_format($sale->discount) }} تومان</p>
                <p>مالیات: {{ number_format($sale->tax) }} تومان</p>
                <p>مبلغ قابل پرداخت: {{ number_format($sale->final_amount) }} تومان</p>
                <p>مبلغ پرداخت شده: {{ number_format($sale->paid_amount) }} تومان</p>
                <p>مانده حساب: {{ number_format($sale->remaining_amount) }} تومان</p>
            </div>
            <div class="qr-code">
                {!! QrCode::size(100)->generate(route('sales.show', $sale)) !!}
            </div>
        </div>

        <!-- پاورقی -->
        <div style="margin-top: 50px; text-align: center; color: #666;">
            <p>{{ config('app.name') }}</p>
            <p>{{ config('app.address') }}</p>
            <p>تلفن: {{ config('app.phone') }}</p>
            <p>وبسایت: {{ config('app.url') }}</p>
        </div>
    </div>

    <!-- دکمه پرینت -->
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #00bcd4; color: white; border: none; border-radius: 5px; cursor: pointer;">
            چاپ فاکتور
        </button>
    </div>
</body>
</html>
