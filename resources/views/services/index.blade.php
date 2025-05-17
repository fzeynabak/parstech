@extends('layouts.app')
@section('title', 'لیست خدمات')

@section('head')
<link rel="stylesheet" href="{{ asset('css/service-index.css') }}">
@endsection

@section('content')
<section class="content pt-4">
    <div class="container-fluid">
        <div class="card card-outline card-primary shadow">
            <div class="card-header service-colored" id="service-header">
                <h5 class="mb-0">لیست خدمات</h5>
                <a href="{{ route('services.create') }}" class="btn btn-success float-left">افزودن خدمات جدید</a>
            </div>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center">
                        <thead>
                            <tr>
                                <th>ردیف</th>
                                <th>عنوان خدمات</th>
                                <th>کد خدمات</th>
                                <th>دسته‌بندی</th>
                                <th>واحد</th>
                                <th>قیمت</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $service->title }}</td>
                                    <td>{{ $service->service_code }}</td>
                                    <td>
                                        @php
                                            $cat = $serviceCategories->firstWhere('id', $service->service_category_id);
                                        @endphp
                                        {{ $cat ? $cat->name : '-' }}
                                    </td>
                                    <td>{{ $service->unit }}</td>
                                    <td>{{ number_format($service->price) }}</td>
                                    <td>
                                        @if($service->is_active)
                                            <span class="badge badge-success">فعال</span>
                                        @else
                                            <span class="badge badge-danger">غیرفعال</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('services.edit', $service->id) }}" class="btn btn-sm btn-info">ویرایش</a>
                                        <form action="{{ route('services.destroy', $service->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('از حذف مطمئن هستید؟')">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">هیچ خدمتی ثبت نشده است.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>
                    {{ $services->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
