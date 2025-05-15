<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Ajax list for products with category filter and search.
     * Route: /products/ajax-list
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
     * Route: /sales/item-info?id=...&type=product
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
            'code' => $service->code,
            'name' => $service->name,
            'image' => $service->image,
            'stock' => $service->stock,
            'sell_price' => $service->sell_price,
            'category' => $service->category->name ?? '-',
            'unit' => $service->unit ?? '-',
        ]);
    }

    return response()->json(['error' => 'Invalid type'], 400);
}
}
