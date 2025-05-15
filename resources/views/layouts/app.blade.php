<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد | حسابیر</title>
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar-custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">

    <!-- Persian DateTimePicker (فایل css را دانلود کن و در public/css قرار بده) -->
    <link rel="stylesheet" href="{{ asset('css/mds.bs.datetimepicker.style.css') }}">
    @yield('head')
    @stack('styles')
    <style>
        body { background: #f9fafb; }
    </style>
</head>
<body>
    @include('layouts.sidebar')
    <div class="main-content" id="main-content">
        @yield('content')
    </div>
    <script>
    window.Laravel = {!! json_encode(['csrfToken' => csrf_token()]) !!};
    </script>
    <!-- فقط یک نسخه jQuery -->
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Persian DateTimePicker (فایل js را دانلود کن و در public/js قرار بده) -->
    <script src="{{ asset('js/mds.bs.datetimepicker.js') }}"></script>
    <script src="{{ asset('js/currency-modal.js') }}"></script>
    <script src="{{ asset('js/sidebar-custom.js') }}"></script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
