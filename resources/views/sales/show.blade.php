@extends('layouts.app')

@section('title', 'جزئیات فاکتور #' . $sale->invoice_number)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sales-show.css') }}">
<style>
    .status-paid     { color: #16a34a; font-weight: bold; }
    .status-partial  { color: #f59e42; font-weight: bold; }
    .status-unpaid   { color: #dc2626; font-weight: bold; }
</style>
@endsection

@section('content')
<div class="sales-show-container animate-fade-in">

    <!-- هدر فاکتور -->
    <div class="invoice-header">
        <div class="invoice-header-content">
            <h1 class="invoice-title">جزئیات فاکتور</h1>
            <div class="invoice-meta">
                <div class="invoice-meta-item">
                    <i class="fas fa-file-invoice fa-lg text-primary"></i>
                    <span>شماره فاکتور:</span>
                    <strong class="farsi-number">{{ $sale->invoice_number }}</strong>
                </div>
                <div class="invoice-meta-item">
                    <i class="fas fa-calendar fa-lg text-info"></i>
                    <span>تاریخ صدور:</span>
                    <span class="farsi-number" data-type="datetime">{{ $sale->created_at }}</span>
                </div>
                @if($sale->reference)
                <div class="invoice-meta-item">
                    <i class="fas fa-hashtag fa-lg text-secondary"></i>
                    <span>شماره مرجع:</span>
                    <span class="farsi-number">{{ $sale->reference }}</span>
                </div>
                @endif
            </div>
        </div>
        <div class="invoice-actions text-left">
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i>
                <span>بازگشت به لیست</span>
            </a>
            <button type="button" class="btn btn-primary btn-print" onclick="InvoiceManager.printInvoice()">
                <i class="fas fa-print"></i>
                <span>چاپ فاکتور</span>
            </button>
            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                <span>ویرایش فاکتور</span>
            </a>
        </div>
    </div>

    <!-- اطلاعات طرفین -->
    <div class="invoice-parties">
        <!-- اطلاعات مشتری -->
        <div class="party-card animate-fade-in" style="animation-delay: 0.1s">
            <h3 class="party-title"><i class="fas fa-user"></i> <span>اطلاعات خریدار</span></h3>
            <div class="party-info">
                @if($sale->customer)
                    <div class="info-row"><span class="info-label">نام و نام خانوادگی:</span><span class="info-value">{{ $sale->customer->full_name }}</span></div>
                    @if($sale->customer->mobile)
                    <div class="info-row"><span class="info-label">شماره تماس:</span><span class="info-value farsi-number">{{ $sale->customer->mobile }}</span></div>
                    @endif
                    @if($sale->customer->email)
                    <div class="info-row"><span class="info-label">ایمیل:</span><span class="info-value">{{ $sale->customer->email }}</span></div>
                    @endif
                    @if($sale->customer->address)
                    <div class="info-row"><span class="info-label">آدرس:</span><span class="info-value">{{ $sale->customer->address }}</span></div>
                    @endif
                @else
                    <div class="text-muted">اطلاعات مشتری موجود نیست</div>
                @endif
            </div>
        </div>
        <!-- اطلاعات فروشنده -->
        <div class="party-card animate-fade-in" style="animation-delay: 0.2s">
            <h3 class="party-title"><i class="fas fa-store"></i> <span>اطلاعات فروشنده</span></h3>
            <div class="party-info">
                @if($sale->seller)
                    <div class="info-row"><span class="info-label">نام فروشنده:</span><span class="info-value">{{ $sale->seller->full_name }}</span></div>
                    @if($sale->seller->seller_code)
                    <div class="info-row"><span class="info-label">کد فروشنده:</span><span class="info-value farsi-number">{{ $sale->seller->seller_code }}</span></div>
                    @endif
                    @if($sale->seller->email)
                    <div class="info-row"><span class="info-label">ایمیل:</span><span class="info-value">{{ $sale->seller->email }}</span></div>
                    @endif
                @else
                    <div class="text-muted">اطلاعات فروشنده موجود نیست</div>
                @endif
            </div>
        </div>
        <!-- وضعیت فاکتور -->
        <div class="party-card animate-fade-in" style="animation-delay: 0.3s">
            <h3 class="party-title"><i class="fas fa-info-circle"></i> <span>وضعیت فاکتور</span></h3>
            <div class="party-info">
                @php
                    $final_amount = $sale->items->sum(function($item){ return $item->quantity * $item->unit_price - $item->discount + $item->tax; });
                    $paid_amount = $sale->paid_amount ?? 0;
                    $remaining_amount = $final_amount - $paid_amount;
                    if ($remaining_amount < 0) $remaining_amount = 0;

                    if ($paid_amount == 0) {
                        $status_label = 'پرداخت نشده';
                        $status_class = 'status-unpaid';
                    } elseif ($remaining_amount > 0) {
                        $status_label = 'پرداخت ناقص';
                        $status_class = 'status-partial';
                    } else {
                        $status_label = 'پرداخت شده';
                        $status_class = 'status-paid';
                    }
                @endphp
                <div class="info-row">
                    <span class="info-label">وضعیت:</span>
                    <span class="{{ $status_class }} farsi-number">{{ $status_label }}</span>
                </div>
                @if($sale->paid_at)
                <div class="info-row">
                    <span class="info-label">تاریخ پرداخت:</span>
                    <span class="info-value farsi-number" data-type="datetime">{{ $sale->paid_at }}</span>
                </div>
                @endif
                @if($sale->payment_method)
                <div class="info-row">
                    <span class="info-label">روش پرداخت:</span>
                    <span class="info-value">{{ $sale->payment_method }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- جدول اقلام -->
    <div class="invoice-items animate-fade-in" style="animation-delay: 0.4s">
        <h3 class="section-title">اقلام فاکتور</h3>
        <div class="table-responsive">
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>شرح کالا</th>
                        <th class="text-center" width="100">تعداد</th>
                        <th class="text-center" width="100">واحد</th>
                        <th class="text-center" width="150">قیمت واحد</th>
                        <th class="text-center" width="150">تخفیف</th>
                        <th class="text-center" width="150">مالیات</th>
                        <th class="text-center" width="150">قیمت کل</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sum_total = 0;
                        $sum_discount = 0;
                        $sum_tax = 0;
                    @endphp
                    @forelse($sale->items as $index => $item)
                    @php
                        $row_total = $item->quantity * $item->unit_price;
                        $row_discount = $item->discount ?? 0;
                        $row_tax = $item->tax ?? 0;
                        $row_total_price = $row_total - $row_discount + $row_tax;
                        $sum_total += $row_total;
                        $sum_discount += $row_discount;
                        $sum_tax += $row_tax;
                    @endphp
                    <tr>
                        <td class="text-center farsi-number">{{ $index + 1 }}</td>
                        <td>
                            <div class="product-info">
                                <strong>{{ optional($item->product)->title ?: $item->description }}</strong>
                                @if($item->description && optional($item->product)->title)
                                    <small class="text-muted d-block">{{ $item->description }}</small>
                                @endif
                            </div>
                        </td>
                        <td class="text-center farsi-number">{{ number_format($item->quantity) }}</td>
                        <td class="text-center">{{ $item->unit ?: 'عدد' }}</td>
                        <td class="text-center farsi-number" data-type="money">{{ number_format($item->unit_price) }}</td>
                        <td class="text-center">
                            @if($row_discount > 0)
                                <span class="text-danger farsi-number" data-type="money">{{ number_format($row_discount) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row_tax > 0)
                                <span class="text-info farsi-number" data-type="money">{{ number_format($row_tax) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center farsi-number" data-type="money">
                            {{ number_format($row_total_price) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-box-open fa-2x mb-2"></i>
                                <p class="mb-0">هیچ آیتمی برای این فاکتور ثبت نشده است</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- خلاصه فاکتور -->
    <div class="invoice-summary animate-fade-in" style="animation-delay: 0.5s">
        @php
            $final_amount = $sum_total - $sum_discount + $sum_tax;
            $paid_amount = $sale->paid_amount ?? 0;
            $remaining_amount = $final_amount - $paid_amount;
            if ($remaining_amount < 0) $remaining_amount = 0;
        @endphp
        <div class="summary-card">
            <h3 class="summary-title">خلاصه مالی</h3>
            <div class="summary-list">
                <div class="summary-item">
                    <span class="summary-label">جمع کل:</span>
                    <span class="summary-value farsi-number">{{ number_format($sum_total) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">تخفیف:</span>
                    <span class="summary-value text-danger farsi-number">{{ number_format($sum_discount) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">مالیات:</span>
                    <span class="summary-value text-info farsi-number">{{ number_format($sum_tax) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">مبلغ پرداخت شده:</span>
                    <span class="summary-value text-success farsi-number">{{ number_format($paid_amount) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">مبلغ باقیمانده:</span>
                    <span class="summary-value text-danger farsi-number">{{ number_format($remaining_amount) }}</span>
                </div>
                <div class="summary-total">
                    <span>مبلغ نهایی:</span>
                    <span class="farsi-number">{{ number_format($final_amount) }}</span>
                </div>
                <div class="summary-total">
                    <span>مبلغ باقی مانده:</span>
                    <span class="farsi-number">{{ number_format($remaining_amount) }}</span>
                </div>
            </div>
        </div>

        <!-- فرم پرداخت و ویرایش -->
        <div class="summary-card">
            <h3 class="summary-title">ثبت یا ویرایش پرداخت</h3>
            <form id="statusUpdateForm" action="{{ route('sales.update-status', $sale) }}" method="POST" novalidate>
                @csrf
                @method('PATCH')
                <div class="form-group mb-3">
                    <label class="form-label">روش پرداخت</label>
                    <select name="payment_method" class="form-select" required id="paymentMethodSelect">
                        <option value="">انتخاب کنید</option>
                        <option value="cash" {{ old('payment_method', $sale->payment_method) == 'cash' ? 'selected' : '' }}>پرداخت نقدی</option>
                        <option value="card" {{ old('payment_method', $sale->payment_method) == 'card' ? 'selected' : '' }}>کارت به کارت</option>
                        <option value="pos" {{ old('payment_method', $sale->payment_method) == 'pos' ? 'selected' : '' }}>دستگاه کارتخوان</option>
                        <option value="online" {{ old('payment_method', $sale->payment_method) == 'online' ? 'selected' : '' }}>پرداخت آنلاین</option>
                        <option value="cheque" {{ old('payment_method', $sale->payment_method) == 'cheque' ? 'selected' : '' }}>چک</option>
                        <option value="multi" {{ old('payment_method', $sale->payment_method) == 'multi' ? 'selected' : '' }}>چند روش پرداخت</option>
                    </select>
                </div>

                <!-- فرم نقدی -->
                <div id="cashPaymentForm" class="payment-form {{ old('payment_method', $sale->payment_method)=='cash'?'':'d-none' }}">
                    <div class="form-group mb-3">
                        <label class="form-label">مبلغ نقدی (تومان)</label>
                        <input type="number" step="0.01" name="cash_amount" class="form-control" value="{{ old('cash_amount', $sale->cash_amount) }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره رسید</label>
                        <input type="text" name="cash_reference" class="form-control" value="{{ old('cash_reference', $sale->cash_reference) }}">
                    </div>
                </div>
                <!-- فرم کارت به کارت -->
                <div id="cardPaymentForm" class="payment-form {{ old('payment_method', $sale->payment_method)=='card'?'':'d-none' }}">
                    <div class="form-group mb-3">
                        <label class="form-label">مبلغ کارت به کارت (تومان)</label>
                        <input type="number" step="0.01" name="card_amount" class="form-control" value="{{ old('card_amount', $sale->card_amount) }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره کارت مقصد</label>
                        <input type="text" name="card_number" class="form-control" value="{{ old('card_number', $sale->card_number) }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">نام بانک</label>
                        <input type="text" name="card_bank" class="form-control" value="{{ old('card_bank', $sale->card_bank) }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره پیگیری</label>
                        <input type="text" name="card_reference" class="form-control" value="{{ old('card_reference', $sale->card_reference) }}">
                    </div>
                </div>
                <!-- فرم دستگاه کارتخوان -->
                <div id="posPaymentForm" class="payment-form {{ old('payment_method', $sale->payment_method)=='pos'?'':'d-none' }}">
                    <div class="form-group mb-3">
                        <label class="form-label">مبلغ کارتخوان (تومان)</label>
                        <input type="number" step="0.01" name="pos_amount" class="form-control" value="{{ old('pos_amount', $sale->pos_amount) }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره پایانه</label>
                        <input type="text" name="pos_terminal" class="form-control" value="{{ old('pos_terminal', $sale->pos_terminal) }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره پیگیری</label>
                        <input type="text" name="pos_reference" class="form-control" value="{{ old('pos_reference', $sale->pos_reference) }}">
                    </div>
                </div>
                <!-- فرم پرداخت آنلاین -->
                <div id="onlinePaymentForm" class="payment-form {{ old('payment_method', $sale->payment_method)=='online'?'':'d-none' }}">
                    <div class="form-group mb-3">
                        <label class="form-label">مبلغ پرداخت آنلاین (تومان)</label>
                        <input type="number" step="0.01" name="online_amount" class="form-control" value="{{ old('online_amount', $sale->online_amount) }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره تراکنش</label>
                        <input type="text" name="online_transaction_id" class="form-control" value="{{ old('online_transaction_id', $sale->online_transaction_id) }}">
                    </div>
                </div>
                <!-- فرم چک -->
                <div id="chequePaymentForm" class="payment-form {{ old('payment_method', $sale->payment_method)=='cheque'?'':'d-none' }}">
                    <div class="form-group mb-3">
                        <label class="form-label">مبلغ چک (تومان)</label>
                        <input type="number" step="0.01" name="cheque_amount" class="form-control" value="{{ old('cheque_amount', $sale->cheque_amount) }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره چک</label>
                        <input type="text" name="cheque_number" class="form-control" value="{{ old('cheque_number', $sale->cheque_number) }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">نام بانک</label>
                        <input type="text" name="cheque_bank" class="form-control" value="{{ old('cheque_bank', $sale->cheque_bank) }}">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">تاریخ سررسید</label>
                        <input type="date" name="cheque_due_date" class="form-control" value="{{ old('cheque_due_date', $sale->cheque_due_date) }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save"></i>
                    <span>ثبت یا ویرایش پرداخت</span>
                </button>
            </form>
        </div>
    </div>

    <!-- یادداشت‌ها -->
    @if($sale->notes)
    <div class="invoice-notes animate-fade-in" style="animation-delay: 0.6s">
        <div class="notes-content">
            <h3 class="notes-title">
                <i class="fas fa-sticky-note"></i>
                <span>یادداشت‌ها</span>
            </h3>
            <p class="mb-0">{{ $sale->notes }}</p>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // نمایش فرم هر روش پرداخت بر اساس انتخاب
    function togglePaymentForms() {
        let val = document.getElementById('paymentMethodSelect').value;
        let methods = ['cash', 'card', 'pos', 'online', 'cheque', 'multi'];
        methods.forEach(function(method){
            let el = document.getElementById(method+'PaymentForm');
            if(el) el.classList.add('d-none');
        });
        if(document.getElementById(val+'PaymentForm')) {
            document.getElementById(val+'PaymentForm').classList.remove('d-none');
        }
    }
    document.getElementById('paymentMethodSelect').addEventListener('change', togglePaymentForms);
    window.addEventListener('DOMContentLoaded', togglePaymentForms);

    // تابع تبدیل عدد انگلیسی به فارسی
    function toFaNumber(str) {
        return (str+'').replace(/[0-9]/g, function(w){return '۰۱۲۳۴۵۶۷۸۹'[+w]});
    }
    // همه اعداد را به فارسی کن
    function convertAllNumbersToFa() {
        document.querySelectorAll('.farsi-number').forEach(function(el){
            el.innerText = toFaNumber(el.innerText);
        });
    }
    window.addEventListener('DOMContentLoaded', convertAllNumbersToFa);
    // اگر ajax داشتی یا بعد از فرم نیاز شد، دوباره convertAllNumbersToFa() اجرا شود
    // تابع مدیریت پرینت

    const InvoiceManager = {
        printInvoice() {
            window.open("{{ route('sales.print', $sale) }}", "_blank");
        }
    };
</script>
@endsection
