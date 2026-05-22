<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'nip' => $this->nip,
            'gelar_depan' => $this->gelar_depan,
            'gelar_belakang' => $this->gelar_belakang,
            'sip' => $this->sip,
            'nama_lengkap' => $this->nama_lengkap,
            'departemen' => $this->departemen,
            'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
            'jenis_kelamin' => $this->jenis_kelamin,
            'signature_url' => $this->signature_url,
            'created_at' => $this->created_at,
        ];
    }
}
