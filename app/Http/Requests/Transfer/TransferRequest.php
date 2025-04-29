<?php

namespace App\Http\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
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

    public function rules(): array
    {
        return [
            '*.branch_id' => 'required|integer|exists:branches,id',
            '*.transfer_date' => 'required|date',
            '*.transfers' => 'required|array|min:1',
            '*.transfers.*.item_id' => 'required|integer|exists:items,id',
            '*.transfers.*.quantity' => 'required|integer|min:1',
        ];
    }
}
