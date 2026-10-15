<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AllowanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AllowanceController extends Controller
{
    public function __construct(protected AllowanceService $allowanceService) {}

    /**
     * Menampilkan daftar tunjangan/bonus (Admin melihat semua, Employee melihat milik sendiri).
     */
    public function index(Request $request): Response|JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        $month = $request->query("month");

        $allowances = $user?->hasRole("admin")
            ? $this->allowanceService->getAll($month)
            : $this->allowanceService->getEmployeeAllowances($user?->id ?? 0, $month);

        if ($request->wantsJson()) {
            return response()->json($allowances);
        }

        $view = $user?->hasRole("admin") ? "Admin/Allowances/Index" : "Employee/Allowances/Index";

        return Inertia::render($view, [
            "allowances" => $allowances,
            "month" => $month,
        ]);
    }

    /**
     * Menyimpan tunjangan/bonus baru (Admin only).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(
            [
                "title" => ["required", "string", "max:255"],
                "description" => ["nullable", "string", "max:500"],
                "amount" => ["required", "numeric", "min:1"],
                "month" => ["required", "string", 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
                "target_type" => ["required", "string", "in:personal,global"],
                "user_id" => ["nullable", "integer", "exists:users,id"],
            ],
            [
                "month.regex" => "Format bulan harus berupa YYYY-MM (contoh: 2026-10).",
            ],
        );

        $allowance = $this->allowanceService->create($validated, Auth::id());

        if ($request->wantsJson()) {
            return response()->json(
                [
                    "message" => "Tunjangan/bonus berhasil dicatat.",
                    "allowance" => $allowance,
                ],
                201,
            );
        }

        return back()->with("success", "Tunjangan/bonus berhasil dicatat.");
    }

    /**
     * Menampilkan detail satu data tunjangan.
     */
    public function show(int $id, Request $request): Response|JsonResponse
    {
        $allowance = $this->allowanceService->getById($id);

        if ($request->wantsJson()) {
            return response()->json($allowance);
        }

        return Inertia::render("Admin/Allowances/Show", [
            "allowance" => $allowance,
        ]);
    }

    /**
     * Memperbarui data tunjangan.
     */
    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "title" => ["nullable", "string", "max:255"],
            "description" => ["nullable", "string", "max:500"],
            "amount" => ["nullable", "numeric", "min:1"],
            "month" => ["nullable", "string", 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            "target_type" => ["nullable", "string", "in:personal,global"],
            "user_id" => ["nullable", "integer", "exists:users,id"],
        ]);

        $allowance = $this->allowanceService->update($id, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                "message" => "Data tunjangan berhasil diperbarui.",
                "allowance" => $allowance,
            ]);
        }

        return back()->with("success", "Data tunjangan berhasil diperbarui.");
    }

    /**
     * Menghapus catatan tunjangan.
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->allowanceService->delete($id);

        if ($request->wantsJson()) {
            return response()->json(["message" => "Data tunjangan berhasil dihapus."]);
        }

        return back()->with("success", "Data tunjangan berhasil dihapus.");
    }
}
