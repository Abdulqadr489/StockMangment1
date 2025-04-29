<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class ItemCategory extends FormRequest
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

     protected function prepareForValidation()
     {
         $data = $this->all();

         if (!isset($data[0]) || !is_array($data[0])) {
             $this->replace([$data]);
         }
     }


    public function rules(): array
    {
        return [
            '*.category_name' => 'required|string|max:255',
        ];
    }
}
