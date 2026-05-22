<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class McuResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_id' => $this->registration_id,
            'pdf_url' => $this->pdf_url,
            'generated_at' => $this->generated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
