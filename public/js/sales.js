// sales.js - اسکریپت کلی صفحه فروش (مناسب برای افزودن تعاملات JS/AJAX در هر بخش)
// این فایل فقط پایه است و هر کامپوننت اسکریپت خودش را ماژولار به آن اضافه می‌کند

document.addEventListener('DOMContentLoaded', function () {
    // مثال: اسکرول به بالای صفحه هنگام نمایش مدال
    window.salesShowModal = function (modalId) {
        document.getElementById('sales-modal-root').innerHTML = document.getElementById(modalId).innerHTML;
        window.scrollTo({top: 0, behavior: "smooth"});
    };

    // سایر فانکشن‌های عمومی بعداً اضافه می‌شوند
});
