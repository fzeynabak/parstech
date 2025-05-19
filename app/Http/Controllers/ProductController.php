<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Service;

class ProductController extends Controller
{
    /**
     * نمایش لیست محصولات
     */
    public function index()
    {
        $products = Product::with(['category', 'brand'])->orderBy('id', 'desc')->paginate(20);
        return view('products.index', compact('products'));
    }

    /**
     * نمایش فرم افزودن محصول جدید
     */
    public function create()
    {
        $lastProduct = Product::orderBy('id', 'desc')->first();
        $default_code = $lastProduct ? str_pad($lastProduct->id + 1, 4, '0', STR_PAD_LEFT) : '0001';
        $categories = Category::where('category_type', 'product')->get();
        $brands = Brand::all();
        $units = Unit::all();
        return view('products.create', compact('default_code', 'categories', 'brands', 'units'));
    }

    /**
     * ذخیره محصول جدید
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:255|unique:products,code',
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',
            'unit'        => 'nullable|string|max:255',
            'stock'       => 'nullable|numeric',
            'min_stock'   => 'nullable|numeric',
            'weight'      => 'nullable|numeric',
            'buy_price'   => 'nullable|numeric',
            'sell_price'  => 'nullable|numeric',
            'discount'    => 'nullable|numeric',
            'barcode'     => 'nullable|string|max:255',
            'store_barcode' => 'nullable|string|max:255',
            'short_desc'  => 'nullable|string',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'video'       => 'nullable|file|mimes:mp4,avi,mov|max:10240',
        ]);

        $data = $request->only([
            'name', 'code', 'category_id', 'brand_id', 'unit', 'stock', 'min_stock', 'weight', 'buy_price',
            'sell_price', 'discount', 'barcode', 'store_barcode', 'short_desc', 'description'
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('products/videos', 'public');
        }
        if (!isset($data['stock'])) $data['stock'] = 0;
        if (!isset($data['min_stock'])) $data['min_stock'] = 0;

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'محصول با موفقیت ثبت شد.');
    }

    /**
     * نمایش فرم ویرایش محصول
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('category_type', 'product')->get();
        $brands = Brand::all();
        $units = Unit::all();
        return view('products.edit', compact('product', 'categories', 'brands', 'units'));
    }

    /**
     * بروزرسانی محصول
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:255|unique:products,code,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',
            'unit'        => 'nullable|string|max:255',
            'stock'       => 'nullable|numeric',
            'min_stock'   => 'nullable|numeric',
            'weight'      => 'nullable|numeric',
            'buy_price'   => 'nullable|numeric',
            'sell_price'  => 'nullable|numeric',
            'discount'    => 'nullable|numeric',
            'barcode'     => 'nullable|string|max:255',
            'store_barcode' => 'nullable|string|max:255',
            'short_desc'  => 'nullable|string',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'video'       => 'nullable|file|mimes:mp4,avi,mov|max:10240',
        ]);

        $data = $request->only([
            'name', 'code', 'category_id', 'brand_id', 'unit', 'stock', 'min_stock', 'weight', 'buy_price',
            'sell_price', 'discount', 'barcode', 'store_barcode', 'short_desc', 'description'
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }
        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('products/videos', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'محصول با موفقیت ویرایش شد.');
    }

    /**
     * حذف محصول
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'محصول با موفقیت حذف شد.');
    }

    /**
     * Ajax list for products with category filter and search.
     */
    public function ajaxList(Request $request)
    {
        $query = Product::with('category')
            ->whereHas('category', function($q) {
                $q->where('category_type', 'product');
            });

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function($q) use ($search){
                $q->where('name', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%");
            });
        }

        $products = $query->limit($request->input('limit', 10))->get();

        $data = $products->map(function($item){
            return [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'image' => $item->image,
                'stock' => $item->stock,
                'sell_price' => $item->sell_price,
                'category' => $item->category->name ?? '-',
                'category_type' => $item->category->category_type ?? '-',
            ];
        });
        return response()->json($data);
    }

    /**
     * دریافت اطلاعات یک محصول (برای افزودن به سبد خرید)
     */
    public function itemInfo(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type');

        if ($type === 'product') {
            $product = Product::with('category')->findOrFail($id);
            return response()->json([
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'image' => $product->image,
                'stock' => $product->stock,
                'sell_price' => $product->sell_price,
                'category' => $product->category->name ?? '-',
                'unit' => $product->unit ?? '-',
            ]);
        } elseif ($type === 'service') {
            $service = Service::with('category')->findOrFail($id);
            return response()->json([
                'id' => $service->id,
                'code' => $service->service_code,
                'name' => $service->title,
                'image' => null,
                'stock' => 1, // خدمات همیشه 1 - فقط جهت سازگاری
                'sell_price' => $service->price,
                'category' => $service->category ? $service->category->name : '-',
                'unit' => $service->unit ?? '-',
                'description' => $service->short_description ?? $service->description,
            ]);
        }

        return response()->json(['error' => 'Invalid type'], 400);
    }
}
