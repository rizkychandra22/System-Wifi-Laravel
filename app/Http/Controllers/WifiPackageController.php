<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WifiPackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WifiPackageController extends Controller
{
    public function __construct(protected WifiPackageService $wifiPackageService) {}

    /**
     * Menampilkan daftar paket WiFi — semua role dapat melihat.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $packages = $this->wifiPackageService->getAll();

        if ($request->wantsJson()) {
            return response()->json($packages);
        }

        return Inertia::render("Admin/Packages/Index", [
            "packages" => $packages,
        ]);
    }

    /**
     * Membuat paket WiFi baru — Admin only.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user?->hasRole("admin")) {
            abort(403, "Hanya Admin yang dapat menambah paket WiFi.");
        }

        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "price" => ["required", "numeric", "min:0"],
        ]);

        $package = $this->wifiPackageService->create($validated);

        if ($request->wantsJson()) {
            return response()->json(
                [
                    "message" => "Paket WiFi berhasil dibuat.",
                    "package" => $package,
                ],
                201,
            );
        }

        return back()->with("success", "Paket WiFi berhasil dibuat.");
    }

    /**
     * Memperbarui informasi paket WiFi — Admin only.
     */
    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user?->hasRole("admin")) {
            abort(403, "Hanya Admin yang dapat mengubah paket WiFi.");
        }

        $validated = $request->validate([
            "name" => ["nullable", "string", "max:255"],
            "price" => ["nullable", "numeric", "min:0"],
        ]);

        $package = $this->wifiPackageService->update($id, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                "message" => "Paket WiFi berhasil diperbarui.",
                "package" => $package,
            ]);
        }

        return back()->with("success", "Paket WiFi berhasil diperbarui.");
    }

    /**
     * Menghapus paket WiFi — Admin only.
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user?->hasRole("admin")) {
            abort(403, "Hanya Admin yang dapat menghapus paket WiFi.");
        }

        $this->wifiPackageService->delete($id);

        if ($request->wantsJson()) {
            return response()->json(["message" => "Paket WiFi berhasil dihapus."]);
        }

        return back()->with("success", "Paket WiFi berhasil dihapus.");
    }
}
