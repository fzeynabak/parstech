<?php

namespace App\Http\Controllers;

use Morilog\Jalali\Jalalian;
use App\Models\Person;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Province;
use App\Models\CustomerPurchase;

class PersonController extends Controller
{
    public function nextCode()
    {
        // فقط کدهایی که با persons=- شروع می‌شوند را درنظر بگیر
        $lastPerson = Person::where('accounting_code', 'like', 'persons=%')
            ->orderByRaw('CAST(SUBSTRING(accounting_code, 9) AS UNSIGNED) DESC')
            ->first();

        if ($lastPerson && preg_match('/^persons=-(\d+)$/', $lastPerson->accounting_code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1001;
        }
        return response()->json(['code' => 'persons=-' . $nextNumber]);
    }

    public function index()
    {
        $persons = Person::latest()->paginate(10);
        return view('persons.index', compact('persons'));
    }

    public function create()
    {
        $provinces = Province::all();
        // کد پیش‌فرض برای فرم (برای نمایش اولیه)
        // فقط کدهایی که با persons=- شروع می‌شوند را درنظر بگیر
        $lastPerson = Person::where('accounting_code', 'like', 'persons=%')
            ->orderByRaw('CAST(SUBSTRING(accounting_code, 9) AS UNSIGNED) DESC')
            ->first();

        if ($lastPerson && preg_match('/^persons=-(\d+)$/', $lastPerson->accounting_code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1001;
        }
        $defaultCode = 'persons=-' . $nextNumber;

        return view('persons.create', compact('provinces', 'defaultCode'));
    }

    public function store(Request $request)
    {

        \Log::info('DATE_INPUTS', [
            'birth_date' => $request->birth_date,
            'marriage_date' => $request->marriage_date,
            'join_date' => $request->join_date,
        ]);

        // تبدیل تاریخ شمسی به میلادی
        foreach (['join_date', 'birth_date', 'marriage_date'] as $dateField) {
            if ($request->has($dateField) && $request->$dateField) {
                try {
                    if (strpos($request->$dateField, '-') !== false) {
                        $request[$dateField] = Jalalian::fromFormat('Y-m-d', $request->$dateField)->toCarbon()->toDateString();
                    } elseif (strpos($request->$dateField, '/') !== false) {
                        $request[$dateField] = Jalalian::fromFormat('Y/m/d', $request->$dateField)->toCarbon()->toDateString();
                    }
                } catch (\Exception $e) {
                    $request[$dateField] = null;
                }
            }
        }

        $rules = [
            'accounting_code' => 'required|string|unique:persons,accounting_code',
            'type' => 'required|in:customer,supplier,shareholder,employee',
            'province' => 'required|exists:provinces,id',
            'city' => 'required|exists:cities,id',
            'address' => 'required|string',
            'country' => 'required|string',
        ];

        if ($request->input('type') == 'supplier') {
            $rules['company_name'] = 'required|string';
        } else {
            $rules['first_name'] = 'required|string';
            $rules['last_name'] = 'required|string';
        }

        $optionalFields = [
            'nickname', 'credit_limit', 'price_list', 'tax_type', 'national_code', 'economic_code',
            'registration_number', 'branch_code', 'description', 'postal_code', 'phone', 'mobile', 'fax',
            'phone1', 'phone2', 'phone3', 'email', 'website', 'birth_date', 'marriage_date', 'join_date',
            'company_name', 'title'
        ];
        foreach ($optionalFields as $field) {
            $rules[$field] = 'nullable';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // اگر کد خودکار فعال است و کاربر دستی کد نداده، دوباره از دیتابیس بگیر تا تداخل ایجاد نشود
            if ($request->input('auto_code', '1') === '1') {
                $lastPerson = Person::where('accounting_code', 'like', 'persons=%')
                    ->orderByRaw('CAST(SUBSTRING(accounting_code, 9) AS UNSIGNED) DESC')
                    ->first();

                if ($lastPerson && preg_match('/^persons=-(\d+)$/', $lastPerson->accounting_code, $matches)) {
                    $nextNumber = intval($matches[1]) + 1;
                } else {
                    $nextNumber = 1001;
                }
                $validated['accounting_code'] = 'persons=-' . $nextNumber;
            }
            // اگر کاربر دستی کد داده همان مقدار ذخیره شود

            $person = Person::create(array_merge(
                $validated,
                ['accounting_code' => $validated['accounting_code']]
            ));

            // ذخیره حساب‌های بانکی (اگر ارسال شده)
            if ($request->has('bank_accounts')) {
                $bankAccounts = [];
                foreach ($request->bank_accounts as $account) {
                    if (!empty($account['bank_name'])) {
                        $bankAccounts[] = [
                            'bank_name' => $account['bank_name'],
                            'branch' => $account['branch'] ?? null,
                            'account_number' => $account['account_number'] ?? null,
                            'card_number' => $account['card_number'] ?? null,
                            'iban' => $account['iban'] ?? null,
                        ];
                    }
                }
                if (!empty($bankAccounts)) {
                    $person->bankAccounts()->createMany($bankAccounts);
                }
            }

            DB::commit();
            return redirect()->route('persons.index')->with('success', 'شخص جدید با موفقیت ایجاد شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطا در ثبت اطلاعات: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Person $person, Request $request)
    {
        $query = CustomerPurchase::where('customer_id', $person->id)->with('invoice');

        // فیلتر جستجو
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('invoice', function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('reference', 'like', "%$search%");
            });
        }
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('purchase_date', [$request->input('from'), $request->input('to')]);
        }

        $purchases = $query->orderByDesc('purchase_date')->paginate(10);

        $totalAmount = $query->sum('total_amount');

        return view('persons.show', compact('person', 'purchases', 'totalAmount'));
    }
    public function updatePercent(Person $person, Request $request)
    {
        $request->validate([
            'purchase_percent' => 'required|numeric|min:0|max:100'
        ]);
        $person->purchase_percent = $request->input('purchase_percent');
        $person->save();
        return redirect()->route('persons.show', $person)->with('success', 'درصد با موفقیت ذخیره شد.');
    }

















}
