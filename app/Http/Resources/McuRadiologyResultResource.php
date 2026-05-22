<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class McuRadiologyResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_id' => $this->registration_id,
            'radio_user' => new UserResource($this->whenLoaded('radioUser')),
            'interpretasi' => $this->interpretasi,
            'file_url' => $this->file_url,
            'created_at' => $this->created_at,
        ];
    }
}
