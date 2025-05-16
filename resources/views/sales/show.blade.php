@extends('layouts.app')

@section('title', 'جزئیات فاکتور #' . $sale->invoice_number)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sales-show.css') }}">
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

            @if($sale->status === 'pending')
            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                <span>ویرایش فاکتور</span>
            </a>
            @endif
        </div>
    </div>

    <!-- اطلاعات طرفین -->
    <div class="invoice-parties">
        <!-- اطلاعات مشتری -->
        <div class="party-card animate-fade-in" style="animation-delay: 0.1s">
            <h3 class="party-title">
                <i class="fas fa-user"></i>
                <span>اطلاعات خریدار</span>
            </h3>
            <div class="party-info">
                @if($sale->customer)
                    <div class="info-row">
                        <span class="info-label">نام و نام خانوادگی:</span>
                        <span class="info-value">{{ $sale->customer->full_name }}</span>
                    </div>
                    @if($sale->customer->mobile)
                    <div class="info-row">
                        <span class="info-label">شماره تماس:</span>
                        <span class="info-value farsi-number">{{ $sale->customer->mobile }}</span>
                    </div>
                    @endif
                    @if($sale->customer->email)
                    <div class="info-row">
                        <span class="info-label">ایمیل:</span>
                        <span class="info-value">{{ $sale->customer->email }}</span>
                    </div>
                    @endif
                    @if($sale->customer->address)
                    <div class="info-row">
                        <span class="info-label">آدرس:</span>
                        <span class="info-value">{{ $sale->customer->address }}</span>
                    </div>
                    @endif
                @else
                    <div class="text-muted">اطلاعات مشتری موجود نیست</div>
                @endif
            </div>
        </div>

        <!-- اطلاعات فروشنده -->
        <div class="party-card animate-fade-in" style="animation-delay: 0.2s">
            <h3 class="party-title">
                <i class="fas fa-store"></i>
                <span>اطلاعات فروشنده</span>
            </h3>
            <div class="party-info">
                @if($sale->seller)
                    <div class="info-row">
                        <span class="info-label">نام فروشنده:</span>
                        <span class="info-value">{{ $sale->seller->full_name }}</span>
                    </div>
                    @if($sale->seller->code)
                    <div class="info-row">
                        <span class="info-label">کد فروشنده:</span>
                        <span class="info-value farsi-number">{{ $sale->seller->code }}</span>
                    </div>
                    @endif
                    @if($sale->seller->email)
                    <div class="info-row">
                        <span class="info-label">ایمیل:</span>
                        <span class="info-value">{{ $sale->seller->email }}</span>
                    </div>
                    @endif
                @else
                    <div class="text-muted">اطلاعات فروشنده موجود نیست</div>
                @endif
            </div>
        </div>

        <!-- وضعیت فاکتور -->
        <div class="party-card animate-fade-in" style="animation-delay: 0.3s">
            <h3 class="party-title">
                <i class="fas fa-info-circle"></i>
                <span>وضعیت فاکتور</span>
            </h3>
            <div class="party-info">
                <div class="info-row">
                    <span class="info-label">وضعیت:</span>
                    <span class="status-badge status-{{ $sale->status }}">
                        {{ $sale->status_label }}
                    </span>
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
                @if($sale->payment_reference)
                <div class="info-row">
                    <span class="info-label">شماره پیگیری:</span>
                    <span class="info-value farsi-number">{{ $sale->payment_reference }}</span>
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
                    @forelse($sale->items as $index => $item)
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
                        <td class="text-center farsi-number">{{ $item->quantity }}</td>
                        <td class="text-center">{{ $item->unit ?: 'عدد' }}</td>
                        <td class="text-center farsi-number" data-type="money">{{ $item->unit_price }}</td>
                        <td class="text-center">
                            @if($item->discount > 0)
                                <span class="text-danger farsi-number" data-type="money">{{ $item->discount }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->tax > 0)
                                <span class="text-info farsi-number">{{ $item->tax }}٪</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center farsi-number" data-type="money">
                            {{ $item->quantity * $item->unit_price - ($item->discount ?? 0) }}
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
        <div class="summary-card">
            <h3 class="summary-title">خلاصه مالی</h3>
            <div class="summary-list">
                <div class="summary-item">
                    <span class="summary-label">جمع کل:</span>
                    <span class="summary-value farsi-number" data-type="money">{{ $sale->total_price }}</span>
                </div>
                @if($sale->discount > 0)
                <div class="summary-item">
                    <span class="summary-label">تخفیف:</span>
                    <span class="summary-value text-danger farsi-number" data-type="money">{{ $sale->discount }}</span>
                </div>
                @endif
                @if($sale->tax > 0)
                <div class="summary-item">
                    <span class="summary-label">مالیات:</span>
                    <span class="summary-value text-info farsi-number" data-type="money">{{ $sale->tax }}</span>
                </div>
                @endif
                <div class="summary-item">
                    <span class="summary-label">مبلغ پرداخت شده:</span>
                    <span class="summary-value text-success farsi-number" data-type="money">{{ $sale->paid_amount }}</span>
                </div>
                @if($sale->remaining_amount > 0)
                <div class="summary-item">
                    <span class="summary-label">مبلغ باقیمانده:</span>
                    <span class="summary-value text-danger farsi-number" data-type="money">{{ $sale->remaining_amount }}</span>
                </div>
                @endif
                <div class="summary-total">
                    <span>مبلغ نهایی:</span>
                    <span class="farsi-number" data-type="money">{{ $sale->total_price - $sale->discount + $sale->tax }}</span>
                </div>
            </div>
        </div>

        <!-- فرم تغییر وضعیت -->
        @if($sale->status === 'pending')
