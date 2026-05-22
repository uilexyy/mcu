<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class McuPackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_paket' => $this->nama_paket,
            'deskripsi' => $this->deskripsi,
            'harga' => (float) $this->harga,
            'is_active' => $this->is_active,
            'items' => McuPackageItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
