<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صدور فاکتور فروش | برنامه حسابداری پارس‌تک</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sales.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('styles')
</head>
<body class="sales-layout-bg">
    @include('layouts.sidebar')
    <div class="container sales-invoice-container my-5 shadow-lg rounded-4 p-4 bg-white">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mb-4 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-cash-register fa-2x text-primary"></i>
                <h2 class="fw-bold m-0">صدور فاکتور فروش</h2>
            </div>
            <div class="text-muted fs-6 mt-3 mt-md-0">
                <i class="fa-regular fa-calendar"></i>
                {{ jdate(now())->format('Y/m/d H:i') }}
            </div>
        </div>

        <!-- Main Content Slot -->
        <div class="sales-body-content">
            @yield('content')
        </div>
    </div>

    <!-- Sales Invoice Modal (For product/service details, etc.) -->
    <div id="sales-modal-root"></div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/sales.js') }}"></script>
    <script src="{{ asset('js/sales-invoice-items.js') }}"></script>
    @yield('scripts')
</body>
</html>
