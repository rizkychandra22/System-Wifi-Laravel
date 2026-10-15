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
     */
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User|null $user */
            $user = Auth::user();
            if ($user?->hasRole("admin")) {
                return redirect()->route("admin.dashboard");
            }
            if ($user?->hasRole("employee")) {
                return redirect()->route("employee.attendance");
            }
            return redirect()->route("customer.subscription");
        }

        return Inertia::render("Auth/Login", [
            "adminContact" => $this->authService->getAdminContact(),
        ]);
    }

    /**
     * Memproses login multi-role.
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
        /** @var User|null $authenticatedUser */
        $authenticatedUser = Auth::user();
        if ($authenticatedUser?->hasRole("admin")) {
            $redirectRoute = "admin.dashboard";
        } elseif ($authenticatedUser?->hasRole("employee")) {
            $redirectRoute = "employee.attendance";
        } else {
            $redirectRoute = "customer.subscription";
        }

        return redirect()
            ->intended(route($redirectRoute))
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
