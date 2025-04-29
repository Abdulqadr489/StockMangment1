<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemDetail extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return app()->runningInConsole() || auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $data = $this->all();
        $isMultiple = is_array($data) && isset($data[0]);
        $itemId = $this->route('item')?->id;

        $baseRules = [
            'item_name' => 'sometimes|required|string|max:255',
            'item_Barcode' => [
                'sometimes', 'required', 'numeric',
                Rule::unique('items', 'item_Barcode')->ignore($itemId),
            ],
            'category_id' => 'sometimes|required|integer|exists:item_categories,id',
            'item_description' => 'nullable|string',
            'item_price' => 'sometimes|required|numeric|min:0',
            'item_expiry_date' => 'sometimes|required|date',
        ];

        if($isMultiple)
        {
            return collect($baseRules)
            ->mapWithKeys(fn ($rule, $key) => ["*.$key*" => $rule])
            ->toArray();
        }

        return $baseRules;

    }
}
