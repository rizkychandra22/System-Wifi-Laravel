<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    /**
     * Menampilkan daftar pengguna (Inertia View / JSON).
     */
    public function index(Request $request): Response|JsonResponse
    {
        $currentUser = Auth::user();
        $users = $this->userService->getUsers($currentUser);

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return Inertia::render("Admin/Users/Index", [
            "users" => $users,
        ]);
    }

    /**
     * Menyimpan pengguna baru.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "phone" => ["required", "string", "max:20", "unique:users,phone"],
            "role" => ["nullable", "string", "in:admin,employee,customer"],
            "address" => ["nullable", "string", "max:500"],
            "password" => ["nullable", "string", "min:6"],
            "registered_by_id" => ["nullable", "integer", "exists:users,id"],
        ]);

        $currentUser = Auth::user();
        $user = $this->userService->createUser($validated, $currentUser);

        if ($request->wantsJson()) {
            return response()->json(
                [
                    "message" => "Pengguna berhasil ditambahkan.",
                    "user" => $user,
                ],
                201,
            );
        }

        return back()->with("success", "Pengguna berhasil ditambahkan.");
    }

    /**
     * Menampilkan detail satu pengguna.
     */
    public function show(int $id, Request $request): JsonResponse|Response
    {
        $currentUser = Auth::user();
        $user = $this->userService->getUserById($id, $currentUser);

        if ($request->wantsJson()) {
            return response()->json($user);
        }

        return Inertia::render("Admin/Users/Show", [
            "user" => $user,
        ]);
    }

    /**
     * Memperbarui data pengguna.
     */
    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "name" => ["nullable", "string", "max:255"],
            "phone" => ["nullable", "string", "max:20"],
            "role" => ["nullable", "string", "in:admin,employee,customer"],
            "address" => ["nullable", "string", "max:500"],
        ]);

        $currentUser = Auth::user();
        $user = $this->userService->updateUser($id, $validated, $currentUser);

        if ($request->wantsJson()) {
            return response()->json([
                "message" => "Data pengguna berhasil diperbarui.",
                "user" => $user,
            ]);
        }

        return back()->with("success", "Data pengguna berhasil diperbarui.");
    }

    /**
     * Menghapus pengguna secara soft delete (Admin only).
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user?->hasRole("admin")) {
            abort(403, "Hanya Admin yang dapat menghapus pengguna.");
        }

        $this->userService->deleteUser($id);

        if ($request->wantsJson()) {
            return response()->json(["message" => "Pengguna berhasil dihapus."]);
        }

        return back()->with("success", "Pengguna berhasil dihapus.");
    }

    /**
     * Me-reset kunci perangkat (Device Lock) pengguna (Admin only).
     */
    public function resetDevice(int $id, Request $request): JsonResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user?->hasRole("admin")) {
            abort(403, "Hanya Admin yang dapat me-reset kunci perangkat.");
        }

        $resetUser = $this->userService->resetUserDevice($id);

        if ($request->wantsJson()) {
            return response()->json([
                "message" => "Kunci perangkat pengguna berhasil di-reset.",
                "user" => $resetUser,
            ]);
        }

        return back()->with("success", "Kunci perangkat pengguna berhasil di-reset.");
    }
}
