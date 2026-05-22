<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\RegistrationsExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\McuRegistrationResource;
use App\Models\ActivityLog;
use App\Models\McuRegistration;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = McuRegistration::with(['user', 'package']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')->paginate(15);

        return McuRegistrationResource::collection($registrations);
    }

    public function show($id): JsonResponse
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

    public function approve(Request $request, $id): JsonResponse
    {
        $request->validate([
            'tanggal_jadwal' => 'required|date',
        ]);

        $registration = McuRegistration::findOrFail($id);

        if ($registration->status !== 'pending') {
            return response()->json(['message' => 'Pendaftaran sudah diproses sebelumnya'], 422);
        }

        $oldStatus = $registration->status;

        $registration->update([
            'status' => 'approved',
            'tanggal_jadwal' => $request->tanggal_jadwal,
            'catatan_admin' => $request->catatan_admin,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'model_type' => McuRegistration::class,
            'model_id' => $registration->id,
            'action' => 'status_changed',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'approved', 'tanggal_jadwal' => $request->tanggal_jadwal],
            'description' => 'Pendaftaran '.($registration->user?->name ?? "#{$registration->id}").' disetujui',
        ]);

        return response()->json([
            'message' => 'Pendaftaran telah disetujui',
            'data' => new McuRegistrationResource($registration->load(['user', 'package'])),
        ]);
    }

    public function reject(Request $request, $id): JsonResponse
    {
        $request->validate([
            'catatan_admin' => 'required|string',
        ]);

        $registration = McuRegistration::findOrFail($id);

        if ($registration->status !== 'pending') {
            return response()->json(['message' => 'Pendaftaran sudah diproses sebelumnya'], 422);
        }

        $oldStatus = $registration->status;

        $registration->update([
            'status' => 'rejected',
            'catatan_admin' => $request->catatan_admin,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'model_type' => McuRegistration::class,
            'model_id' => $registration->id,
            'action' => 'status_changed',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'rejected', 'catatan_admin' => $request->catatan_admin],
            'description' => 'Pendaftaran '.($registration->user?->name ?? "#{$registration->id}").' ditolak',
        ]);

        return response()->json([
            'message' => 'Pendaftaran ditolak',
            'data' => new McuRegistrationResource($registration->load(['user', 'package'])),
        ]);
    }

    public function export(Request $request)
    {
        $filename = 'rekap-mcu-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(
            new RegistrationsExport(
                status: $request->status,
                search: $request->search,
                tanggal: $request->tanggal,
            ),
            $filename
        );
    }

    public function stats(): JsonResponse
    {
        $total = McuRegistration::count();
        $pending = McuRegistration::where('status', 'pending')->count();
        $approved = McuRegistration::where('status', 'approved')->count();
        $doctorDone = McuRegistration::where('status', 'doctor_done')->count();
        $labDone = McuRegistration::where('status', 'lab_done')->count();
        $radiologyDone = McuRegistration::where('status', 'radiology_done')->count();
        $completed = McuRegistration::where('status', 'completed')->count();
        $rejected = McuRegistration::where('status', 'rejected')->count();

        // Monthly registrations (last 12 months)
        $monthly = McuRegistration::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                $date = Carbon::create($item->year, $item->month, 1);

                return [
                    'bulan' => $date->locale('id')->isoFormat('MMMM YYYY'),
                    'total' => (int) $item->total,
                ];
            });

        // Registrations by package
        $byPackage = McuRegistration::selectRaw('mcu_packages.nama_paket, COUNT(*) as total')
            ->join('mcu_packages', 'mcu_registrations.package_id', '=', 'mcu_packages.id')
            ->groupBy('mcu_packages.nama_paket')
            ->get();

        // Recent registrations (last 10)
        $recent = McuRegistration::with(['user', 'package'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($reg) {
                return [
                    'id' => $reg->id,
                    'user_name' => $reg->user->name,
                    'package_name' => $reg->package->nama_paket,
                    'status' => $reg->status,
                    'created_at' => $reg->created_at,
                ];
            });

        return response()->json([
            'data' => [
                'summary' => [
                    'total' => $total,
                    'pending' => $pending,
                    'approved' => $approved,
                    'doctor_done' => $doctorDone,
                    'lab_done' => $labDone,
                    'radiology_done' => $radiologyDone,
                    'completed' => $completed,
                    'rejected' => $rejected,
                ],
                'monthly' => $monthly,
                'by_package' => $byPackage,
                'recent' => $recent,
            ],
        ]);
    }

    public function logs(Request $request)
    {
        $query = ActivityLog::with('user');

        $allowedActions = [
            'created', 'updated', 'deleted',
            'status_changed', 'registered',
            'physical_exam_created', 'physical_exam_updated',
            'lab_results_created', 'lab_results_updated',
            'radiology_created', 'radiology_updated',
        ];

        if ($request->filled('action') && in_array($request->action, $allowedActions)) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25);

        return response()->json($logs);
    }
}
