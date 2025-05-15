@php
    $tabType = $type ?? 'product'; // 'product' or 'service'
@endphp

<div class="row mb-3">
    <div class="col-12">
        <input type="text" class="form-control sales-form-input mb-2"
               id="{{ $tabType }}-search-input"
               placeholder="جستجو در {{ $tabType == 'product' ? 'محصولات' : 'خدمات' }} ...">
    </div>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle sales-product-table">
        <thead>
        <tr>
            <th>افزودن</th>
            <th>کد</th>
            <th>تصویر</th>
            <th>نام</th>
            <th>موجودی</th>
            <th>دسته‌بندی</th>
            <th>قیمت فروش</th>
        </tr>
        </thead>
        <tbody id="{{ $tabType }}-table-body">
        <!-- Ajax: لیست محصولات یا خدمات -->
        </tbody>
    </table>
    <div class="text-center py-2">
        <button class="btn btn-outline-primary d-none" id="{{ $tabType }}-load-more-btn">
            <i class="fa-solid fa-arrow-down ms-1"></i>
            بارگذاری بیشتر
        </button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let type = @json($tabType);
    let page = 1;
    let lastQuery = '';
    let loading = false;

    function renderRows(items) {
        let html = '';
        items.forEach(item => {
            html += `<tr>
                <td>
                    <button class="btn btn-success btn-sm add-product-btn" data-id="${item.id}" data-type="${type}">
                        <i class="fa fa-plus"></i>
                    </button>
                </td>
                <td>${item.code ?? '-'}</td>
                <td><img src="${item.image ?? ''}" class="rounded" style="width:40px;height:40px;object-fit:cover"></td>
                <td>${item.name ?? '-'}</td>
                <td>${item.stock ?? '-'}</td>
                <td>${item.category ?? '-'}</td>
                <td>${item.sell_price ? parseInt(item.sell_price).toLocaleString() : '-'}</td>
            </tr>`;
        });
        return html;
    }

    function loadList(query = '', reset = true) {
        if (loading) return;
        loading = true;
        let url = type === 'product'
            ? '/products/ajax-list'
            : '/services/ajax-list';
        let params = '?limit=10';
        if (query) {
            params += '&q=' + encodeURIComponent(query);
        }
        fetch(url + params)
            .then(r => r.json())
            .then(data => {
                let tbody = document.getElementById(type + '-table-body');
                tbody.innerHTML = renderRows(data);
                loading = false;
            });
    }

    // بارگذاری اولیه: ۱۰ محصول یا خدمت آخر
    loadList();

    // جستجو
    document.getElementById(type + '-search-input').addEventListener('input', function () {
        let q = this.value.trim();
        loadList(q, true);
    });
});
</script>
