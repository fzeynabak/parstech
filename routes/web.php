<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PreInvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuickSaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ShareholderController;
use App\Models\Person;
use Illuminate\Http\Request;
use App\Http\Controllers\SaleController;

use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ServiceApiController;

Route::get('/services/next-code', [ServiceController::class, 'nextCode']);
Route::get('sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');


Route::post('sales/bulk-delete', [SaleController::class, 'bulkDelete'])->name('sales.bulk-delete');
Route::match(['post', 'patch'], 'sales/{sale}/status', [SaleController::class, 'updateStatus'])->name('sales.update-status');
Route::post('sales/export', [SaleController::class, 'export'])->name('sales.export');
Route::get('sales/next-invoice-number', [SaleController::class, 'nextInvoiceNumber'])->name('sales.next-invoice-number');


Route::get('/sales/next-invoice-number', [SaleController::class, 'nextInvoiceNumber']);
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');


Route::post('/persons/{person}/percent', [PersonController::class, 'updatePercent'])->name('persons.updatePercent');

Route::get('/sales/item-info', [ProductController::class, 'itemInfo']); // هندل کردن هم محصول و هم خدمت
Route::get('/products/ajax-list', [ProductController::class, 'ajaxList']);
Route::get('/services/ajax-list', [ServiceController::class, 'ajaxList']);

Route::get('/sales/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Protected Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/sales-data/{period}', [DashboardController::class, 'getSalesData']);

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Persons
    Route::prefix('persons')->name('persons.')->group(function () {
        Route::get('/', [PersonController::class, 'index'])->name('index');
        Route::get('/create', [PersonController::class, 'create'])->name('create');
        Route::post('/store', [PersonController::class, 'store'])->name('store');
        Route::get('/customers', [PersonController::class, 'customers'])->name('customers');
        Route::get('/suppliers', [PersonController::class, 'suppliers'])->name('suppliers');
        Route::get('/api/persons/next-code', [PersonController::class, 'getNextCode'])->name('persons.next-code');
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SalesController::class, 'index'])->name('index');
        Route::get('/create', [SalesController::class, 'create'])->name('create');
        Route::post('/', [SalesController::class, 'store'])->name('store');
        Route::get('/returns', [SalesController::class, 'returns'])->name('returns');
        Route::post('/returns', [SalesController::class, 'storeReturn'])->name('returns.store');
    });

    // Accounting
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/journal', [AccountingController::class, 'journal'])->name('journal');
        Route::get('/ledger', [AccountingController::class, 'ledger'])->name('ledger');
        Route::get('/balance', [AccountingController::class, 'balance'])->name('balance');
    });

    // Financial
    Route::prefix('financial')->name('financial.')->group(function () {
        Route::get('/income', [FinancialController::class, 'income'])->name('income');
        Route::get('/expenses', [FinancialController::class, 'expenses'])->name('expenses');
        Route::get('/banking', [FinancialController::class, 'banking'])->name('banking');
        Route::get('/cheques', [FinancialController::class, 'cheques'])->name('cheques');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/company', [SettingsController::class, 'company'])->name('company');
        Route::get('/users', [SettingsController::class, 'users'])->name('users');
    });

    // Products and Categories
    Route::resource('products', ProductController::class);
    Route::post('/products/upload', [ProductController::class, 'upload'])->name('products.upload');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::get('categories/list', [CategoryController::class, 'apiList']);

    Route::resource('services', ServiceController::class);

    // Stock Management
    Route::prefix('stocks')->name('stocks.')->group(function () {
        Route::get('/in', [StockController::class, 'in'])->name('in');
        Route::get('/out', [StockController::class, 'out'])->name('out');
        Route::get('/transfer', [StockController::class, 'transfer'])->name('transfer');
    });

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/next-number', [InvoiceController::class, 'getNextNumber'])->name('invoices.next-number');
    Route::get('/api/invoices/next-number', [InvoiceController::class, 'getNextNumber']);
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    Route::resource('pre-invoices', PreInvoiceController::class);
    Route::get('/quick-sale', [QuickSaleController::class, 'index'])->name('quick-sale');

    // Other Resources
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
    Route::post('/units', [UnitController::class, 'store'])->name('units.store');

    // Currencies

    // Sellers
    Route::prefix('sellers')->name('sellers.')->group(function () {
        Route::get('/create', [SellerController::class, 'create'])->name('create');
        Route::post('/store', [SellerController::class, 'store'])->name('store');
        Route::get('/next-code', [SellerController::class, 'nextCode'])->name('next-code');
        Route::get('/', [SellerController::class, 'index'])->name('index');
        Route::get('/{seller}', [SellerController::class, 'show'])->name('show');
        Route::get('/{seller}/edit', [SellerController::class, 'edit'])->name('edit');
        Route::put('/{seller}', [SellerController::class, 'update'])->name('update');
        Route::delete('/{seller}', [SellerController::class, 'destroy'])->name('destroy');
    });

    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices/store', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/sellers/list', [SellerController::class, 'list'])->name('sellers.list');

});

