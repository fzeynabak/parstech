<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['seller', 'customer', 'items.product'])->orderByDesc('id')->paginate(20);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $sellers = Seller::all();
        $products = Product::all();
        $currencies = Currency::all();
        $last = Sale::orderByDesc('id')->first();
        $nextNumber = 'invoices-10001';
        if ($last && preg_match('/invoices-(\d+)/', $last->invoice_number, $m)) {
            $nextNumber = 'invoices-' . (intval($m[1]) + 1);
        }
        return view('sales.create', compact('sellers', 'products', 'currencies', 'nextNumber'));
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
            'customer_id.required' => 'مشتری را انتخاب کنید.',
            'seller_id.required' => 'فروشنده را انتخاب کنید.',
            'currency_id.required' => 'واحد پول را انتخاب کنید.',
            'products_input.required' => 'حداقل یک محصول یا خدمت به فاکتور اضافه کنید.',
        ]);

        $issued_at = now();
        $items = json_decode($request->products_input, true);
        if (empty($items)) {
            return back()->withInput()->withErrors(['products' => 'هیچ محصولی انتخاب نشده است.']);
        }

        DB::beginTransaction();
        try {
            // محاسبه مبلغ کل فاکتور
            $total_price = 0;
            foreach ($items as $item) {
                $count = intval($item['count']);
                $unit_price = intval($item['sell_price']);
                $discount = floatval($item['discount'] ?? 0);
                $tax = floatval($item['tax'] ?? 0);
                $subtotal = $count * $unit_price - $discount;
                if ($tax > 0) $subtotal += ($subtotal * $tax / 100);
                $total_price += $subtotal;

                // کم کردن موجودی محصول
                $product = Product::find($item['id']);
                if ($product) {
                    if($product->stock < $count) {
                        throw new \Exception("موجودی محصول '{$product->name}' کافی نیست.");
                    }
                    $product->stock -= $count;
                    $product->save();
                }
            }

            // ایجاد Sale
            $sale = Sale::create([
                'invoice_number' => $request->invoice_number,
                'reference' => $request->reference,
                'customer_id' => $request->customer_id,
                'seller_id' => $request->seller_id,
                'currency_id' => $request->currency_id,
                'title' => $request->title,
                'issued_at' => $issued_at,
                'total_price' => $total_price,
            ]);

            // ذخیره اقلام
            foreach ($items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'description' => $item['desc'] ?? '',
                    'unit' => $item['unit'] ?? '',
                    'quantity' => $item['count'],
                    'unit_price' => $item['sell_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => (($item['count'] * $item['sell_price']) - ($item['discount'] ?? 0)) + ((($item['count'] * $item['sell_price']) - ($item['discount'] ?? 0)) * ($item['tax'] ?? 0) / 100),
                ]);
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'فاکتور با موفقیت ثبت شد.');
        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $ex->getMessage()]);
        }
    }
}
