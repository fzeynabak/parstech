<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', 1);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('code', 'LIKE', "%$search%");
            });
        }

        $products = $query->orderBy('name')->paginate(10);

        $products->getCollection()->transform(function($p){
            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'category_id' => $p->category_id,
                'category' => $p->category ? $p->category->name : '',
                'image' => $p->image ? asset('storage/' . $p->image) : asset('img/product-default.png')
            ];
        });

        return response()->json($products);
    }
}
