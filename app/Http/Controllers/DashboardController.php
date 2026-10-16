<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard sesuai role pengguna yang sedang login.
     */
    public function index(Request $request): Response|JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($request->wantsJson()) {
            return response()->json([
                "role" => $user?->role,
                "name" => $user?->name,
            ]);
        }

        return Inertia::render("Dashboard", [
            "role" => $user?->role,
        ]);
    }
}
