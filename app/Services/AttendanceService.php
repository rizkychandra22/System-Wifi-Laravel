<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public const OFFICE_LAT = -7.033562;
    public const OFFICE_LNG = 106.949204;
    public const MAX_RADIUS_METERS = 100.0;

    /**
     * Menghitung jarak antara dua koordinat GPS menggunakan formula Haversine (dalam meter).
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000.0; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Memproses Clock In (Absen Masuk) Karyawan dengan validasi Geofencing dan Grade Disiplin.
     */
    public function clockIn(int $userId, float $lat, float $lng): Attendance
    {
        $now = Carbon::now("Asia/Jakarta");
        $dateStr = $now->toDateString();

        // 1. Cek apakah hari ini sudah dinyatakan libur otomatis
        $isHoliday = Attendance::where("date", $dateStr)->where("status", "libur")->exists();

        if ($isHoliday) {
            throw ValidationException::withMessages([
                "attendance" => [
                    "Hari ini sudah dinyatakan libur karena tidak ada yang absen masuk hingga jam 12:00.",
                ],
            ]);
        }

        // 2. Cek apakah karyawan sudah absen hari ini
        $existing = Attendance::where("user_id", $userId)->where("date", $dateStr)->first();

        if ($existing) {
            throw ValidationException::withMessages([
                "attendance" => ["Anda sudah melakukan presensi hari ini."],
            ]);
        }

        // 3. Batas waktu absen masuk adalah pukul 12:00 WIB
        $cutoff = Carbon::create($now->year, $now->month, $now->day, 12, 0, 0, "Asia/Jakarta");
        if ($now->greaterThan($cutoff)) {
            throw ValidationException::withMessages([
                "attendance" => ["Batas waktu absen masuk telah lewat (maksimal 12:00 WIB)."],
            ]);
        }

        // 4. Validasi Geofencing (Maksimal 100 meter dari kantor)
        $distance = $this->calculateDistance(self::OFFICE_LAT, self::OFFICE_LNG, $lat, $lng);
        if ($distance > self::MAX_RADIUS_METERS) {
            throw ValidationException::withMessages([
                "attendance" => [
                    "Anda berada di luar area kantor (jarak Anda: " .
                    round($distance) .
                    " meter, maksimal 100 meter).",
                ],
            ]);
        }

        // 5. Tentukan Grade Disiplin berdasarkan jam masuk
        $time750 = Carbon::create($now->year, $now->month, $now->day, 7, 50, 0, "Asia/Jakarta");
        $time800 = Carbon::create($now->year, $now->month, $now->day, 8, 0, 0, "Asia/Jakarta");
        $time810 = Carbon::create($now->year, $now->month, $now->day, 8, 10, 0, "Asia/Jakarta");

        if ($now->lessThanOrEqualTo($time750)) {
            $grade = "Disiplin";
        } elseif ($now->lessThanOrEqualTo($time800)) {
            $grade = "Tepat Waktu";
        } elseif ($now->lessThanOrEqualTo($time810)) {
            $grade = "Toleransi Terlambat";
        } else {
            $grade = "Terlambat";
        }

        return Attendance::create([
            "user_id" => $userId,
            "date" => $dateStr,
            "clock_in" => $now,
            "grade" => $grade,
            "clock_in_lat" => $lat,
            "clock_in_lng" => $lng,
            "status" => "proses",
        ]);
    }

    /**
     * Memproses Clock Out (Absen Pulang) Karyawan.
     */
    public function clockOut(int $userId, float $lat, float $lng): Attendance
    {
        $now = Carbon::now("Asia/Jakarta");
        $dateStr = $now->toDateString();

        // 1. Validasi Geofencing
        $distance = $this->calculateDistance(self::OFFICE_LAT, self::OFFICE_LNG, $lat, $lng);
        if ($distance > self::MAX_RADIUS_METERS) {
            throw ValidationException::withMessages([
                "attendance" => [
                    "Anda berada di luar area kantor saat melakukan absen pulang (jarak: " .
                    round($distance) .
                    " meter).",
                ],
            ]);
        }

        // 2. Cari presensi hari ini yang sedang berlangsung (status: proses)
        $attendance = Attendance::where("user_id", $userId)
            ->where("date", $dateStr)
            ->where("status", "proses")
            ->first();

        if (!$attendance) {
            throw ValidationException::withMessages([
                "attendance" => [
                    "Data presensi masuk hari ini tidak ditemukan atau sudah selesai checkout.",
                ],
            ]);
        }

        // 3. Simpan clock out dan ubah status menjadi hadir
        $attendance->update([
            "clock_out" => $now,
            "clock_out_lat" => $lat,
            "clock_out_lng" => $lng,
            "status" => "hadir",
        ]);

        return $attendance;
    }

    /**
     * Mengajukan izin / ketidakhadiran karyawan.
     */
    public function requestIzin(int $userId, string $reason): Attendance
    {
        $now = Carbon::now("Asia/Jakarta");
        $dateStr = $now->toDateString();

        $existing = Attendance::where("user_id", $userId)->where("date", $dateStr)->first();

        if ($existing) {
            throw ValidationException::withMessages([
                "attendance" => ["Anda sudah memiliki catatan presensi/izin untuk hari ini."],
            ]);
        }

        return Attendance::create([
            "user_id" => $userId,
            "date" => $dateStr,
            "status" => "izin",
            "grade" => "-",
            "notes" => $reason,
        ]);
    }

    /**
     * Mengambil data presensi hari ini untuk karyawan tertentu.
     */
    public function getTodayAttendance(int $userId): ?Attendance
    {
        $dateStr = Carbon::now("Asia/Jakarta")->toDateString();

        return Attendance::where("user_id", $userId)->where("date", $dateStr)->first();
    }

    /**
     * Mengambil riwayat presensi karyawan.
     */
    public function getAttendanceHistory(int $userId, ?string $month = null): Collection
    {
        $query = Attendance::where("user_id", $userId);

        if ($month && $month !== "all") {
            $query->where("date", "like", "{$month}%");
        }

        return $query->orderBy("date", "desc")->get();
    }

    /**
     * Mengambil semua presensi karyawan untuk Admin.
     */
    public function getAllAttendance(?string $date = null, ?int $userId = null): Collection
    {
        $query = Attendance::with("user");

        if ($date) {
            $query->where("date", $date);
        }

        if ($userId) {
            $query->where("user_id", $userId);
        }

        return $query->orderBy("date", "desc")->orderBy("created_at", "desc")->get();
    }

    /**
     * Memperbarui status / grade presensi secara manual oleh Admin.
     */
    public function updateAttendance(int $id, array $data): Attendance
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            "status" => $data["status"] ?? $attendance->status,
            "grade" => $data["grade"] ?? $attendance->grade,
            "notes" => $data["notes"] ?? $attendance->notes,
        ]);

        return $attendance;
    }
}
