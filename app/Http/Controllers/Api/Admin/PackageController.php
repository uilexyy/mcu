<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePackageRequest;
use App\Http\Requests\Admin\UpdatePackageRequest;
use App\Http\Resources\McuPackageResource;
use App\Models\McuPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function index(): JsonResponse
    {
        $user = request()->user();

        $packages = McuPackage::with('items')
            ->when(! $user || $user->role !== 'admin', fn ($q) => $q->where('is_active', true))
            ->orderBy('nama_paket')
            ->get();

        return response()->json([
            'data' => McuPackageResource::collection($packages),
        ]);
    }

    public function store(StorePackageRequest $request): JsonResponse
    {
        $data = DB::transaction(function () use ($request) {
            $package = McuPackage::create($request->safe()->except('items'));

            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $package->items()->create($item);
                }
            }

            return $package->load('items');
        });

        return response()->json([
            'message' => 'Paket MCU berhasil dibuat',
            'data' => new McuPackageResource($data),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $package = McuPackage::with('items')->findOrFail($id);

        return response()->json([
            'data' => new McuPackageResource($package),
        ]);
    }

    public function update(UpdatePackageRequest $request, $id): JsonResponse
    {
        $data = DB::transaction(function () use ($request, $id) {
            $package = McuPackage::findOrFail($id);
            $package->update($request->safe()->except('items'));

            if ($request->has('items')) {
                $package->items()->delete();
                foreach ($request->items as $item) {
                    $package->items()->create($item);
                }
            }

            return $package->load('items');
        });

        return response()->json([
            'message' => 'Paket MCU berhasil diperbarui',
            'data' => new McuPackageResource($data),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $package = McuPackage::findOrFail($id);
        $package->items()->delete();
        $package->delete();

        return response()->json([
            'message' => 'Paket MCU berhasil dihapus',
        ]);
    }
}
