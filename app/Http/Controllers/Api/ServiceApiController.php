<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;

class ServiceApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('category')->where('is_active', 1);

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

        $services = $query->orderBy('name')->paginate(10);

        $services->getCollection()->transform(function($s){
            return [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'category_id' => $s->category_id,
                'category' => $s->category ? $s->category->name : ''
            ];
        });

        return response()->json($services);
    }
}
