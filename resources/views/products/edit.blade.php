@extends('layouts.app')

@section('title', 'ویرایش محصول')

@section('content')
<div class="container">
    <h2 class="mb-4">ویرایش محصول</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="name" class="form-label">نام محصول</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="col-md-4">
                <label for="code" class="form-label">کد محصول</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $product->code) }}" required>
            </div>
            <div class="col-md-4">
                <label for="unit" class="form-label">واحد</label>
                <select class="form-select" name="unit" id="unit">
                    <option value="">انتخاب کنید</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->title }}" @if(old('unit', $product->unit) == $unit->title) selected @endif>{{ $unit->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="category_id" class="form-label">دسته‌بندی</label>
                <select class="form-select" name="category_id" id="category_id" required>
                    <option value="">انتخاب کنید</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @if(old('category_id', $product->category_id) == $category->id) selected @endif>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="brand_id" class="form-label">برند</label>
                <select class="form-select" name="brand_id" id="brand_id">
                    <option value="">انتخاب کنید</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @if(old('brand_id', $product->brand_id) == $brand->id) selected @endif>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="stock" class="form-label">موجودی</label>
                <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock', $product->stock) }}">
            </div>
            <div class="col-md-2">
                <label for="min_stock" class="form-label">حداقل موجودی</label>
                <input type="number" class="form-control" id="min_stock" name="min_stock" value="{{ old('min_stock', $product->min_stock) }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label for="weight" class="form-label">وزن (گرم)</label>
                <input type="number" class="form-control" id="weight" name="weight" value="{{ old('weight', $product->weight) }}">
            </div>
            <div class="col-md-3">
                <label for="buy_price" class="form-label">قیمت خرید</label>
                <input type="number" class="form-control" id="buy_price" name="buy_price" value="{{ old('buy_price', $product->buy_price) }}">
            </div>
            <div class="col-md-3">
                <label for="sell_price" class="form-label">قیمت فروش</label>
                <input type="number" class="form-control" id="sell_price" name="sell_price" value="{{ old('sell_price', $product->sell_price) }}">
            </div>
            <div class="col-md-3">
                <label for="discount" class="form-label">تخفیف (%)</label>
                <input type="number" class="form-control" id="discount" name="discount" value="{{ old('discount', $product->discount) }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="barcode" class="form-label">بارکد</label>
                <input type="text" class="form-control" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}">
            </div>
            <div class="col-md-4">
                <label for="store_barcode" class="form-label">بارکد فروشگاهی</label>
                <input type="text" class="form-control" id="store_barcode" name="store_barcode" value="{{ old('store_barcode', $product->store_barcode) }}">
            </div>
        </div>

        <div class="mb-3">
            <label for="short_desc" class="form-label">توضیح کوتاه</label>
            <input type="text" class="form-control" id="short_desc" name="short_desc" value="{{ old('short_desc', $product->short_desc) }}">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">توضیحات کامل</label>
            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">تصویر شاخص</label>
            @if($product->image)
                <div class="mb-2"><img src="{{ asset('storage/' . $product->image) }}" alt="تصویر محصول" width="120"></div>
            @endif
            <input type="file" class="form-control" name="image" id="image" accept="image/*">
        </div>
        <div class="mb-3">
            <label for="video" class="form-label">ویدیو</label>
            @if($product->video)
                <div class="mb-2"><video src="{{ asset('storage/' . $product->video) }}" width="180" controls></video></div>
            @endif
            <input type="file" class="form-control" name="video" id="video" accept="video/*">
        </div>

        <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">بازگشت</a>
    </form>
</div>
@endsection
