<?php

namespace App\Http\Requests\Dokter;

use Illuminate\Foundation\Http\FormRequest;

class StorePhysicalExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_id' => 'required|exists:mcu_registrations,id',
            'tekanan_darah' => 'nullable|string|max:50',
            'berat_badan' => 'nullable|numeric|min:0|max:500',
            'tinggi_badan' => 'nullable|numeric|min:0|max:300',
            'imt' => 'nullable|numeric|min:0|max:100',
            'anamnesis' => 'nullable|string',
            'catatan' => 'nullable|string',
        ];
    }
}