<div class="summary-card">
    <h3 class="summary-title">تغییر وضعیت پرداخت</h3>
    <form id="statusUpdateForm" action="{{ route('sales.update-status', $sale) }}" method="POST">
        @csrf
        @method('PATCH')

        <!-- مبلغ کل و باقیمانده -->
        <div class="invoice-payment-status mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="payment-info-box">
                        <div class="title">مبلغ کل</div>
                        <div class="amount">{{ number_format($sale->total_amount) }} تومان</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="payment-info-box">
                        <div class="title">پرداخت شده</div>
                        <div class="amount text-success">{{ number_format($sale->paid_amount) }} تومان</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="payment-info-box">
                        <div class="title">مانده حساب</div>
                        <div class="amount text-danger">{{ number_format($sale->remaining_amount) }} تومان</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- انتخاب روش پرداخت -->
        <div class="form-group mb-3">
            <label class="form-label">روش پرداخت</label>
            <select name="payment_method" class="form-select" required id="paymentMethodSelect">
                <option value="">انتخاب کنید</option>
                <option value="cash">پرداخت نقدی</option>
                <option value="card">کارت به کارت</option>
                <option value="pos">دستگاه کارتخوان</option>
                <option value="online">پرداخت آنلاین</option>
                <option value="cheque">چک</option>
                <option value="multi">چند روش پرداخت</option>
            </select>
        </div>

        <!-- فرم پرداخت نقدی -->
        <div id="cashPaymentForm" class="payment-form d-none">
            <div class="form-group mb-3">
                <label class="form-label">مبلغ نقدی (تومان)</label>
                <input type="number" name="cash_amount" class="form-control" placeholder="مبلغ را وارد کنید">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">شماره رسید</label>
                <input type="text" name="cash_reference" class="form-control" placeholder="شماره رسید را وارد کنید">
            </div>
        </div>

        <!-- فرم کارت به کارت -->
        <div id="cardPaymentForm" class="payment-form d-none">
            <div class="form-group mb-3">
                <label class="form-label">مبلغ کارت به کارت (تومان)</label>
                <input type="number" name="card_amount" class="form-control" placeholder="مبلغ را وارد کنید">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">شماره کارت مقصد</label>
                <input type="text" name="card_number" class="form-control" placeholder="شماره کارت را وارد کنید">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">نام بانک</label>
                <input type="text" name="card_bank" class="form-control" placeholder="نام بانک را وارد کنید">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">شماره پیگیری</label>
                <input type="text" name="card_reference" class="form-control" placeholder="شماره پیگیری را وارد کنید">
            </div>
        </div>

        <!-- فرم دستگاه کارتخوان -->
        <div id="posPaymentForm" class="payment-form d-none">
            <div class="form-group mb-3">
                <label class="form-label">مبلغ کارتخوان (تومان)</label>
                <input type="number" name="pos_amount" class="form-control" placeholder="مبلغ را وارد کنید">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">شماره پایانه</label>
                <input type="text" name="pos_terminal" class="form-control" placeholder="شماره پایانه را وارد کنید">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">شماره پیگیری</label>
                <input type="text" name="pos_reference" class="form-control" placeholder="شماره پیگیری را وارد کنید">
            </div>
        </div>

        <!-- فرم پرداخت آنلاین -->
        <div id="onlinePaymentForm" class="payment-form d-none">
            <div class="form-group mb-3">
                <label class="form-label">مبلغ پرداخت آنلاین (تومان)</label>
                <input type="number" name="online_amount" class="form-control" placeholder="مبلغ را وارد کنید">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">شماره تراکنش</label>
                <input type="text" name="online_transaction_id" class="form-control" placeholder="شماره تراکنش را وارد کنید">
            </div>
        </div>

        <!-- فرم چک -->
        <div id="chequePaymentForm" class="payment-form d-none">
            <div class="form-group mb-3">
                <label class="form-label">مبلغ چک (تومان)</label>
                <input type="number" name="cheque_amount" class="form-control" placeholder="مبلغ را وارد کنید">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">شماره چک</label>
                <input type="text" name="cheque_number" class="form-control" placeholder="شماره چک را وارد کنید">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">نام بانک</label>
                <input type="text" name="cheque_bank" class="form-control" placeholder="نام بانک را وارد کنید">
            </div>
            <div class="form-group mb-3">
                <label class="form-label">تاریخ سررسید</label>
                <input type="date" name="cheque_due_date" class="form-control">
            </div>
        </div>

        <!-- فرم چند روش پرداخت -->
        <div id="multiPaymentForm" class="payment-form d-none">
            <div class="alert alert-info">
                لطفاً مبالغ پرداختی برای هر روش را مشخص کنید.
            </div>

            <!-- نقدی -->
            <div class="multi-payment-section">
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="multiCashCheck">
                    <label class="form-check-label" for="multiCashCheck">پرداخت نقدی</label>
                </div>
                <div id="multiCashFields" class="d-none">
                    <div class="form-group mb-3">
                        <label class="form-label">مبلغ نقدی (تومان)</label>
                        <input type="number" name="multi_cash_amount" class="form-control multi-amount" placeholder="مبلغ را وارد کنید">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره رسید</label>
                        <input type="text" name="multi_cash_reference" class="form-control" placeholder="شماره رسید را وارد کنید">
                    </div>
                </div>
            </div>

            <!-- کارت به کارت -->
            <div class="multi-payment-section mt-3">
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="multiCardCheck">
                    <label class="form-check-label" for="multiCardCheck">کارت به کارت</label>
                </div>
                <div id="multiCardFields" class="d-none">
                    <div class="form-group mb-3">
                        <label class="form-label">مبلغ کارت به کارت (تومان)</label>
                        <input type="number" name="multi_card_amount" class="form-control multi-amount" placeholder="مبلغ را وارد کنید">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره کارت</label>
                        <input type="text" name="multi_card_number" class="form-control" placeholder="شماره کارت را وارد کنید">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره پیگیری</label>
                        <input type="text" name="multi_card_reference" class="form-control" placeholder="شماره پیگیری را وارد کنید">
                    </div>
                </div>
            </div>

            <!-- چک -->
            <div class="multi-payment-section mt-3">
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="multiChequeCheck">
                    <label class="form-check-label" for="multiChequeCheck">چک</label>
                </div>
                <div id="multiChequeFields" class="d-none">
                    <div class="form-group mb-3">
                        <label class="form-label">مبلغ چک (تومان)</label>
                        <input type="number" name="multi_cheque_amount" class="form-control multi-amount" placeholder="مبلغ را وارد کنید">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">شماره چک</label>
                        <input type="text" name="multi_cheque_number" class="form-control" placeholder="شماره چک را وارد کنید">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">تاریخ سررسید</label>
                        <input type="date" name="multi_cheque_due_date" class="form-control">
                    </div>
                </div>
            </div>

            <div class="payment-summary mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span>جمع مبالغ وارد شده:</span>
                    <span id="multiPaymentTotal" class="text-primary">۰ تومان</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span>مانده حساب:</span>
                    <span id="multiPaymentRemaining" class="text-danger">{{ number_format($sale->remaining_amount) }} تومان</span>
                </div>
            </div>
        </div>

        <!-- یادداشت پرداخت -->
        <div class="form-group mb-3 mt-4">
            <label class="form-label">یادداشت پرداخت</label>
            <textarea name="payment_notes" class="form-control" rows="3" placeholder="توضیحات اضافی در مورد پرداخت را اینجا وارد کنید..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-save"></i>
            <span>ثبت اطلاعات پرداخت</span>
        </button>
    </form>
</div>
@endif
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
<script src="{{ asset('js/sales-show.js') }}"></script>
@endsection
