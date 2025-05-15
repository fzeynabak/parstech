@extends('layouts.app')

@section('title', 'لیست فروشندگان')

@section('content')
<div class="container">
    <h1>لیست فروشندگان</h1>
    <a href="{{ route('sellers.create') }}" class="btn btn-primary mb-3">افزودن فروشنده جدید</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>کد فروشنده</th>
                <th>نام</th>
                <th>نام خانوادگی</th>
                <th>شرکت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sellers as $seller)
            <tr>
                <td>{{ $seller->seller_code }}</td>
                <td>{{ $seller->first_name }}</td>
                <td>{{ $seller->last_name }}</td>
                <td>{{ $seller->company_name }}</td>
                <td>
                    <a href="{{ route('sellers.edit', $seller) }}" class="btn btn-warning">ویرایش</a>
                    <form action="{{ route('sellers.destroy', $seller) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $sellers->links() }}
</div>
@endsection
