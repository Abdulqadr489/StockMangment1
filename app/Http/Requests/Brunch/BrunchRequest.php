<?php

namespace App\Http\Requests\Brunch;

use Illuminate\Foundation\Http\FormRequest;

class BrunchRequest extends FormRequest
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
        $data= $this->all();
        $isMultiple = is_array($data) && isset($data[0]);
        $baseRules = [
            'branch_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
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
