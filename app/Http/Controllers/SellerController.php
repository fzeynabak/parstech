<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index()
    {
        $sellers = Seller::paginate(10);
        return view('sellers.index', compact('sellers'));
    }

    public function create()
    {
        $nextCode = $this->generateNextSellerCode();
        return view('sellers.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'seller_code' => 'required|unique:sellers,seller_code|max:255',
            'first_name' => 'nullable|max:255',
            'last_name' => 'nullable|max:255',
            'company_name' => 'nullable|max:255',
            'mobile' => 'nullable|max:15',
        ]);

        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('sellers', 'public');
        }

        Seller::create($data);

        return redirect()->route('sellers.index')->with('success', 'فروشنده جدید با موفقیت اضافه شد.');
    }

    public function edit(Seller $seller)
    {
        return view('sellers.edit', compact('seller'));
    }

    public function update(Request $request, Seller $seller)
    {
        $request->validate([
            'seller_code' => "required|unique:sellers,seller_code,{$seller->id}|max:255",
            'first_name' => 'nullable|max:255',
            'last_name' => 'nullable|max:255',
            'company_name' => 'nullable|max:255',
            'mobile' => 'nullable|max:15',
        ]);

        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('sellers', 'public');
        }

        $seller->update($data);

        return redirect()->route('sellers.index')->with('success', 'اطلاعات فروشنده با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Seller $seller)
    {
        $seller->delete();
        return redirect()->route('sellers.index')->with('success', 'فروشنده با موفقیت حذف شد.');
    }

    private function generateNextSellerCode()
    {
        $lastSeller = Seller::latest('id')->first();
        $nextCode = $lastSeller ? intval(substr($lastSeller->seller_code, 7)) + 1 : 10001;
        return 'Seller-' . $nextCode;
    }
}
