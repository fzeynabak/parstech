// sales-invoice-items.js

// آرایه اقلام فاکتور
let invoiceItems = [];

function renderInvoiceItemsTable() {
    syncInvoiceItemsToForm();
    const tbody = document.getElementById('invoice-items-tbody');
    const totalCell = document.getElementById('invoice-total-cell');
    tbody.innerHTML = '';
    let total = 0;

    if(invoiceItems.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted">هنوز هیچ محصولی به فاکتور افزوده نشده است.</td></tr>`;
        totalCell.innerText = '۰';
        return;
    }

    invoiceItems.forEach((item, idx) => {
        const rowTotal = item.count * item.sale_price;
        total += rowTotal;

        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>${idx + 1}</td>
                <td>${item.type === 'product' ? 'محصول' : 'خدمت'}</td>
                <td>${item.code ?? '-'}</td>
                <td><img src="${item.image ?? '/images/noimage.png'}" style="width:40px;height:40px;border-radius:10px;"></td>
                <td>${item.name}</td>
                <td>
                    <input type="number" min="1" class="form-control form-control-sm text-center invoice-item-count" data-idx="${idx}" value="${item.count}">
                </td>
                <td>${item.sale_price.toLocaleString('fa-IR')}</td>
                <td>${rowTotal.toLocaleString('fa-IR')}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-invoice-item-btn" data-idx="${idx}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });

    totalCell.innerText = total.toLocaleString('fa-IR');
}

document.addEventListener('DOMContentLoaded', function () {
    // افزودن آیتم از لیست محصولات/خدمات
    document.querySelectorAll('.sales-product-table').forEach(tbl => {
        tbl.addEventListener('click', function(e) {
            if (e.target.closest('.add-item-btn')) {
                const btn = e.target.closest('.add-item-btn');
                const tr = btn.closest('tr');
                const tds = tr.querySelectorAll('td');
                // اطلاعات آیتم
                const item = {
                    id: btn.dataset.id,
                    type: btn.dataset.type,
                    code: tds[1].innerText,
                    image: tr.querySelector('img')?.src,
                    name: tds[3].innerText,
                    count: 1,
                    sale_price: parseInt(tds[6].innerText.replace(/,/g, '').replace(/[^\d]/g, '')) || 0
                };
                // اگر قبلاً اضافه شده فقط تعداد را زیاد کن و SweetAlert نمایش بده
                const idx = invoiceItems.findIndex(x => x.id == item.id && x.type == item.type);
                if (idx > -1) {
                    invoiceItems[idx].count += 1;
                    if (typeof Swal !== "undefined") {
                        Swal.fire({
                            icon: 'info',
                            title: 'محصول تکراری',
                            text: 'تعداد این محصول در فاکتور افزایش یافت.',
                            timer: 1300,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        alert('تعداد این محصول در فاکتور افزایش یافت.');
                    }
                } else {
                    invoiceItems.push(item);
                }
                renderInvoiceItemsTable();
            }
        });
    });

    // تغییر تعداد هر آیتم
    document.getElementById('invoice-items-tbody').addEventListener('input', function(e) {
        if (e.target.classList.contains('invoice-item-count')) {
            const idx = +e.target.dataset.idx;
            let val = parseInt(e.target.value);
            if (isNaN(val) || val < 1) val = 1;
            invoiceItems[idx].count = val;
            renderInvoiceItemsTable();
        }
    });

    // حذف آیتم
    document.getElementById('invoice-items-tbody').addEventListener('click', function(e) {
        if (e.target.closest('.remove-invoice-item-btn')) {
            const idx = +e.target.closest('.remove-invoice-item-btn').dataset.idx;
            invoiceItems.splice(idx, 1);
            renderInvoiceItemsTable();
        }
    });
});

// همگام‌سازی با فرم (در صورت نیاز)
function syncInvoiceItemsToForm() {
    const input = document.getElementById('invoice-items-json');
    if (input) {
        input.value = JSON.stringify(invoiceItems);
    }
}
