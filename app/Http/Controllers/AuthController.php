<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Menampilkan halaman login.
     * Jika sudah login, redirect ke /dashboard (unified untuk semua role).
     */
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route("dashboard");
        }

        return Inertia::render("Auth/Login", [
            "adminContact" => $this->authService->getAdminContact(),
        ]);
    }

    /**
     * Memproses login multi-role.
     * Semua role diarahkan ke /dashboard setelah login berhasil.
     */
    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(
            [
                "phone" => ["required", "string"],
                "password" => ["nullable", "string"],
                "device_id" => ["nullable", "string"],
            ],
            [
                "phone.required" => "Nomor telepon wajib diisi.",
            ],
        );

        $result = $this->authService->login(
            $validated["phone"],
            $validated["password"] ?? null,
            $validated["device_id"] ?? null,
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        $user = $result["user"];

        return redirect()
            ->intended(route("dashboard"))
            ->with("success", "Selamat datang, {$user["name"]}!");
    }

    /**
     * Memproses logout pengguna.
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        $this->authService->logout($request);

        if ($request->wantsJson()) {
            return response()->json(["message" => "Logout berhasil."]);
        }

        return redirect()->route("login")->with("success", "Anda telah berhasil logout.");
    }

    /**
     * Mengambil kontak admin utama.
     */
    public function adminContact(): JsonResponse
    {
        $admin = $this->authService->getAdminContact();

        return response()->json([
            "admin" => $admin,
        ]);
    }
}
