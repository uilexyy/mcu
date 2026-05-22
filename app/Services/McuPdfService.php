<?php

namespace App\Services;

use App\Models\McuRegistration;
use App\Models\McuResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class McuPdfService
{
    public function generate(McuRegistration $registration): void
    {
        $registration->load([
            'user', 'package.items', 'physicalExam.doctor',
            'labResults.item', 'labResults.labUser',
            'radiologyResult.radioUser',
        ]);

        $result = McuResult::updateOrCreate(
            ['registration_id' => $registration->id],
            ['generated_at' => now()]
        );

        $pdf = Pdf::loadView('pdf.mcu-result', [
            'registration' => $registration,
            'result' => $result,
        ]);

        $filename = 'mcu-'.$registration->id.'-'.time().'.pdf';
        $path = 'pdf/'.$filename;
        Storage::disk('public')->put($path, $pdf->output());

        $result->update(['pdf_path' => $path]);

        $registration->update(['status' => 'completed']);
    }
}
