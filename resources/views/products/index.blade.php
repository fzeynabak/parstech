@extends('layouts.app')

@section('title', 'لیست محصولات')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">لیست محصولات</h4>
            <a href="{{ route('products.create') }}" class="btn btn-success">افزودن محصول جدید</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>نام</th>
                        <th>کد</th>
                        <th>دسته‌بندی</th>
                        <th>برند</th>
                        <th>قیمت فروش</th>
                        <th>موجودی</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->category?->name ?? '-' }}</td>
                            <td>{{ $product->brand?->name ?? '-' }}</td>
                            <td>{{ number_format($product->sell_price) }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-info btn-sm">نمایش</a>
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">ویرایش</a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید؟')">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-danger">محصولی یافت نشد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
