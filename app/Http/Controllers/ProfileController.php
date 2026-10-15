<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Memperbarui profil akun yang sedang login.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "phone" => ["required", "string", "max:20"],
            "address" => ["nullable", "string", "max:500"],
        ]);

        $currentUser = Auth::user();
        $user = $this->authService->updateProfile($currentUser, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                "message" => "Profil berhasil diperbarui.",
                "user" => $user,
            ]);
        }

        return back()->with("success", "Profil berhasil diperbarui.");
    }

    /**
     * Memperbarui kata sandi akun Admin.
     */
    public function updatePassword(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(
            [
                "old_password" => ["required", "string"],
                "new_password" => ["required", "string", "min:6"],
            ],
            [
                "old_password.required" => "Password lama wajib diisi.",
                "new_password.required" => "Password baru wajib diisi.",
                "new_password.min" => "Password baru minimal 6 karakter.",
            ],
        );

        $currentUser = Auth::user();

        $this->authService->updatePassword(
            $currentUser,
            $validated["old_password"],
            $validated["new_password"],
        );

        if ($request->wantsJson()) {
            return response()->json([
                "message" => "Password berhasil diperbarui.",
            ]);
        }

        return back()->with("success", "Password berhasil diperbarui.");
    }
}
