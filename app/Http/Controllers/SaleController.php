<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class SaleController extends Controller
{
    public function create()
    {
        $sellers = Seller::all();
        $products = Product::all();
        $currencies = Currency::all();
        // مقدار پیش‌فرض شماره بعدی فاکتور و غیره را اینجا اضافه کن
        return view('sales.create', compact('sellers', 'products', 'currencies'));
    }

    public function store(Request $request)
    {
        // اعتبارسنجی و تبدیل تاریخ شمسی به میلادی
        $request->validate([
            'invoice_number' => 'required',
            'customer_id' => 'required|exists:customers,id',
            'seller_id' => 'required|exists:sellers,id',
            'currency_id' => 'required|exists:currencies,id',
            'issued_at_jalali' => 'required',
            'due_at_jalali' => 'required',
            // سایر فیلدها
        ]);

        // تبدیل تاریخ شمسی به میلادی با Morilog/Jalali
        $issued_at = Jalalian::fromFormat('Y/m/d', $request->issued_at_jalali)->toCarbon();
        $due_at = Jalalian::fromFormat('Y/m/d', $request->due_at_jalali)->toCarbon();

        // ذخیره فاکتور (نمونه)
        // Sale::create([...]);
        // ...

        return redirect()->route('sales.index')->with('success', 'فاکتور با موفقیت ثبت شد.');
    }
    public function nextInvoiceNumber()
    {
        $last = \App\Models\Sale::orderByDesc('id')->first();
        $number = 'invoices-10001';
        if ($last && preg_match('/invoices-(\d+)/', $last->invoice_number, $m)) {
            $number = 'invoices-' . (intval($m[1]) + 1);
        }
        return response()->json(['number' => $number]);
    }
}
