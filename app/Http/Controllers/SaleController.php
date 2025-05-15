<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Person;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
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
            'invoice_number' => 'required',
            'customer_id' => 'required|exists:persons,id',
            'seller_id' => 'required|exists:sellers,id',
            'currency_id' => 'required|exists:currencies,id',
            'issued_at_jalali' => 'required',
            'due_at_jalali' => 'required',
            'products_input' => 'required',
        ], [
            'customer_id.required' => 'مشتری را انتخاب کنید.',
            'seller_id.required' => 'فروشنده را انتخاب کنید.',
            'currency_id.required' => 'واحد پول را انتخاب کنید.',
            'products_input.required' => 'حداقل یک محصول یا خدمت به فاکتور اضافه کنید.',
        ]);

        // کنترل مقدار تاریخ و تبدیل با هندل خطا
        if (empty($request->issued_at_jalali) || empty($request->due_at_jalali)) {
            return back()->withInput()->withErrors(['dates' => 'تاریخ فاکتور یا تاریخ سررسید وارد نشده است.']);
        }
        try {
            $issued_at = Jalalian::fromFormat('Y/m/d', $request->issued_at_jalali)->toCarbon();
            $due_at = Jalalian::fromFormat('Y/m/d', $request->due_at_jalali)->toCarbon();
        } catch (\Exception $ex) {
            return back()->withInput()->withErrors(['dates' => 'فرمت تاریخ وارد شده صحیح نیست. لطفاً مجدداً انتخاب کنید.']);
        }

        DB::beginTransaction();
        try {
            $sale = Sale::create([
                'invoice_number' => $request->invoice_number,
                'reference' => $request->reference,
                'customer_id' => $request->customer_id,
                'seller_id' => $request->seller_id,
                'currency_id' => $request->currency_id,
                'title' => $request->title,
                'issued_at' => $issued_at,
                'due_at' => $due_at,
                'total_price' => 0,
            ]);

            $items = json_decode($request->products_input, true);
            if (empty($items)) throw new \Exception("هیچ محصول یا خدمتی برای ثبت وجود ندارد.");

            $total = 0;
            foreach ($items as $item) {
                $itemTotal = ($item['count'] * $item['sell_price']) - ($item['discount'] ?? 0);
                $itemTotal += (($item['tax'] ?? 0) / 100) * (($item['count'] * $item['sell_price']) - ($item['discount'] ?? 0));
                $total += $itemTotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'description' => $item['desc'] ?? '',
                    'unit' => $item['unit'] ?? '',
                    'quantity' => $item['count'],
                    'unit_price' => $item['sell_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            $sale->total_price = $total;
            $sale->save();

            $customer = Person::find($request->customer_id);
            $customer->last_purchase_at = now();
            $customer->total_purchases = ($customer->total_purchases ?? 0) + $total;
            $customer->save();

            DB::commit();
            $nextNumber = 'invoices-' . (intval(preg_replace('/invoices-/', '', $sale->invoice_number)) + 1);

            return redirect()->route('sales.create')->with(['success' => 'فاکتور با موفقیت ثبت شد.', 'nextNumber' => $nextNumber]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'خطا در ثبت فاکتور: '.$e->getMessage()]);
        }
    }

    public function nextInvoiceNumber()
    {
        $last = Sale::orderByDesc('id')->first();
        $number = 'invoices-10001';
        if ($last && preg_match('/invoices-(\d+)/', $last->invoice_number, $m)) {
            $number = 'invoices-' . (intval($m[1]) + 1);
        }
        return response()->json(['number' => $number]);
    }
}
