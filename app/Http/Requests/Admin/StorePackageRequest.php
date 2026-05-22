<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_paket' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'has_radiologi' => 'boolean',
            'items' => 'nullable|array',
            'items.*.nama_pemeriksaan' => 'required_with:items|string|max:100',
            'items.*.satuan' => 'nullable|string|max:50',
            'items.*.nilai_normal' => 'nullable|string|max:100',
        ];
    }
}
