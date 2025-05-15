document.addEventListener('DOMContentLoaded', function () {
    ['product', 'service'].forEach(type => {
        let page = 1;
        let lastQuery = '';
        let lastCategory = '';
        const tbody = document.getElementById(`${type}-table-body`);
        const searchInput = document.getElementById(`${type}-search-input`);
        const categorySelect = document.getElementById(`${type}-category-select`);
        const loadMoreBtn = document.getElementById(`${type}-load-more-btn`);

        // دسته‌بندی‌ها را لود کن
        fetch(`/api/categories?type=${type}`)
            .then(res => res.json())
            .then(data => {
                categorySelect.innerHTML = `<option value="">دسته‌بندی: همه</option>` +
                    data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            });

        // جستجو و فیلتر
        function loadItems(reset = false) {
            const query = searchInput.value.trim();
            const category = categorySelect.value;
            if (reset) {
                page = 1;
                tbody.innerHTML = '';
            }
            fetch(`/api/${type}s?search=${encodeURIComponent(query)}&category_id=${category}&page=${page}`)
                .then(res => res.json())
                .then(data => {
                    if (reset && data.data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">موردی یافت نشد.</td></tr>`;
                        loadMoreBtn.classList.add('d-none');
                        return;
                    }
                    data.data.forEach(item => {
                        tbody.insertAdjacentHTML('beforeend', `
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm add-item-btn" data-id="${item.id}" data-type="${type}">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </td>
                                <td>${item.code ?? '-'}</td>
                                <td><img src="${item.image ?? '/images/noimage.png'}" alt="" style="width:45px;height:45px;border-radius:10px;"></td>
                                <td>${item.name}</td>
                                <td>${item.stock ?? '-'}</td>
                                <td>${item.category_name ?? '-'}</td>
                                <td>${item.sale_price ?? '-'}</td>
                            </tr>
                        `);
                    });
                    // دکمه بارگذاری بیشتر
                    if (data.next_page_url) {
                        loadMoreBtn.classList.remove('d-none');
                    } else {
                        loadMoreBtn.classList.add('d-none');
                    }
                });
        }

        // سرچ و فیلتر
        searchInput?.addEventListener('input', () => loadItems(true));
        categorySelect?.addEventListener('change', () => loadItems(true));
        loadMoreBtn?.addEventListener('click', () => {
            page++;
            loadItems(false);
        });

        // افزودن آیتم به فاکتور
        tbody?.addEventListener('click', function(e) {
            if (e.target.closest('.add-item-btn')) {
                const btn = e.target.closest('.add-item-btn');
                // اینجا باید فانکشن افزودن به جدول فاکتور را فراخوانی کنی
                alert(`آیتم با شناسه ${btn.dataset.id} و نوع ${btn.dataset.type} اضافه شد!`);
            }
        });

        // اولین بار
        loadItems(true);
    });
});
document.addEventListener('DOMContentLoaded', function () {
    let invoiceInput = document.getElementById('invoice_number');
    if (invoiceInput && !invoiceInput.value) {
        fetch('/api/invoices/next-number')
            .then(res => res.json())
            .then(data => { invoiceInput.value = data.number; });
    }
});
