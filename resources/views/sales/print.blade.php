<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاکتور {{ $sale->invoice_number }}</title>
    <style>
        @font-face {
            font-family: 'Anjoman';
            src: url('{{ asset('fonts/Anjoman.ttf') }}') format('truetype');
        }

        * {
            font-family: 'Anjoman', tahoma, arial;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }

        .page {
            background: white;
            padding: 20px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 900;
        }

        .header h2 {
            font-size: 47px;
            font-weight: 900;
            margin-bottom: 20px;
        }

        .logo {
            width: 120px;
            margin: 10px auto;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            font-size: 10px;
        }

        .meta-info .right {
            text-align: right;
        }

        .meta-info .left {
            text-align: left;
        }

        .customer-info {
            border: 3px solid #000;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
        }

        .customer-info h3 {
            font-size: 10px;
            margin-bottom: 10px;
        }

        .customer-info p {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .products-table th {
            background-color: #83fee0;
            padding: 10px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        .products-table td {
            padding: 10px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            border: 3px solid #000;
        }

        .products-table .product-name {
            text-align: right;
            font-size: 11px;
            font-weight: normal;
        }

        .totals {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .totals .right {
            width: 60%;
        }

        .totals .left {
            width: 35%;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
        }

        .about-us {
            margin-top: 40px;
            text-align: right;
        }

        .about-us h3 {
            font-size: 11px;
            margin-bottom: 10px;
            color: #000;
        }

        .about-us p {
            font-size: 9px;
            line-height: 1.5;
            margin-bottom: 5px;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            width: calc(100% - 40px);
            text-align: center;
        }

        .footer p {
            margin-bottom: 5px;
            font-size: 11px;
        }

        @media print {
            body {
                width: 210mm;
                height: 297mm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1>مرکز خدمات (کامپیوتر،کافی نت، موبایل)</h1>
            <img src="{{ asset('images/logo.png') }}" alt="لوگو پارس تک" class="logo">
            <h2>پــارس تـکـ</h2>
            <h1>فاکتور فروش</h1>
        </div>

        <div class="meta-info">
            <div class="right">
                <p>
                    <span>شماره:</span>
                    <span>{{ $sale->invoice_number }}</span>
                </p>
                <p>
                    <span>تاریخ:</span>
                    <span>{{ jdate($sale->created_at)->format('Y/m/d') }}</span>
                </p>
            </div>
        </div>

        <div class="customer-info">
            <h3>خریدار/شرکت/سازمان</h3>
            <p>{{ $sale->customer->full_name }}</p>
            @if($sale->customer->company_name)
                <p>{{ $sale->customer->company_name }}</p>
            @endif
        </div>

        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 40%;">مـحـصـولات</th>
                    <th style="width: 15%;">قیمت (ریال)</th>
                    <th style="width: 15%;">تعداد</th>
                    <th style="width: 30%;">جمع کل (ریال)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td class="product-name">{{ $item->product->title }}</td>
                    <td>{{ number_format($item->unit_price) }}</td>
                    <td>{{ number_format($item->quantity) }}</td>
                    <td>{{ number_format($item->total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="right">
                <div class="about-us">
                    <h3>درباره ما</h3>
                    <p>مرکز خدمات پارس تک ارائه دهنده خدمات تخصصی کامپیوتر، چاپ و تکثیر، شارژ و فروش کارتریج، طراحی و چاپ بنر، تابلوهای تبلیغاتی و LED</p>
                </div>
            </div>
            <div class="left">
                <div class="total-row">
                    <span>جمع کل (ریال):</span>
                    <span>{{ number_format($sale->total_price) }}</span>
                </div>
                <div class="total-row">
                    <span>مالیات:</span>
                    <span>{{ $sale->tax_percent ?? '0.0' }}٪</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>www.tepars.ir</p>
            <p>tepars.ir@gmail.com</p>
            <p>۰۹۳۸۰۰۷۲۰۱۹</p>
        </div>
    </div>

    <div class="no-print text-center mt-4">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            چاپ فاکتور
        </button>
    </div>
</body>
</html>
