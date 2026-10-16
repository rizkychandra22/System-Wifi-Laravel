<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService) {}

    /**
     * Menampilkan halaman presensi.
     * Admin  → melihat semua presensi karyawan (dengan filter tanggal/user_id).
     * Employee → melihat presensi milik sendiri hari ini + riwayat.
     */
    public function index(Request $request): Response|JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->hasRole("admin")) {
            $date = $request->query("date");
            $userId = $request->query("user_id") ? (int) $request->query("user_id") : null;
            $attendances = $this->attendanceService->getAllAttendance($date, $userId);

            if ($request->wantsJson()) {
                return response()->json($attendances);
            }

            return Inertia::render("Admin/Attendance/Index", [
                "attendances" => $attendances,
                "filters" => [
                    "date" => $date,
                    "user_id" => $userId,
                ],
            ]);
        }

        // Employee
        $userId = Auth::id();
        $today = $this->attendanceService->getTodayAttendance($userId);
        $history = $this->attendanceService->getAttendanceHistory(
            $userId,
            $request->query("month"),
        );

        if ($request->wantsJson()) {
            return response()->json([
                "today" => $today,
                "history" => $history,
            ]);
        }

        return Inertia::render("Employee/Attendance/Index", [
            "today" => $today,
            "history" => $history,
        ]);
    }

    /**
     * Mengambil presensi hari ini (JSON endpoint).
     */
    public function today(): JsonResponse
    {
        $today = $this->attendanceService->getTodayAttendance(Auth::id());

        return response()->json($today);
    }

    /**
     * Mengambil riwayat presensi karyawan (JSON endpoint).
     */
    public function history(Request $request): JsonResponse
    {
        $history = $this->attendanceService->getAttendanceHistory(
            Auth::id(),
            $request->query("month"),
        );

        return response()->json($history);
    }

    /**
     * Memproses Clock In (Absen Masuk) — Employee only.
     */
    public function clockIn(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "lat" => ["required", "numeric"],
            "lng" => ["required", "numeric"],
        ]);

        $attendance = $this->attendanceService->clockIn(
            Auth::id(),
            (float) $validated["lat"],
            (float) $validated["lng"],
        );

        if ($request->wantsJson()) {
            return response()->json(
                [
                    "message" => "Presensi masuk berhasil ({$attendance->grade}).",
                    "attendance" => $attendance,
                ],
                201,
            );
        }

        return back()->with("success", "Presensi masuk berhasil dicatat ({$attendance->grade}).");
    }

    /**
     * Memproses Clock Out (Absen Pulang) — Employee only.
     */
    public function clockOut(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "lat" => ["required", "numeric"],
            "lng" => ["required", "numeric"],
        ]);

        $attendance = $this->attendanceService->clockOut(
            Auth::id(),
            (float) $validated["lat"],
            (float) $validated["lng"],
        );

        if ($request->wantsJson()) {
            return response()->json([
                "message" => "Presensi pulang berhasil.",
                "attendance" => $attendance,
            ]);
        }

        return back()->with("success", "Presensi pulang berhasil dicatat.");
    }

    /**
     * Mengajukan Izin / Sakit — Employee only.
     */
    public function izin(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "notes" => ["required", "string", "max:500"],
        ]);

        $attendance = $this->attendanceService->requestIzin(Auth::id(), $validated["notes"]);

        if ($request->wantsJson()) {
            return response()->json(
                [
                    "message" => "Pengajuan izin berhasil dicatat.",
                    "attendance" => $attendance,
                ],
                201,
            );
        }

        return back()->with("success", "Pengajuan izin berhasil dicatat.");
    }

    /**
     * Mengubah data status presensi secara manual — Admin only.
     */
    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user?->hasRole("admin")) {
            abort(403, "Hanya Admin yang dapat mengubah data presensi.");
        }

        $validated = $request->validate([
            "status" => ["nullable", "string", "in:proses,hadir,izin,sakit,alpa,libur"],
            "grade" => ["nullable", "string"],
            "notes" => ["nullable", "string"],
        ]);

        $attendance = $this->attendanceService->updateAttendance($id, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                "message" => "Data presensi berhasil diperbarui.",
                "attendance" => $attendance,
            ]);
        }

        return back()->with("success", "Data presensi berhasil diperbarui.");
    }
}
