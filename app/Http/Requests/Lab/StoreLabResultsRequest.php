<?php

namespace App\Http\Requests\Lab;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_id' => 'required|exists:mcu_registrations,id',
            'results' => 'required|array|min:1',
            'results.*.item_id' => 'required|exists:mcu_package_items,id',
            'results.*.nilai' => 'nullable|string|max:50',
            'results.*.keterangan' => 'nullable|in:Normal,Tinggi,Rendah',
        ];
    }
}
