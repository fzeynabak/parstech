<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryApiController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $query = Category::query();
        if ($type) {
            $query->where('category_type', $type);
        }
        $categories = $query->orderBy('name')->get([
            'id', 'name', 'code', 'category_type', 'parent_id', 'description', 'image'
        ]);
        return response()->json($categories);
    }
}
