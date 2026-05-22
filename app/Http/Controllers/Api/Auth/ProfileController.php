<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => new UserResource(request()->user()),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = request()->user();
        $user->update($request->validated());

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    public function uploadSignature(Request $request): JsonResponse
    {
        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:512',
        ]);

        $user = $request->user();

        if ($user->signature) {
            Storage::disk('public')->delete($user->signature);
        }

        $path = $request->file('signature')->store('signatures', 'public');

        $user->update(['signature' => $path]);

        return response()->json([
            'message' => 'Tanda tangan berhasil diunggah',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = request()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password saat ini salah'], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah',
        ]);
    }
}
