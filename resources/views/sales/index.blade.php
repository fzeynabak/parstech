@extends('layouts.app')

@section('title', 'لیست فروش‌ها')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sales-list.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="{{ asset('css/persian-datepicker.min.css') }}">
@endsection

@section('content')
<div class="sales-container">
    <!-- نوار بالایی -->
    <div class="sales-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="sales-title">
                <h1 class="h2 mb-0">
                    <i class="fas fa-shopping-cart text-primary me-2"></i>
                    لیست فروش‌ها
                </h1>
                <p class="text-muted mb-0">مدیریت و پیگیری فروش‌های شما</p>
            </div>
            <div class="sales-actions d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-file-export me-1"></i>
                    خروجی
                </button>
                <a href="{{ route('sales.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    فروش جدید
                </a>
            </div>
        </div>

        <!-- فیلترها -->
        <div class="sales-filters bg-light rounded-3 p-3 mb-4">
            <form id="salesFilterForm" method="GET" class="row g-3">
                <!-- جستجو -->
                <div class="col-md-3">
                    <div class="search-box">
                        <input type="text"
                               class="form-control"
                               id="search"
                               name="search"
                               placeholder="جستجو در فاکتورها..."
                               value="{{ request('search') }}">
                        <i class="fas fa-search"></i>
                    </div>
                </div>

                <!-- فیلتر تاریخ -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-calendar"></i>
                        </span>
                        <input type="text"
                               class="form-control"
                               id="dateRange"
                               name="date_range"
                               placeholder="بازه زمانی"
                               value="{{ request('date_range') }}"
                               autocomplete="off">
                    </div>
                </div>

                <!-- فیلتر مشتری -->
                <div class="col-md-3">
                    <select class="form-select" name="customer" id="customerFilter">
                        <option value="">همه مشتریان</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ request('customer') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- فیلتر وضعیت -->
                <div class="col-md-3">
                    <select class="form-select" name="status" id="statusFilter">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>در انتظار پرداخت</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>پرداخت شده</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>تکمیل شده</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>لغو شده</option>
                    </select>
                </div>

                <!-- فیلتر محدوده قیمت -->
                <div class="col-md-4">
                    <div class="price-range-filter">
                        <label class="form-label">محدوده قیمت (تومان)</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="number"
                                   class="form-control"
                                   name="price_min"
                                   placeholder="از"
                                   value="{{ request('price_min') }}">
                            <span>تا</span>
                            <input type="number"
                                   class="form-control"
                                   name="price_max"
                                   placeholder="تا"
                                   value="{{ request('price_max') }}">
                        </div>
                    </div>
                </div>

                <!-- فیلتر فروشنده -->
                <div class="col-md-3">
                    <select class="form-select" name="seller" id="sellerFilter">
                        <option value="">همه فروشندگان</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}"
                                {{ request('seller') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- دکمه‌های فیلتر -->
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>
                        اعمال فیلتر
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        پاک کردن فیلترها
                    </a>
                </div>
            </form>
        </div>

        <!-- نمایش خلاصه آمار -->
        <div class="sales-stats row g-3 mb-4">
            <div class="col-md-3">
                <div class="stats-card bg-primary bg-opacity-10 rounded-3 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">کل فروش</h6>
                            <h3 class="mb-0">{{ number_format($totalSales) }} تومان</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-chart-line fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card bg-success bg-opacity-10 rounded-3 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">تعداد فروش</h6>
                            <h3 class="mb-0">{{ number_format($salesCount) }}</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-shopping-bag fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card bg-info bg-opacity-10 rounded-3 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">میانگین فروش</h6>
                            <h3 class="mb-0">{{ number_format($averageSale) }} تومان</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-calculator fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card bg-warning bg-opacity-10 rounded-3 p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">فروش امروز</h6>
                            <h3 class="mb-0">{{ number_format($todaySales) }} تومان</h3>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول فروش‌ها -->
    <div class="sales-table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th>شماره فاکتور</th>
                        <th>تاریخ</th>
                        <th>مشتری</th>
                        <th>فروشنده</th>
                        <th>وضعیت</th>
                        <th>مبلغ کل</th>
                        <th>مبلغ پرداخت شده</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input sale-checkbox" type="checkbox" value="{{ $sale->id }}">
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $sale->invoice_number }}</span>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span>{{ jdate($sale->created_at)->format('Y/m/d') }}</span>
                                <small class="text-muted">{{ jdate($sale->created_at)->format('H:i') }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="customer-avatar">
                                    @if($sale->customer && $sale->customer->avatar)
                                        <img src="{{ $sale->customer->avatar }}"
                                             alt="{{ $sale->customer->full_name }}"
                                             class="rounded-circle"
                                             width="32"
                                             height="32">
                                    @else
                                        <div class="avatar-placeholder">
                                            {{ $sale->customer ? substr($sale->customer->full_name, 0, 2) : 'NA' }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div>{{ optional($sale->customer)->full_name }}</div>
                                    <small class="text-muted">{{ optional($sale->customer)->mobile }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ optional($sale->seller)->full_name }}</td>
                        <td>
                            <span class="badge bg-{{ $sale->status_color }}">
                                {{ $sale->status_label }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ number_format($sale->total_price) }} تومان</span>
                                @if($sale->discount > 0)
                                    <small class="text-success">{{ number_format($sale->discount) }} تومان تخفیف</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ number_format($sale->paid_amount) }} تومان</span>
                                @if($sale->remaining_amount > 0)
                                    <small class="text-danger">{{ number_format($sale->remaining_amount) }} تومان باقیمانده</small>
                                @endif
                                @if($sale->status === 'paid')
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i>
                                        پرداخت کامل
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('sales.show', $sale) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   data-bs-toggle="tooltip"
                                   title="مشاهده جزئیات">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($sale->status === 'pending')
                                <a href="{{ route('sales.edit', $sale) }}"
                                   class="btn btn-sm btn-outline-warning"
                                   data-bs-toggle="tooltip"
                                   title="ویرایش">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="deleteSale({{ $sale->id }})"
                                        data-bs-toggle="tooltip"
                                        title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                <button type="button"
                                        class="btn btn-sm btn-outline-info"
                                        onclick="printInvoice({{ $sale->id }})"
                                        data-bs-toggle="tooltip"
                                        title="چاپ فاکتور">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="empty-state">
                                <img src="{{ asset('images/empty-sales.svg') }}"
                                     alt="بدون فروش"
                                     class="mb-3"
                                     width="120">
                                <h4>هیچ فروشی یافت نشد!</h4>
                                <p class="text-muted">می‌توانید اولین فروش خود را ثبت کنید.</p>
                                <a href="{{ route('sales.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    ثبت فروش جدید
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- پاگینیشن -->
        <div class="d-flex justify-content-between align-items-center border-top pt-3">
            <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm"
                        style="width: auto"
                        onchange="changePerPage(this)">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="text-muted">
                    نمایش {{ $sales->firstItem() ?? 0 }} تا {{ $sales->lastItem() ?? 0 }} از {{ $sales->total() ?? 0 }} مورد
                </span>
            </div>
            {{ $sales->withQueryString()->links() }}
        </div>
    </div>
</div>

@include('sales.partials.export-modal')

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment-jalaali@0.9.2/build/moment-jalaali.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function() {
    // تنظیمات DateRangePicker
    $('#dateRange').daterangepicker({
        locale: {
            format: 'jYYYY/jMM/jDD'
        },
        autoUpdateInput: false
    });

    // هندل کردن انتخاب تاریخ
    $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('jYYYY/jMM/jDD') + ' - ' + picker.endDate.format('jYYYY/jMM/jDD'));
    });

    // هندل کردن پاک کردن تاریخ
    $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // Select All functionality
    $('#selectAll').change(function() {
        $('.sale-checkbox').prop('checked', $(this).prop('checked'));
    });
});

function deleteSale(id) {
    if (confirm('آیا از حذف این فاکتور اطمینان دارید؟')) {
        axios.delete(`/sales/${id}`)
            .then(response => {
                if (response.data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                alert('خطا در حذف فاکتور');
                console.error(error);
            });
    }
}

function printInvoice(id) {
    window.open(`/sales/${id}/print`, '_blank');
}

function changePerPage(select) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', select.value);
    window.location.href = url.toString();
}
</script>
@endsection
