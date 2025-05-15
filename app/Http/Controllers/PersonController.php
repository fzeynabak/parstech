<?php

namespace App\Http\Controllers;

use Morilog\Jalali\Jalalian;
use App\Models\Person;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Province;

class PersonController extends Controller
{
    public function nextCode()
    {
        // فرض می‌کنیم کد حسابداری عددی و یکتا است
        $maxCode = Person::max(DB::raw('CAST(accounting_code AS UNSIGNED)'));
        $nextCode = $maxCode ? $maxCode + 1 : 1001; // اگر کدی وجود ندارد، از 1001 شروع کن
        return response()->json(['code' => $nextCode]);
    }

    public function index()
    {
        $persons = Person::latest()->paginate(10);
        return view('persons.index', compact('persons'));
    }

    public function create()
    {
        $provinces = Province::all();
        return view('persons.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        \Log::info('DATE_INPUTS', [
            'birth_date' => $request->birth_date,
            'marriage_date' => $request->marriage_date,
            'join_date' => $request->join_date,
        ]);

        // تبدیل تاریخ شمسی به میلادی (اگر از jalalian استفاده می‌کنی)
        foreach (['join_date', 'birth_date', 'marriage_date'] as $dateField) {
            if ($request->has($dateField) && $request->$dateField) {
                try {
                    // اگر تاریخ - داشت (YYYY-MM-DD)، همان قبلی
                    if (strpos($request->$dateField, '-') !== false) {
                        $request[$dateField] = Jalalian::fromFormat('Y-m-d', $request->$dateField)->toCarbon()->toDateString();
                    }
                    // اگر تاریخ / داشت (YYYY/MM/DD)
                    elseif (strpos($request->$dateField, '/') !== false) {
                        $request[$dateField] = Jalalian::fromFormat('Y/m/d', $request->$dateField)->toCarbon()->toDateString();
                    }
                } catch (\Exception $e) {
                    $request[$dateField] = null;
                }
            }
        }

        // اعتبارسنجی
        $rules = [
            'accounting_code' => 'required|string',
            'type' => 'required|in:customer,supplier,shareholder,employee',
            'province' => 'required|exists:provinces,id',
            'city' => 'required|exists:cities,id',
            'address' => 'required|string',
            'country' => 'required|string',
        ];

        // اگر تامین‌کننده (شرکت) بود فقط company_name اجباری شود
        if ($request->input('type') == 'supplier') {
            $rules['company_name'] = 'required|string';
        } else {
            $rules['first_name'] = 'required|string';
            $rules['last_name'] = 'required|string';
        }

        // فیلدهای اختیاری دیگر
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

            // فقط اطلاعات شخص را ذخیره کن (بدون bank_accounts)
            $person = Person::create($request->except(['bank_accounts']));

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
}
