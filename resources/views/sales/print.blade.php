<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاکتور {{ $sale->invoice_number }}</title>
    <link rel="stylesheet" href="{{ asset('css/invoice-print.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="invoice-container">
        <!-- Header Section -->
        <header class="invoice-header animate-fade-in">
            <div class="header-content">
                <div class="company-info">
                    <h1 class="company-name">پارس تک</h1>
                    <div class="company-details">
                        مرکز خدمات کامپیوتر، کافی نت و موبایل
                    </div>
                </div>
                <div class="invoice-meta">
                    <div class="invoice-number">شماره فاکتور: {{ $sale->invoice_number }}</div>
                    <div class="invoice-date">تاریخ: {{ jdate($sale->created_at)->format('Y/m/d') }}</div>
                </div>
            </div>
            <div id="invoice-qr" class="qr-code">

                {!! QrCode::size(100)->generate(url('/sales/'.$sale->id)) !!}
            </div>
        </header>

        <!-- Party Information -->
        <section class="party-info">
            <div class="info-box animate-fade-in">
                <h3>اطلاعات خریدار</h3>
                <ul class="info-list">
                    <li><strong>نام:</strong> {{ $sale->customer->full_name }}</li>
                    @if($sale->customer->mobile)
                    <li><strong>موبایل:</strong> {{ $sale->customer->mobile }}</li>
                    @endif
                    @if($sale->customer->email)
                    <li><strong>ایمیل:</strong> {{ $sale->customer->email }}</li>
                    @endif
                    @if($sale->customer->address)
                    <li><strong>آدرس:</strong> {{ $sale->customer->address }}</li>
                    @endif
                </ul>
            </div>
            <div class="info-box animate-fade-in">
                <h3>اطلاعات فروشنده</h3>
                <ul class="info-list">
                    <li><strong>نام فروشنده:</strong> {{ $sale->seller->full_name }}</li>
                    <li><strong>کد فروشنده:</strong> {{ $sale->seller->seller_code }}</li>
                    @if($sale->seller->mobile)
                    <li><strong>شماره تماس:</strong> {{ $sale->seller->mobile }}</li>
                    @endif
                </ul>
            </div>
        </section>

        <!-- Items Table -->
        <section class="items-section animate-fade-in">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>شرح کالا</th>
                        <th>تعداد</th>
                        <th>قیمت واحد (تومان)</th>
                        <th>تخفیف (تومان)</th>
                        <th>مالیات</th>
                        <th>قیمت کل (تومان)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $index => $item)
                    <tr class="item-row">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->title }}</td>
                        <td class="quantity">{{ number_format($item->quantity) }}</td>
                        <td class="price price-format">{{ number_format($item->unit_price) }}</td>
                        <td class="discount price-format">{{ number_format($item->discount) }}</td>
                        <td class="tax price-format">{{ number_format($item->tax) }}</td>
                        <td class="total price-format">{{ number_format($item->total) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <!-- Payment Information -->
        <section class="payment-section">
            <div class="payment-methods animate-fade-in">
                <h3 class="payment-title">روش‌های پرداخت</h3>
                <ul class="payment-list">
                    @if($sale->cash_amount > 0)
                    <li>
                        <span class="payment-label">پرداخت نقدی:</span>
                        <span class="payment-value price-format">{{ number_format($sale->cash_amount) }} تومان</span>
                    </li>
                    @endif

                    @if($sale->card_amount > 0)
                    <li>
                        <span class="payment-label">کارت به کارت:</span>
                        <span class="payment-value price-format">{{ number_format($sale->card_amount) }} تومان</span>
                        @if($sale->card_reference)
                        <small class="reference-number">شماره پیگیری: {{ $sale->card_reference }}</small>
                        @endif
                    </li>
                    @endif

                    @if($sale->pos_amount > 0)
                    <li>
                        <span class="payment-label">کارتخوان:</span>
                        <span class="payment-value price-format">{{ number_format($sale->pos_amount) }} تومان</span>
                        @if($sale->pos_reference)
                        <small class="reference-number">شماره پیگیری: {{ $sale->pos_reference }}</small>
                        @endif
                    </li>
                    @endif

                    @if($sale->cheque_amount > 0)
                    <li>
                        <span class="payment-label">چک:</span>
                        <span class="payment-value price-format">{{ number_format($sale->cheque_amount) }} تومان</span>
                        @if($sale->cheque_number)
                        <small class="reference-number">شماره چک: {{ $sale->cheque_number }}</small>
                        @endif
                    </li>
                    @endif
                </ul>
            </div>

            <div class="payment-summary animate-fade-in">
                <h3 class="payment-title">خلاصه مالی</h3>
                <ul class="payment-list">
                    <li>
                        <span class="payment-label">جمع کل:</span>
                        <span class="payment-value subtotal-amount price-format">{{ number_format($sale->total_price) }}</span>
                    </li>
                    <li>
                        <span class="payment-label">تخفیف:</span>
                        <span class="payment-value price-format">{{ number_format($sale->discount) }}</span>
                    </li>
                    <li>
                        <span class="payment-label">مالیات:</span>
                        <span class="payment-value tax-amount price-format">{{ number_format($sale->tax) }}</span>
                    </li>
                    <li class="total-row">
                        <span class="payment-label">مبلغ نهایی:</span>
                        <span class="payment-value total-amount price-format">{{ number_format($sale->final_amount) }}</span>
                    </li>
                    <li>
                        <span class="payment-label">پرداخت شده:</span>
                        <span class="payment-value price-format">{{ number_format($sale->paid_amount) }}</span>
                    </li>
                    <li>
                        <span class="payment-label">مانده حساب:</span>
                        <span class="payment-value price-format">{{ number_format($sale->remaining_amount) }}</span>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Footer -->
        <footer class="invoice-footer animate-fade-in">
            <div class="footer-content">
                <img src="{{ asset('images/logo.png') }}" alt="پارس تک" class="footer-logo">
                <div class="footer-contacts">
                    <span class="footer-contact">
                        <i class="fas fa-phone"></i>
                        09380074019
                    </span>
                    <span class="footer-contact">
                        <i class="fas fa-envelope"></i>
                        tepars.ir@gmail.com
                    </span>
                    <span class="footer-contact">
                        <i class="fas fa-globe"></i>
                        www.tepars.ir
                    </span>
                </div>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-telegram"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Print Button -->
    <div class="text-center mt-4 mb-4 no-print">
        <button class="print-button btn btn-primary">
            <i class="fas fa-print"></i>
            چاپ فاکتور
        </button>
        <button class="pdf-button btn btn-secondary">
            <i class="fas fa-file-pdf"></i>
            دانلود PDF
        </button>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="{{ asset('js/invoice-print.js') }}"></script>
</body>
</html>
