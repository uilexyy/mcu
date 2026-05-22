<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class McuLabResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_id' => $this->registration_id,
            'lab_user' => new UserResource($this->whenLoaded('labUser')),
            'item' => new McuPackageItemResource($this->whenLoaded('item')),
            'nilai' => $this->nilai,
            'keterangan' => $this->keterangan,
            'created_at' => $this->created_at,
        ];
    }
}
