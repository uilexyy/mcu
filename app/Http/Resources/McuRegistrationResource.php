<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class McuRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'package' => new McuPackageResource($this->whenLoaded('package')),
            'status' => $this->status,
            'tanggal_jadwal' => $this->tanggal_jadwal?->format('Y-m-d'),
            'catatan_admin' => $this->catatan_admin,
            'foto_ktp' => $this->foto_ktp ? asset('storage/'.$this->foto_ktp) : null,
            'physical_exam' => new McuPhysicalExamResource($this->whenLoaded('physicalExam')),
            'lab_results' => McuLabResultResource::collection($this->whenLoaded('labResults')),
            'radiology_result' => new McuRadiologyResultResource($this->whenLoaded('radiologyResult')),
            'result' => new McuResultResource($this->whenLoaded('result')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
