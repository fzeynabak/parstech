<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>کد خدمات</th>
            <th>عنوان خدمات</th>
            <th>دسته‌بندی</th>
            <th>واحد</th>
            <th>قیمت</th>
            <th>عملیات</th>
        </tr>
    </thead>
    <tbody>
        @foreach($services as $service)
            <tr>
                <td>{{ $service->service_code }}</td>
                <td>{{ $service->title }}</td>
                <td>{{ $service->category ? $service->category->name : '-' }}</td>
                <td>{{ $service->unit }}</td>
                <td>{{ number_format($service->price) }}</td>
                <td>
                    <button type="button" class="btn btn-success btn-sm add-service-btn"
                        data-id="{{ $service->id }}"
                        data-title="{{ $service->title }}"
                        data-price="{{ $service->price }}"
                        data-unit="{{ $service->unit }}">
                        افزودن
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
