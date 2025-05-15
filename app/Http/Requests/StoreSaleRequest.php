<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'invoice_number' => 'required|string|max:50',
            'reference' => 'nullable|string|max:100',
            'issued_at_jalali' => 'required|string|max:50',
            'issued_at' => 'required|date',
            // 'due_at_jalali' => 'required', // این خط باید حذف شود
            // 'due_at' => 'required|date',   // این خط باید حذف شود
            'currency_id' => 'required|exists:currencies,id',
            'customer_id' => 'required|exists:customers,id',
            'title' => 'nullable|string|max:255',
            'seller_id' => 'required|exists:sellers,id',
            'products_input' => 'required|string',
        ];
    }
}
