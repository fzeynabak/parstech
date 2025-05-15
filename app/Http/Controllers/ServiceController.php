<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Ajax list for services with category filter and search.
     * Route: /services/ajax-list
     */
    public function ajaxList(Request $request)
    {
        $query = Service::with('category')
            ->whereHas('category', function($q) {
                $q->where('category_type', 'service');
            });

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function($q) use ($search){
                $q->where('name', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%");
            });
        }

        $services = $query->limit($request->input('limit', 10))->get();

        $data = $services->map(function($item){
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
     * دریافت اطلاعات یک خدمت (برای افزودن به سبد خرید)
     * Route: /sales/item-info?id=...&type=service
     */
    public function itemInfo(Request $request)
    {
        $id = $request->input('id');
        $service = Service::with('category')->where('id', $id)
            ->whereHas('category', function($q) {
                $q->where('category_type', 'service');
            })
            ->firstOrFail();

        return response()->json([
            'id' => $service->id,
            'code' => $service->code,
            'name' => $service->name,
            'image' => $service->image,
            'stock' => $service->stock,
            'sell_price' => $service->sell_price,
            'category' => $service->category->name ?? '-',
            'category_type' => $service->category->category_type ?? '-',
            'unit' => $service->unit ?? '-',
        ]);
    }
}
