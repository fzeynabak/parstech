<div class="sales-form-section mb-3">
    <ul class="nav nav-tabs sales-product-tabs" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products-pane" type="button" role="tab">محصولات</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services-pane" type="button" role="tab">خدمات</button>
        </li>
    </ul>
    <div class="tab-content" id="productTabsContent">
        <div class="tab-pane fade show active" id="products-pane" role="tabpanel">
            @include('sales.partials.product_list_inner', ['type' => 'product'])
        </div>
        <div class="tab-pane fade" id="services-pane" role="tabpanel">
            @include('sales.partials.product_list_inner', ['type' => 'service'])
        </div>
    </div>
</div>
