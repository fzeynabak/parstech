<!-- مودال خروجی گرفتن -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-export me-1"></i>
                    خروجی گرفتن
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm" action="{{ route('sales.export') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">فرمت فایل</label>
                        <select class="form-select" name="format" required>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">محدوده زمانی</label>
                        <input type="text"
                               class="form-control"
                               name="date_range"
                               id="exportDateRange">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   name="include_details"
                                   id="includeDetails">
                            <label class="form-check-label" for="includeDetails">
                                شامل جزئیات اقلام
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="submit" form="exportForm" class="btn btn-primary">
                    <i class="fas fa-download me-1"></i>
                    دریافت فایل
                </button>
            </div>
        </div>
    </div>
</div>
