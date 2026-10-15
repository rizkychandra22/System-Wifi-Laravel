<?php

namespace App\Http\Controllers;

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
     * Menampilkan dashboard presensi karyawan hari ini.
     */
    public function employeePage(Request $request): Response|JsonResponse
    {
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
     * Memproses Clock In (Absen Masuk).
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
     * Memproses Clock Out (Absen Pulang).
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
     * Mengajukan Izin / Sakit.
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
     * Menampilkan seluruh presensi karyawan untuk Admin.
     */
    public function adminIndex(Request $request): Response|JsonResponse
    {
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

    /**
     * Mengubah data status presensi secara manual oleh Admin.
     */
    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
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