// API Routes
Route::get('/api/persons/search', [PersonController::class, 'searchAjax'])->name('persons.search.ajax');
Route::get('/api/sellers/list', [SellerController::class, 'list'])->name('sellers.list');
Route::get('/api/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/categories/person-search', [CategoryController::class, 'personSearch'])->name('categories.person-search');
Route::get('/provinces/{province}/cities', [ProvinceController::class, 'cities'])->name('provinces.cities');
Route::get('shareholders', [ShareholderController::class, 'index'])->name('shareholders.index');


Route::get('/currencies', [CurrencyController::class, 'index'])->name('currencies.index');
Route::post('/currencies', [CurrencyController::class, 'store'])->name('currencies.store');
Route::put('/currencies/{currency}', [CurrencyController::class, 'update'])->name('currencies.update');
Route::delete('/currencies/{currency}', [CurrencyController::class, 'destroy'])->name('currencies.destroy');






Route::get('/sales/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
Route::get('/sales/newform', [InvoiceController::class, 'newForm'])->name('sales.newform');


Route::get('/sales/newform', function () {
    return view('sales.create', [
        'sellers' => \App\Models\Seller::all(),
        'products' => \App\Models\Product::all(), // اضافه کردن لیست محصولات
        'currencies' => \App\Models\Currency::all(),
    ]);
})->name('sales.newform');

Route::get('/customers/search', function (Request $request) {
    $q = $request->get('q');

    $results = Person::query()
        ->where('first_name', 'LIKE', "%$q%")
        ->orWhere('last_name', 'LIKE', "%$q%")
        ->orWhere('nickname', 'LIKE', "%$q%")
        ->orWhere('company_name', 'LIKE', "%$q%")
        ->orWhere('accounting_code', 'LIKE', "%$q%") // اضافه کردن کد حسابداری به جستجو
        ->limit(10)
        ->get(['id', DB::raw("CONCAT(first_name, ' ', last_name) as name")]);

    return response()->json($results);
});

Route::get('/api/customers/search', function(Request $request) {
    $q = $request->get('q');
    $results = Person::query()
        ->where('title', 'LIKE', "%$q%")
        ->orWhere('company_name', 'LIKE', "%$q%")
        ->limit(10)
        ->get(['id', 'title as name']);
    return response()->json($results);
})->middleware(['web', 'auth']); // یا فقط 'web' اگر احراز هویت نمی‌خواهی

Route::resource('persons', \App\Http\Controllers\PersonController::class);
Route::get('persons/next-code', [PersonController::class, 'nextCode'])->name('persons.next-code');

Route::resource('sales', SaleController::class);
Route::get('/api/invoices/next-number', [\App\Http\Controllers\SaleController::class, 'nextInvoiceNumber']);

// ایجکس محصولات و خدمات
Route::get('/products/ajax-list', [\App\Http\Controllers\ProductController::class, 'ajaxList']);
Route::get('/services/ajax-list', [\App\Http\Controllers\ServiceController::class, 'ajaxList']);



// برای دسته‌بندی‌ها اگر لازم است
Route::get('/api/categories', [CategoryController::class, 'apiList']);





require __DIR__.'/auth.php';
