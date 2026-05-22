<?php

namespace App\Http\Requests\Radiologi;

use Illuminate\Foundation\Http\FormRequest;

class StoreRadiologiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_id' => 'required|exists:mcu_registrations,id',
            'interpretasi' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }
}
