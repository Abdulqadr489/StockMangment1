<?php

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequests extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return app()->runningInConsole() || auth()->check();
    }

    protected function prepareForValidation()
    {
        $data = $this->all();

        if (!isset($data[0]) || !is_array($data[0])) {
            $this->replace([$data]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            '*.branch_id' => 'required|integer|exists:branches,id',
            '*.sale_date' => 'required|date',
            '*.total_amount' => 'nullable|numeric|min:0', // Optional
            '*.customer_id' => 'required|integer|exists:customers,id',
            '*.items' => 'required|array|min:1',
            '*.items.*.item_id' => 'required|integer|exists:items,id',
            '*.items.*.quantity' => 'required|integer|min:1',
            '*.items.*.unit_price' => 'required|numeric|min:0',
            '*.items.*.discount' => 'required|numeric|min:0',
        ];
        }

}
