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
                <td>${item.sale_price}</td>
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
                    sale_price: tds[6].innerText.replace(/,/g, '').trim()
                };
                // اگر قبلاً اضافه شده باشد فقط تعداد را زیاد کن
                const idx = invoiceItems.findIndex(x => x.id == item.id && x.type == item.type);
                if (idx > -1) {
                    invoiceItems[idx].count += 1;
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
            const idx = parseInt(e.target.dataset.idx);
            const val = Math.max(1, parseInt(e.target.value) || 1);
            invoiceItems[idx].count = val;
            renderInvoiceItemsTable();
        }
    });

    // حذف آیتم
    document.getElementById('invoice-items-tbody').addEventListener('click', function(e) {
        if (e.target.closest('.remove-invoice-item-btn')) {
            const idx = parseInt(e.target.closest('.remove-invoice-item-btn').dataset.idx);
            invoiceItems.splice(idx, 1);
            renderInvoiceItemsTable();
        }
    });

    // هنگام لود اولیه
    renderInvoiceItemsTable();
});
function syncInvoiceItemsToForm() {
    // آرایه را به صورت JSON در یک input مخفی قرار بده
    let hiddenInput = document.getElementById('invoice_items_input');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'invoice_items';
        hiddenInput.id = 'invoice_items_input';
        document.getElementById('sales-invoice-form').appendChild(hiddenInput);
    }
    hiddenInput.value = JSON.stringify(invoiceItems);
}
