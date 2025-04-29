<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $data= $this->all();
        $isMultiple = is_array($data) && isset($data[0]);
        $baseRules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
        ];
        if ($isMultiple) {
            return [
                '*.name' => $baseRules['name'],
                '*.phone' => $baseRules['phone'],
            ];
        }
        return $baseRules;

        
    }
}
