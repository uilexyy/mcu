<?php

namespace App\Http\Controllers\Api\Lab;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lab\StoreLabResultsRequest;
use App\Http\Resources\McuRegistrationResource;
use App\Models\ActivityLog;
use App\Models\McuRegistration;
use App\Services\McuPdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class LabResultController extends Controller
{
    public function riwayat(Request $request): AnonymousResourceCollection
    {
        $query = McuRegistration::with(['user', 'package', 'physicalExam.doctor', 'labResults.item'])
            ->whereIn('status', ['lab_done', 'radiology_done', 'completed']);

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
        $registrations = McuRegistration::with(['user', 'package', 'physicalExam'])
            ->where('status', 'doctor_done')
            ->orderBy('updated_at')
            ->get();

        return response()->json([
            'data' => McuRegistrationResource::collection($registrations),
        ]);
    }

    public function history($id): JsonResponse
    {
        $registration = McuRegistration::with([
            'user', 'package.items', 'physicalExam.doctor',
            'labResults.item', 'labResults.labUser',
            'radiologyResult.radioUser', 'result',
        ])->findOrFail($id);

        return response()->json([
            'data' => new McuRegistrationResource($registration),
        ]);
    }

    public function store(StoreLabResultsRequest $request): JsonResponse
    {
        $registration = McuRegistration::findOrFail($request->registration_id);

        if (! in_array($registration->status, ['doctor_done', 'lab_done'])) {
            return response()->json(['message' => 'Status pendaftaran tidak valid untuk input hasil lab'], 422);
        }

        DB::transaction(function () use ($request, $registration) {
            // Delete existing lab results for this registration to allow re-entry
            $registration->labResults()->delete();

            foreach ($request->results as $result) {
                $registration->labResults()->create([
                    'lab_user_id' => $request->user()->id,
                    'item_id' => $result['item_id'],
                    'nilai' => $result['nilai'] ?? null,
                    'keterangan' => $result['keterangan'] ?? null,
                    'created_at' => now(),
                ]);
            }

            if ($registration->status === 'doctor_done') {
                $registration->update(['status' => 'lab_done']);
            }
        });

        if ($registration->status === 'lab_done' && ! $registration->package->has_radiologi) {
            app(McuPdfService::class)->generate($registration->fresh());
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'model_type' => McuRegistration::class,
            'model_id' => $registration->id,
            'action' => 'lab_results_created',
            'new_values' => ['results' => $request->results],
            'description' => 'Hasil Lab untuk '.($registration->user?->name ?? "#{$registration->id}").' disimpan',
        ]);

        return response()->json([
            'message' => 'Hasil laboratorium berhasil disimpan',
            'data' => new McuRegistrationResource($registration->load(['user', 'package', 'labResults.item', 'labResults.labUser'])),
        ], 201);
    }

    public function update(StoreLabResultsRequest $request, $id): JsonResponse
    {
        $registration = McuRegistration::findOrFail($id);

        if (! in_array($registration->status, ['doctor_done', 'lab_done', 'radiology_done', 'completed'])) {
            return response()->json(['message' => 'Status pendaftaran tidak valid untuk edit hasil lab'], 422);
        }

        $oldResults = $registration->labResults->map(function ($lr) {
            return ['item_id' => $lr->item_id, 'nilai' => $lr->nilai, 'keterangan' => $lr->keterangan];
        });

        DB::transaction(function () use ($request, $registration) {
            $registration->labResults()->delete();

            foreach ($request->results as $result) {
                $registration->labResults()->create([
                    'lab_user_id' => $request->user()->id,
                    'item_id' => $result['item_id'],
                    'nilai' => $result['nilai'] ?? null,
                    'keterangan' => $result['keterangan'] ?? null,
                    'created_at' => now(),
                ]);
            }
        });

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'model_type' => McuRegistration::class,
            'model_id' => $registration->id,
            'action' => 'lab_results_updated',
            'old_values' => ['results' => $oldResults],
            'new_values' => ['results' => $request->results],
            'description' => 'Hasil Lab untuk '.($registration->user?->name ?? "#{$registration->id}").' diperbarui',
        ]);

        return response()->json([
            'message' => 'Hasil laboratorium berhasil diperbarui',
            'data' => new McuRegistrationResource($registration->load(['user', 'package', 'labResults.item', 'labResults.labUser'])),
        ]);
    }
}
