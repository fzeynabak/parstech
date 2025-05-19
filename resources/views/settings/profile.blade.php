@extends('layouts.app')

@section('title', 'تنظیمات فروشگاه')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- منوی تنظیمات -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#store-info" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fas fa-store me-2"></i>
                            اطلاعات فروشگاه
                        </a>
                        <a href="#invoice-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-file-invoice me-2"></i>
                            تنظیمات فاکتور
                        </a>
                        <a href="#payment-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-money-bill me-2"></i>
                            تنظیمات مالی
                        </a>
                        <a href="#appearance" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-paint-brush me-2"></i>
                            ظاهر فاکتور
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- محتوای تنظیمات -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- اطلاعات فروشگاه -->
                <div class="tab-pane fade show active" id="store-info">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">اطلاعات فروشگاه</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('settings.store.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- لوگو -->
                                    <div class="col-12 mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="logo-preview me-3">
                                                <img src="{{ $settings->logo_url ?? asset('images/default-logo.png') }}"
                                                     alt="لوگو فروشگاه"
                                                     class="rounded-3"
                                                     style="max-width: 100px;">
                                            </div>
                                            <div class="logo-upload">
                                                <label class="btn btn-outline-primary mb-0">
                                                    <i class="fas fa-upload me-2"></i>
                                                    آپلود لوگو
                                                    <input type="file" name="logo" class="d-none" accept="image/*">
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- نام فروشگاه -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">نام فروشگاه</label>
                                        <input type="text"
                                               class="form-control"
                                               name="store_name"
                                               value="{{ $settings->store_name ?? '' }}"
                                               required>
                                    </div>

                                    <!-- تلفن -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">تلفن ثابت</label>
                                        <input type="text"
                                               class="form-control"
                                               name="store_phone"
                                               value="{{ $settings->store_phone ?? '' }}">
                                    </div>

                                    <!-- موبایل -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">تلفن همراه</label>
                                        <input type="text"
                                               class="form-control"
                                               name="store_mobile"
                                               value="{{ $settings->store_mobile ?? '' }}">
                                    </div>

                                    <!-- ایمیل -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ایمیل</label>
                                        <input type="email"
                                               class="form-control"
                                               name="store_email"
                                               value="{{ $settings->store_email ?? '' }}">
                                    </div>

                                    <!-- وب‌سایت -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">وب‌سایت</label>
                                        <input type="url"
                                               class="form-control"
                                               name="store_website"
                                               value="{{ $settings->store_website ?? '' }}">
                                    </div>

                                    <!-- آدرس -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label">آدرس</label>
                                        <textarea class="form-control"
                                                  name="store_address"
                                                  rows="3">{{ $settings->store_address ?? '' }}</textarea>
                                    </div>

                                    <!-- توضیحات -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label">توضیحات فروشگاه</label>
                                        <textarea class="form-control"
                                                  name="store_description"
                                                  rows="3">{{ $settings->store_description ?? '' }}</textarea>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>
                                            ذخیره تغییرات
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات فاکتور -->
                <div class="tab-pane fade" id="invoice-settings">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">تنظیمات فاکتور</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('settings.invoice.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- متن سربرگ -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label">متن سربرگ فاکتور</label>
                                        <textarea class="form-control"
                                                  name="invoice_header_text"
                                                  rows="2">{{ $settings->invoice_header_text ?? '' }}</textarea>
                                    </div>

                                    <!-- متن پاورقی -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label">متن پاورقی فاکتور</label>
                                        <textarea class="form-control"
                                                  name="invoice_footer_text"
                                                  rows="2">{{ $settings->invoice_footer_text ?? '' }}</textarea>
                                    </div>

                                    <!-- نمایش QR -->
                                    <div class="col-12 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="show_qr_code"
                                                   id="showQrCode"
                                                   {{ $settings->show_qr_code ? 'checked' : '' }}>
                                            <label class="form-check-label" for="showQrCode">
                                                نمایش کد QR در فاکتور
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>
                                            ذخیره تغییرات
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- تنظیمات مالی -->
                <div class="tab-pane fade" id="payment-settings">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">تنظیمات مالی</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('settings.payment.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- درصد مالیات پیش‌فرض -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">درصد مالیات پیش‌فرض</label>
                                        <div class="input-group">
                                            <input type="number"
                                                   class="form-control"
                                                   name="default_tax_rate"
                                                   step="0.01"
                                                   value="{{ $settings->default_tax_rate ?? '9.00' }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>

                                    <!-- اطلاعات حساب‌های بانکی -->
                                    <div class="col-12 mb-3">
                                        <label class="form-label">حساب‌های بانکی</label>
                                        <div id="bankAccounts">
                                            @if($settings->bank_accounts)
                                                @foreach(json_decode($settings->bank_accounts) as $account)
                                                <div class="input-group mb-2">
                                                    <input type="text"
                                                           class="form-control"
                                                           name="bank_accounts[]"
                                                           value="{{ $account }}"
                                                           placeholder="شماره حساب / شماره کارت">
                                                    <button type="button"
                                                            class="btn btn-outline-danger"
                                                            onclick="this.parentElement.remove()">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                onclick="addBankAccount()">
                                            <i class="fas fa-plus me-2"></i>
                                            افزودن حساب بانکی
                                        </button>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>
                                            ذخیره تغییرات
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ظاهر فاکتور -->
                <div class="tab-pane fade" id="appearance">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">ظاهر فاکتور</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('settings.appearance.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- مهر شرکت -->
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">مهر شرکت</label>
                                        <div class="d-flex align-items-center">
                                            <div class="stamp-preview me-3">
                                                @if($settings->stamp_path)
                                                    <img src="{{ asset($settings->stamp_path) }}"
                                                         alt="مهر شرکت"
                                                         style="max-width: 100px;">
                                                @endif
                                            </div>
                                            <div class="stamp-upload">
                                                <label class
