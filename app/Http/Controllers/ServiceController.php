<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
     // ServiceController
public function create()
{
    // فقط دسته‌هایی که زیرمجموعه service هستند:
    $serviceCategories = \App\Models\Category::where('category_type', 'service')->get();

    // لیست واحدها (از دیتابیس یا ثابت)
    $units = ['عدد', 'ساعت', 'جلسه', 'مورد', 'بسته', 'پروژه', 'ماه'];

    return view('services.create', compact('serviceCategories', 'units'));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // اضافه به ServiceController
    public function ajaxList(Request $request)
    {
        $query = \App\Models\Service::with('category')->where('is_active', 1);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        } else {
            $catIds = \App\Models\Category::where('category_type', 'service')->pluck('id');
            $query->whereIn('category_id', $catIds);
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function($q2) use ($q) {
                $q2->where('name', 'like', "%$q%")
                    ->orWhere('code', 'like', "%$q%");
            });
        }

        $services = $query->limit(10)->get();
        return response()->json($services->map(function($s){
            return [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'category' => $s->category ? $s->category->name : ''
            ];
        }));
    }

}
