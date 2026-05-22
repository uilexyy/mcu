<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class McuPhysicalExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_id' => $this->registration_id,
            'doctor' => new UserResource($this->whenLoaded('doctor')),
            'tekanan_darah' => $this->tekanan_darah,
            'berat_badan' => $this->berat_badan ? (float) $this->berat_badan : null,
            'tinggi_badan' => $this->tinggi_badan ? (float) $this->tinggi_badan : null,
            'imt' => $this->imt ? (float) $this->imt : null,
            'anamnesis' => $this->anamnesis,
            'catatan' => $this->catatan,
            'created_at' => $this->created_at,
        ];
    }
}
