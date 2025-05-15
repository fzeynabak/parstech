<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

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
            'products_input' => 'required',
        ], [
            'customer_id.required' => 'مشتری را انتخاب کنید.',
            'seller_id.required' => 'فروشنده را انتخاب کنید.',
            'currency_id.required' => 'واحد پول را انتخاب کنید.',
            'products_input.required' => 'حداقل یک محصول یا خدمت به فاکتور اضافه کنید.',
        ]);

        // کنترل مقدار تاریخ و تبدیل با هندل خطا
        if (empty($request->issued_at_jalali)) {
            return back()->withInput()->withErrors(['dates' => 'تاریخ فاکتور وارد نشده است.']);
        }
        try {
            $issued_at = Jalalian::fromFormat('Y/m/d', $request->issued_at_jalali)->toCarbon();
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
                'total_price' => 0,
            ]);

            $items = json_decode($request->products_input, true);
            if (empty($items)) throw new \Exception("هیچ محصول یا خدمتی برای ثبت وجود ندارد.");

            // ادامه منطق ذخیره محصولات و ... طبق قبل

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'فاکتور با موفقیت ثبت شد.');
        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $ex->getMessage()]);
        }
    }
}
