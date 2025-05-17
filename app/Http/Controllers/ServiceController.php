<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Category;
use App\Models\Unit;
use App\Models\ServiceCategory;
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
        return view('services.index', compact('services'));
    }
    protected $fillable = ['name'];
    public function nextCode()
    {
        $last = Service::where('code', 'like', 'ser%')
            ->orderByRaw('CAST(SUBSTRING(code, 4) AS UNSIGNED) DESC')
            ->first();
        if($last && preg_match('/^ser(\d+)$/', $last->code, $m)) {
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
        $serviceCategories = ServiceCategory::all();
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
            'service_category_id' => 'nullable|exists:service_categories,id',
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
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:255|unique:services,code,' . $service->id,
            'category_id' => 'required|exists:categories,id',
            'unit'        => 'nullable|string|max:255',
            'price'       => 'nullable|numeric',
            'is_active'   => 'nullable|boolean',
        ]);

        $data = $request->only([
            'name', 'code', 'category_id', 'unit', 'price', 'is_active'
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
                'image' => $item->image ?? null,
                'stock' => $item->stock ?? null,
                'sell_price' => $item->price ?? null,
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
            'image' => $service->image ?? null,
            'stock' => $service->stock ?? null,
            'sell_price' => $service->price ?? null,
            'category' => $service->category->name ?? '-',
            'category_type' => $service->category->category_type ?? '-',
            'unit' => $service->unit ?? '-',
        ]);
    }
}
