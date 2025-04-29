<?php

namespace App\Http\Requests\WareHouse;

use Illuminate\Foundation\Http\FormRequest;

class WareHouseRequest extends FormRequest
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
            '*.item_id' => 'required|exists:items,id',
            '*.quantity' => 'required|integer|min:1',
        ];
    }
}
