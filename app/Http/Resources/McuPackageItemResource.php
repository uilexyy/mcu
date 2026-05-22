<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class McuPackageItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'package_id' => $this->package_id,
            'nama_pemeriksaan' => $this->nama_pemeriksaan,
            'satuan' => $this->satuan,
            'nilai_normal' => $this->nilai_normal,
        ];
    }
}
