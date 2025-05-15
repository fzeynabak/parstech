@extends('layouts.app')

@section('title', 'ایجاد فروشنده جدید')

@section('content')
<div class="container">
    <h1>ایجاد فروشنده جدید</h1>
    <form action="{{ route('sellers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="seller_code" class="form-label">کد فروشنده</label>
            <input type="text" id="seller_code" name="seller_code" class="form-control" value="{{ $nextCode }}" readonly>
        </div>
        <div class="mb-3">
            <label for="first_name" class="form-label">نام</label>
            <input type="text" id="first_name" name="first_name" class="form-control">
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">نام خانوادگی</label>
            <input type="text" id="last_name" name="last_name" class="form-control">
        </div>
        <div class="mb-3">
            <label for="company_name" class="form-label">شرکت</label>
            <input type="text" id="company_name" name="company_name" class="form-control">
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">تصویر</label>
            <input type="file" id="image" name="image" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">ذخیره</button>
    </form>
</div>
@endsection
