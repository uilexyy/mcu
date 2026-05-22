<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => 'required|exists:mcu_packages,id',
            'foto_ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}
