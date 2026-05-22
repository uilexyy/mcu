<?php

namespace App\Http\Controllers\Api\Radiologi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Radiologi\StoreRadiologiRequest;
use App\Http\Resources\McuRegistrationResource;
use App\Models\ActivityLog;
use App\Models\McuRegistration;
use App\Models\McuResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RadiologiController extends Controller
{
    public function riwayat(Request $request): AnonymousResourceCollection
    {
        $query = McuRegistration::with(['user', 'package', 'physicalExam.doctor', 'labResults.item', 'radiologyResult'])
            ->whereIn('status', ['radiology_done', 'completed']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            });
        }

        return McuRegistrationResource::collection(
            $query->orderBy('updated_at', 'desc')->paginate(15)
        );
    }

    public function history($id): JsonResponse
    {
        $registration = McuRegistration::with([
            'user', 'package', 'physicalExam.doctor',
            'labResults.item', 'labResults.labUser',
            'radiologyResult.radioUser', 'result',
        ])->findOrFail($id);

        return response()->json([
            'data' => new McuRegistrationResource($registration),
        ]);
    }

    public function queue(): JsonResponse
    {
        $registrations = McuRegistration::with(['user', 'package', 'physicalExam', 'labResults.item'])
            ->where('status', 'lab_done')
            ->orderBy('updated_at')
            ->get();

        return response()->json([
            'data' => McuRegistrationResource::collection($registrations),
        ]);
    }

    public function store(StoreRadiologiRequest $request): JsonResponse
    {
        $registration = McuRegistration::findOrFail($request->registration_id);

        if ($registration->status !== 'lab_done') {
            return response()->json(['message' => 'Status pendaftaran tidak valid untuk input hasil radiologi'], 422);
        }

        $data = DB::transaction(function () use ($request, $registration) {
            $input = [
                'radio_user_id' => $request->user()->id,
                'interpretasi' => $request->interpretasi,
            ];

            if ($request->hasFile('foto')) {
                if ($registration->radiologyResult?->file_path) {
                    Storage::disk('public')->delete($registration->radiologyResult->file_path);
                }
                $input['file_path'] = $request->file('foto')->store('rontgen', 'public');
            }

            $registration->radiologyResult()->updateOrCreate(
                ['registration_id' => $registration->id],
                $input
            );

            $registration->update(['status' => 'radiology_done']);

            // Auto-generate PDF
            $this->generatePdf($registration);

            return $registration;
        });

        $newRadiology = ['interpretasi' => $request->interpretasi];
        if ($request->hasFile('foto')) {
            $newRadiology['foto'] = 'uploaded';
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'model_type' => McuRegistration::class,
            'model_id' => $registration->id,
            'action' => 'radiology_created',
            'new_values' => $newRadiology,
            'description' => 'Hasil Radiologi untuk '.($registration->user?->name ?? "#{$registration->id}").' disimpan',
        ]);

        return response()->json([
            'message' => 'Hasil radiologi berhasil disimpan dan PDF telah digenerate',
            'data' => new McuRegistrationResource($data->load([
                'user', 'package', 'physicalExam.doctor',
                'labResults.item', 'labResults.labUser',
                'radiologyResult.radioUser', 'result',
            ])),
        ], 201);
    }

    public function update(StoreRadiologiRequest $request, $id): JsonResponse
    {
        $registration = McuRegistration::findOrFail($id);

        if (! in_array($registration->status, ['lab_done', 'radiology_done', 'completed'])) {
            return response()->json(['message' => 'Status pendaftaran tidak valid untuk edit hasil radiologi'], 422);
        }

        $oldRadiology = ['interpretasi' => $registration->radiologyResult?->interpretasi];
        if ($registration->radiologyResult?->file_path) {
            $oldRadiology['foto'] = 'exists';
        }

        $data = DB::transaction(function () use ($request, $registration) {
            $input = [
                'radio_user_id' => $request->user()->id,
                'interpretasi' => $request->interpretasi,
            ];

            if ($request->hasFile('foto')) {
                if ($registration->radiologyResult?->file_path) {
                    Storage::disk('public')->delete($registration->radiologyResult->file_path);
                }
                $input['file_path'] = $request->file('foto')->store('rontgen', 'public');
            }

            $registration->radiologyResult()->updateOrCreate(
                ['registration_id' => $registration->id],
                $input
            );

            // Delete old PDF result so it regenerates
            $registration->result()?->delete();
            $this->generatePdf($registration);

            return $registration;
        });

        $newRadiology = ['interpretasi' => $request->interpretasi];
        if ($request->hasFile('foto')) {
            $newRadiology['foto'] = 'uploaded';
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'model_type' => McuRegistration::class,
            'model_id' => $registration->id,
            'action' => 'radiology_updated',
            'old_values' => $oldRadiology,
            'new_values' => $newRadiology,
            'description' => 'Hasil Radiologi untuk '.($registration->user?->name ?? "#{$registration->id}").' diperbarui',
        ]);

        return response()->json([
            'message' => 'Hasil radiologi berhasil diperbarui dan PDF telah digenerate ulang',
            'data' => new McuRegistrationResource($data->load([
                'user', 'package', 'physicalExam.doctor',
                'labResults.item', 'labResults.labUser',
                'radiologyResult.radioUser', 'result',
            ])),
        ]);
    }

    private function generatePdf(McuRegistration $registration): void
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
