<?php

namespace App\Http\Controllers\Api\Dokter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dokter\StorePhysicalExamRequest;
use App\Http\Resources\McuRegistrationResource;
use App\Models\ActivityLog;
use App\Models\McuRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PhysicalExamController extends Controller
{
    public function riwayat(Request $request): AnonymousResourceCollection
    {
        $query = McuRegistration::with(['user', 'package', 'physicalExam.doctor'])
            ->whereHas('physicalExam', function ($q) use ($request) {
                $q->where('doctor_id', $request->user()->id);
            })
            ->whereIn('status', ['doctor_done', 'lab_done', 'radiology_done', 'completed']);

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

    public function queue(): JsonResponse
    {
        $today = now()->toDateString();

        $registrations = McuRegistration::with(['user', 'package'])
            ->where('status', 'approved')
            ->whereDate('tanggal_jadwal', $today)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => McuRegistrationResource::collection($registrations),
        ]);
    }

    public function store(StorePhysicalExamRequest $request): JsonResponse
    {
        $registration = McuRegistration::findOrFail($request->registration_id);

        if ($registration->status !== 'approved') {
            return response()->json(['message' => 'Status pendaftaran tidak valid untuk pemeriksaan fisik'], 422);
        }

        if ($registration->physicalExam) {
            return response()->json(['message' => 'Pemeriksaan fisik sudah dilakukan'], 422);
        }

        $data = DB::transaction(function () use ($request, $registration) {
            $exam = $registration->physicalExam()->create([
                'doctor_id' => $request->user()->id,
                'tekanan_darah' => $request->tekanan_darah,
                'berat_badan' => $request->berat_badan,
                'tinggi_badan' => $request->tinggi_badan,
                'imt' => $request->imt,
                'anamnesis' => $request->anamnesis,
                'catatan' => $request->catatan,
            ]);

            $registration->update(['status' => 'doctor_done']);

            return $exam;
        });

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'model_type' => McuRegistration::class,
            'model_id' => $registration->id,
            'action' => 'physical_exam_created',
            'new_values' => $request->only(['tekanan_darah', 'berat_badan', 'tinggi_badan', 'imt', 'anamnesis', 'catatan']),
            'description' => 'Pemeriksaan Fisik untuk '.($registration->user?->name ?? "#{$registration->id}").' disimpan',
        ]);

        return response()->json([
            'message' => 'Hasil pemeriksaan fisik berhasil disimpan',
            'data' => new McuRegistrationResource($registration->load(['user', 'package', 'physicalExam.doctor'])),
        ], 201);
    }

    public function update(StorePhysicalExamRequest $request, $id): JsonResponse
    {
        $registration = McuRegistration::findOrFail($id);

        if (! $registration->physicalExam) {
            return response()->json(['message' => 'Belum ada pemeriksaan fisik untuk diperbarui'], 422);
        }

        if ($registration->physicalExam->doctor_id !== $request->user()->id) {
            return response()->json(['message' => 'Anda tidak berwenang mengedit pemeriksaan ini'], 403);
        }

        $oldValues = $registration->physicalExam->only(['tekanan_darah', 'berat_badan', 'tinggi_badan', 'imt', 'anamnesis', 'catatan']);
        $newValues = $request->only(['tekanan_darah', 'berat_badan', 'tinggi_badan', 'imt', 'anamnesis', 'catatan']);

        DB::transaction(function () use ($request, $registration) {
            $registration->physicalExam()->update([
                'tekanan_darah' => $request->tekanan_darah,
                'berat_badan' => $request->berat_badan,
                'tinggi_badan' => $request->tinggi_badan,
                'imt' => $request->imt,
                'anamnesis' => $request->anamnesis,
                'catatan' => $request->catatan,
            ]);
        });

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'model_type' => McuRegistration::class,
            'model_id' => $registration->id,
            'action' => 'physical_exam_updated',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => 'Pemeriksaan Fisik untuk '.($registration->user?->name ?? "#{$registration->id}").' diperbarui',
        ]);

        return response()->json([
            'message' => 'Hasil pemeriksaan fisik berhasil diperbarui',
            'data' => new McuRegistrationResource($registration->load(['user', 'package', 'physicalExam.doctor'])),
        ]);
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
}
