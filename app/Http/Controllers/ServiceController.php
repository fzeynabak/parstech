<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * لیست خدمات
     */
    public function index()
    {
        $serviceCategories = Category::where('category_type', 'service')->get();
        $services = Service::latest()->paginate(20);
        return view('services.index', compact('services', 'serviceCategories'));
    }

    public function ajaxList(Request $request)
    {
        $query = Service::with('category')->where('is_active', 1);

        if ($request->has('q') && $request->q) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%$q%")
                    ->orWhere('service_code', 'like', "%$q%");
            });
        }

        $limit = intval($request->get('limit', 10));
        $services = $query->limit($limit)->get();

        $results = $services->map(function ($service) {
            return [
                'id'         => $service->id,
                'code'       => $service->service_code,
                'name'       => $service->title,
                'category'   => $service->category ? $service->category->name : '-',
                'unit'       => $service->unit,
                'sell_price' => $service->price,
                'description'=> $service->short_description ?? $service->description,
                'stock'      => 1, // خدمات موجودی ندارد، فقط برای سازگاری با کد JS
            ];
        });

        return response()->json($results);
    }


    public function nextCode()
    {
        $last = Service::where('service_code', 'like', 'ser%')
            ->orderByRaw('CAST(SUBSTRING(service_code, 4) AS UNSIGNED) DESC')
            ->first();
        if($last && preg_match('/^ser(\d+)$/', $last->service_code, $m)) {
            $next = intval($m[1]) + 1;
        } else {
            $next = 10001;
        }
        return response()->json(['code' => 'ser' . $next]);
    }

    /**
     * نمایش فرم افزودن خدمت جدید
     */
    public function create()
    {
        $serviceCategories = Category::where('category_type', 'service')->get();
        $units = Service::select('unit')->distinct()->pluck('unit')->toArray();
        if (empty($units)) {
            $units = ['ساعت', 'روز', 'عدد'];
        }
        return view('services.create', compact('serviceCategories', 'units'));
    }

    /**
     * ثبت خدمت جدید
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'service_code' => 'required|string|max:255|unique:services,service_code',
            'service_category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'execution_cost' => 'nullable|numeric',
            'short_description' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
            'is_vat_included' => 'nullable|boolean',
            'is_discountable' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_vat_included'] = $request->has('is_vat_included');
        $validated['is_discountable'] = $request->has('is_discountable');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        Service::create($validated);

        return redirect()->route('services.index')->with('success', 'خدمات با موفقیت ثبت شد.');
    }

    /**
     * نمایش فرم ویرایش خدمت
     */
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        $serviceCategories = Category::where('category_type', 'service')->get();
        $units = Service::select('unit')->distinct()->pluck('unit')->toArray();
        if (empty($units)) {
            $units = ['ساعت', 'روز', 'عدد'];
        }
        return view('services.edit', compact('service', 'serviceCategories', 'units'));
    }

    /**
     * ویرایش خدمت
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'title'        => 'required|string|max:255',
            'service_code' => 'required|string|max:255|unique:services,service_code,' . $service->id,
            'service_category_id' => 'nullable|exists:categories,id',
            'unit'        => 'nullable|string|max:255',
            'price'       => 'nullable|numeric',
            'is_active'   => 'nullable|boolean',
        ]);

        $data = $request->only([
            'title', 'service_code', 'service_category_id', 'unit', 'price', 'is_active'
        ]);
        if (!isset($data['is_active'])) $data['is_active'] = true;

        $service->update($data);

        return redirect()->route('services.index')->with('success', 'خدمت با موفقیت ویرایش شد.');
    }

    /**
     * حذف خدمت
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return redirect()->route('services.index')->with('success', 'خدمت با موفقیت حذف شد.');
    }
}
