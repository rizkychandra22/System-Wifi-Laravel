<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OvertimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OvertimeController extends Controller
{
    public function __construct(protected OvertimeService $overtimeService) {}

    /**
     * Menampilkan daftar lembur (Admin melihat semua, Employee melihat milik sendiri).
     */
    public function index(Request $request): Response|JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        $overtimes = $user?->hasRole("admin")
            ? $this->overtimeService->getAll()
            : $this->overtimeService->getByUser($user?->id ?? 0);

        if ($request->wantsJson()) {
            return response()->json($overtimes);
        }

        $view = $user?->hasRole("admin") ? "Admin/Overtimes/Index" : "Employee/Overtimes/Index";

        return Inertia::render($view, [
            "overtimes" => $overtimes,
        ]);
    }

    /**
     * Menyimpan data lembur baru.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "user_id" => ["nullable", "integer", "exists:users,id"],
            "title" => ["required", "string", "max:255"],
            "description" => ["nullable", "string", "max:500"],
            "date" => ["required", "date"],
            "start_time" => ["required", "date"],
            "end_time" => ["required", "date", "after:start_time"],
        ]);

        /** @var User|null $currentUser */
        $currentUser = Auth::user();

        // Jika bukan admin, otomatis assign ke user yang sedang login
        if (!$currentUser?->hasRole("admin") || empty($validated["user_id"])) {
            $validated["user_id"] = $currentUser?->id;
        }

        $overtime = $this->overtimeService->create($validated);

        if ($request->wantsJson()) {
            return response()->json(
                [
                    "message" => "Data lembur berhasil dicatat.",
                    "overtime" => $overtime,
                ],
                201,
            );
        }

        return back()->with("success", "Data lembur berhasil dicatat.");
    }

    /**
     * Menampilkan detail satu lembur.
     */
    public function show(int $id, Request $request): Response|JsonResponse
    {
        $overtime = $this->overtimeService->getById($id);

        if ($request->wantsJson()) {
            return response()->json($overtime);
        }

        return Inertia::render("Admin/Overtimes/Show", [
            "overtime" => $overtime,
        ]);
    }

    /**
     * Memperbarui data lembur.
     */
    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "user_id" => ["nullable", "integer", "exists:users,id"],
            "title" => ["nullable", "string", "max:255"],
            "description" => ["nullable", "string", "max:500"],
            "date" => ["nullable", "date"],
            "start_time" => ["nullable", "date"],
            "end_time" => ["nullable", "date"],
        ]);

        $overtime = $this->overtimeService->update($id, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                "message" => "Data lembur berhasil diperbarui.",
                "overtime" => $overtime,
            ]);
        }

        return back()->with("success", "Data lembur berhasil diperbarui.");
    }

    /**
     * Menghapus catatan lembur.
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->overtimeService->delete($id);

        if ($request->wantsJson()) {
            return response()->json(["message" => "Data lembur berhasil dihapus."]);
        }

        return back()->with("success", "Data lembur berhasil dihapus.");
    }
}
