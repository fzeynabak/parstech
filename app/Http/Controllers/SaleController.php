<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\Request;
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
            'products_input' => 'required',
        ], [
            'customer_id.required' => 'مشتری را انتخاب کنید.',
            'seller_id.required' => 'فروشنده را انتخاب کنید.',
            'currency_id.required' => 'واحد پول را انتخاب کنید.',
            'products_input.required' => 'حداقل یک محصول یا خدمت به فاکتور اضافه کنید.',
        ]);

        // تاریخ صدور را همین لحظه بگیر و هرگز از ورودی نفرست
        $issued_at = now();

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
                'total_price' => 0,
            ]);

            $items = json_decode($request->products_input, true);
            if (empty($items)) throw new \Exception("هیچ محصول یا خدمتی برای ثبت وجود ندارد.");

            // ادامه منطق ثبت اقلام و غیره...

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'فاکتور با موفقیت ثبت شد.');
        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $ex->getMessage()]);
        }
    }
}
