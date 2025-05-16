<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function index(Request $request)
    {

        $query = Sale::query()->with(['customer', 'seller']);
        // اعمال فیلترها
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('customer')) {
            $query->where('customer_id', $request->customer);
        }

        if ($request->filled('seller')) {
            $query->where('seller_id', $request->seller);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $start = Carbon::createFromFormat('Y/m/d', trim($dates[0]))->startOfDay();
                $end = Carbon::createFromFormat('Y/m/d', trim($dates[1]))->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        if ($request->filled('price_min')) {
            $query->where('total_price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('total_price', '<=', $request->price_max);
        }

        // مرتب‌سازی
        $sortField = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        // تعداد آیتم در هر صفحه
        $perPage = $request->per_page ?? 10;

        // دریافت داده‌ها
        $sales = $query->paginate($perPage)->withQueryString();

        // محاسبه آمار
        $totalSales = $query->sum('total_price');
        $salesCount = $query->count();
        $averageSale = $salesCount > 0 ? $totalSales / $salesCount : 0;
        $todaySales = $query->whereDate('created_at', Carbon::today())->sum('total_price');

        // دریافت لیست‌های مورد نیاز برای فیلترها
        $customers = Person::whereHas('sales')->get();
        $sellers = Seller::whereHas('sales')->get();

        return view('sales.index', compact(
            'sales',
            'customers',
            'sellers',
            'totalSales',
            'salesCount',
            'averageSale',
            'todaySales'
        ));
    }

    public function create()
    {
        $sellers = Seller::all();
        $products = Product::with('category')->get();
        $currencies = Currency::all();
        $customers = Person::all();

        // شماره پیشنهادی برای اولین بار
        $nextNumber = $this->generateNextInvoiceNumber();

        return view('sales.create', compact(
            'sellers',
            'products',
            'currencies',
            'customers',
            'nextNumber'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|unique:sales,invoice_number',
            'customer_id' => 'required|exists:persons,id',
            'seller_id' => 'required|exists:sellers,id',
            'currency_id' => 'required|exists:currencies,id',
            'products_input' => 'required',
        ], [
            'invoice_number.required' => 'شماره فاکتور الزامی است.',
            'invoice_number.unique' => 'این شماره فاکتور قبلاً ثبت شده است.',
            'customer_id.required' => 'انتخاب مشتری الزامی است.',
            'seller_id.required' => 'انتخاب فروشنده الزامی است.',
            'currency_id.required' => 'انتخاب واحد پول الزامی است.',
            'products_input.required' => 'حداقل یک محصول یا خدمت به فاکتور اضافه کنید.',
        ]);

        $items = json_decode($request->products_input, true);
        if (empty($items)) {
            return back()->withInput()->withErrors(['products' => 'هیچ محصولی انتخاب نشده است.']);
        }

        DB::beginTransaction();
        try {
            // محاسبه مبلغ کل فاکتور
            $totalPrice = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            foreach ($items as $item) {
                $count = intval($item['count']);
                $unitPrice = intval($item['sell_price']);
                $discount = floatval($item['discount'] ?? 0);
                $tax = floatval($item['tax'] ?? 0);

                $subtotal = $count * $unitPrice;
                $itemDiscount = $discount;
                $itemTax = ($subtotal - $itemDiscount) * ($tax / 100);

                $totalPrice += $subtotal;
                $totalDiscount += $itemDiscount;
                $totalTax += $itemTax;

                // کم کردن موجودی محصول
                $product = Product::find($item['id']);
                if ($product && $product->type === 'product') {
                    if($product->stock < $count) {
                        throw new \Exception("موجودی محصول '{$product->name}' کافی نیست.");
                    }
                    $product->stock -= $count;
                    $product->save();
                }
            }

            // ایجاد فاکتور
            $sale = Sale::create([
                'invoice_number' => $request->invoice_number,
                'reference' => $request->reference,
                'customer_id' => $request->customer_id,
                'seller_id' => $request->seller_id,
                'currency_id' => $request->currency_id,
                'title' => $request->title,
                'issued_at' => Carbon::now(),
                'total_price' => $totalPrice,
                'discount' => $totalDiscount,
                'tax' => $totalTax,
                'status' => 'pending'
            ]);

            // ذخیره اقلام فاکتور
            foreach ($items as $item) {
                $count = intval($item['count']);
                $unitPrice = intval($item['sell_price']);
                $discount = floatval($item['discount'] ?? 0);
                $tax = floatval($item['tax'] ?? 0);

                $subtotal = $count * $unitPrice;
                $itemDiscount = $discount;
                $itemTax = ($subtotal - $itemDiscount) * ($tax / 100);
                $total = $subtotal - $itemDiscount + $itemTax;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'description' => $item['desc'] ?? '',
                    'unit' => $item['unit'] ?? '',
                    'quantity' => $count,
                    'unit_price' => $unitPrice,
                    'discount' => $itemDiscount,
                    'tax' => $itemTax,
                    'total' => $total
                ]);
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'فاکتور با موفقیت ثبت شد.');
        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $ex->getMessage()]);
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'seller', 'items.product', 'currency']);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        if ($sale->status !== 'pending') {
            return redirect()->route('sales.show', $sale)
                           ->with('error', 'فقط فاکتورهای در انتظار پرداخت قابل ویرایش هستند.');
        }

        $sale->load(['customer', 'seller', 'items.product', 'currency']);
        $sellers = Seller::all();
        $products = Product::with('category')->get();
        $currencies = Currency::all();
        $customers = Person::all();

        return view('sales.edit', compact('sale', 'sellers', 'products', 'currencies', 'customers'));
    }

    public function update(Request $request, Sale $sale)
    {
        if ($sale->status !== 'pending') {
            return redirect()->route('sales.show', $sale)
                           ->with('error', 'فقط فاکتورهای در انتظار پرداخت قابل ویرایش هستند.');
        }

        $request->validate([
            'invoice_number' => 'required|unique:sales,invoice_number,' . $sale->id,
            'customer_id' => 'required|exists:persons,id',
            'seller_id' => 'required|exists:sellers,id',
            'currency_id' => 'required|exists:currencies,id',
            'products_input' => 'required',
        ]);

        $items = json_decode($request->products_input, true);
        if (empty($items)) {
            return back()->withInput()->withErrors(['products' => 'حداقل یک محصول یا خدمت به فاکتور اضافه کنید.']);
        }

        DB::beginTransaction();
        try {
            // برگرداندن موجودی محصولات قبلی
            foreach ($sale->items as $item) {
                if ($item->product && $item->product->type === 'product') {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // محاسبه مجدد مبلغ کل
            $totalPrice = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            foreach ($items as $item) {
                $count = intval($item['count']);
                $unitPrice = intval($item['sell_price']);
                $discount = floatval($item['discount'] ?? 0);
                $tax = floatval($item['tax'] ?? 0);

                $subtotal = $count * $unitPrice;
                $itemDiscount = $discount;
                $itemTax = ($subtotal - $itemDiscount) * ($tax / 100);

                $totalPrice += $subtotal;
                $totalDiscount += $itemDiscount;
                $totalTax += $itemTax;

                // بررسی و کم کردن موجودی جدید
                $product = Product::find($item['id']);
                if ($product && $product->type === 'product') {
                    if($product->stock < $count) {
                        throw new \Exception("موجودی محصول '{$product->name}' کافی نیست.");
                    }
                    $product->stock -= $count;
                    $product->save();
                }
            }

            // به‌روزرسانی فاکتور
            $sale->update([
                'invoice_number' => $request->invoice_number,
                'reference' => $request->reference,
                'customer_id' => $request->customer_id,
                'seller_id' => $request->seller_id,
                'currency_id' => $request->currency_id,
                'title' => $request->title,
                'total_price' => $totalPrice,
                'discount' => $totalDiscount,
                'tax' => $totalTax
            ]);

            // حذف اقلام قبلی
            $sale->items()->delete();

            // ذخیره اقلام جدید
            foreach ($items as $item) {
                $count = intval($item['count']);
                $unitPrice = intval($item['sell_price']);
                $discount = floatval($item['discount'] ?? 0);
                $tax = floatval($item['tax'] ?? 0);

                $subtotal = $count * $unitPrice;
                $itemDiscount = $discount;
                $itemTax = ($subtotal - $itemDiscount) * ($tax / 100);
                $total = $subtotal - $itemDiscount + $itemTax;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'description' => $item['desc'] ?? '',
                    'unit' => $item['unit'] ?? '',
                    'quantity' => $count,
                    'unit_price' => $unitPrice,
                    'discount' => $itemDiscount,
                    'tax' => $itemTax,
                    'total' => $total
                ]);
            }

            DB::commit();
            return redirect()->route('sales.show', $sale)->with('success', 'فاکتور با موفقیت به‌روزرسانی شد.');
        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $ex->getMessage()]);
        }
    }

    public function destroy(Sale $sale)
    {
        if ($sale->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'فقط فاکتورهای در انتظار پرداخت قابل حذف هستند.'
            ]);
        }

        DB::beginTransaction();
        try {
            // برگرداندن موجودی محصولات
            foreach ($sale->items as $item) {
                if ($item->product && $item->product->type === 'product') {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // حذف فاکتور و اقلام آن
            $sale->items()->delete();
            $sale->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'فاکتور با موفقیت حذف شد.'
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف فاکتور: ' . $ex->getMessage()
            ]);
        }
    }

    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,completed,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled'
        ]);

        $sale->status = $request->status;
        if ($request->status === 'cancelled') {
            $sale->cancellation_reason = $request->cancellation_reason;
        } elseif ($request->status === 'paid') {
            $sale->paid_at = now();
            $sale->payment_method = $request->payment_method;
            $sale->payment_reference = $request->payment_reference;
        }
        $sale->save();

        return redirect()->route('sales.show', $sale)
                        ->with('success', 'وضعیت فاکتور با موفقیت به‌روزرسانی شد.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:sales,id'
        ]);

        DB::beginTransaction();
        try {
            $sales = Sale::whereIn('id', $request->ids)
                        ->where('status', 'pending')
                        ->with('items.product')
                        ->get();

            foreach ($sales as $sale) {
                // برگرداندن موجودی محصولات
                foreach ($sale->items as $item) {
                    if ($item->product && $item->product->type === 'product') {
                        $item->product->increment('stock', $item->quantity);
                    }
                }

                // حذف فاکتور و اقلام آن
                $sale->items()->delete();
                $sale->delete();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => count($sales) . ' فاکتور با موفقیت حذف شد.'
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف فاکتورها: ' . $ex->getMessage()
            ]);
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:excel,pdf,csv',
            'date_range' => 'nullable|string',
            'selected_ids' => 'nullable|string'
        ]);

        $query = Sale::with(['customer', 'seller', 'items.product', 'currency']);

        // فیلتر بر اساس آیدی‌های انتخاب شده
        if ($request->filled('selected_ids')) {
            $ids = explode(',', $request->selected_ids);
            $query->whereIn('id', $ids);
        }

        // فیلتر تاریخ
        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                $start = Carbon::createFromFormat('Y/m/d', trim($dates[0]))->startOfDay();
                $end = Carbon::createFromFormat('Y/m/d', trim($dates[1]))->endOfDay();
                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        $sales = $query->get();

        // ایجاد خروجی بر اساس فرمت درخواستی
        switch ($request->format) {
            case 'excel':
                return Excel::download(new SalesExport($sales), 'sales.xlsx');
            case 'pdf':
                $pdf = PDF::loadView('exports.sales-pdf', compact('sales'));
                return $pdf->download('sales.pdf');
            case 'csv':
                return Excel::download(new SalesExport($sales), 'sales.csv');
        }
    }

    protected function generateNextInvoiceNumber()
    {
        $lastSale = Sale::where('invoice_number', 'like', 'INV-%')
                       ->orderByRaw('CAST(SUBSTRING(invoice_number, 5) AS UNSIGNED) DESC')
                       ->first();

        if ($lastSale && preg_match('/INV-(\d+)/', $lastSale->invoice_number, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1001;
        }

        return 'INV-' . $number;
    }
}
