<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\StoreRegistrationRequest;
use App\Http\Resources\McuRegistrationResource;
use App\Models\McuRegistration;
use App\Models\McuResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = McuRegistration::with(['package', 'physicalExam', 'labResults.item', 'radiologyResult', 'result'])
            ->where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('package', function ($q) use ($search) {
                $q->where('nama_paket', 'like', "%{$search}%");
            });
        }

        return McuRegistrationResource::collection(
            $query->orderBy('created_at', 'desc')->paginate(15)
        );
    }

    public function store(StoreRegistrationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';

        if ($request->hasFile('foto_ktp')) {
            $data['foto_ktp'] = $request->file('foto_ktp')->store('foto-ktp', 'public');
        }

        $registration = McuRegistration::create($data);

        return response()->json([
            'message' => 'Pendaftaran MCU berhasil dikirim',
            'data' => new McuRegistrationResource($registration->load(['package'])),
        ], 201);
    }

    public function download($id, Request $request): JsonResponse
    {
        $registration = McuRegistration::with([
            'user', 'package.items', 'physicalExam.doctor', 'labResults.item', 'labResults.labUser', 'radiologyResult.radioUser',
        ])->where('user_id', $request->user()->id)->findOrFail($id);

        if (! in_array($registration->status, ['completed'])) {
            return response()->json(['message' => 'Hasil MCU belum tersedia'], 404);
        }

        $result = McuResult::where('registration_id', $id)->firstOrFail();

        if (! $result->pdf_path || ! Storage::disk('public')->exists($result->pdf_path)) {
            $pdf = Pdf::loadView('pdf.mcu-result', [
                'registration' => $registration,
                'result' => $result,
            ]);
            $filename = 'mcu-'.$registration->id.'-'.time().'.pdf';
            $path = 'pdf/'.$filename;
            Storage::disk('public')->put($path, $pdf->output());

            $result->update([
                'pdf_path' => $path,
                'generated_at' => now(),
            ]);
        }

        return response()->json([
            'pdf_url' => $result->pdf_url,
        ]);
    }
}
