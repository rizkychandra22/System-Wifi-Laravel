<?php

namespace App\Services;

use App\Models\Overtime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class OvertimeService
{
    public const HOURLY_RATE = 5000.0;

    /**
     * Menghitung upah lembur secara proporsional (Rp 5.000 / jam).
     */
    public function calculateOvertimePay(Carbon $startTime, Carbon $endTime): float
    {
        $durationMinutes = $startTime->diffInMinutes($endTime, false);

        if ($durationMinutes < 0) {
            throw ValidationException::withMessages([
                "end_time" => ["Waktu selesai lembur harus lebih besar dari waktu mulai."],
            ]);
        }

        return round(($durationMinutes / 60.0) * self::HOURLY_RATE);
    }

    /**
     * Mencatat data lembur karyawan.
     */
    public function create(array $data): Overtime
    {
        $startTime = Carbon::parse($data["start_time"]);
        $endTime = Carbon::parse($data["end_time"]);
        $price = $this->calculateOvertimePay($startTime, $endTime);

        return Overtime::create([
            "user_id" => $data["user_id"],
            "title" => $data["title"],
            "description" => $data["description"] ?? null,
            "date" => $data["date"],
            "start_time" => $startTime,
            "end_time" => $endTime,
            "price" => $price,
        ]);
    }

    /**
     * Mengambil seluruh data lembur karyawan untuk Admin.
     */
    public function getAll(): Collection
    {
        return Overtime::with("user")
            ->orderBy("date", "desc")
            ->orderBy("start_time", "desc")
            ->get();
    }

    /**
     * Mengambil data lembur milik karyawan tertentu.
     */
    public function getByUser(int $userId): Collection
    {
        return Overtime::with("user")
            ->where("user_id", $userId)
            ->orderBy("date", "desc")
            ->orderBy("start_time", "desc")
            ->get();
    }

    /**
     * Mengambil detail lembur berdasarkan ID.
     */
    public function getById(int $id): Overtime
    {
        return Overtime::with("user")->findOrFail($id);
    }

    /**
     * Memperbarui data lembur.
     */
    public function update(int $id, array $data): Overtime
    {
        $overtime = Overtime::findOrFail($id);

        $startTime = isset($data["start_time"])
            ? Carbon::parse($data["start_time"])
            : Carbon::parse($overtime->start_time);
        $endTime = isset($data["end_time"])
            ? Carbon::parse($data["end_time"])
            : Carbon::parse($overtime->end_time);
        $price = $this->calculateOvertimePay($startTime, $endTime);

        $overtime->update([
            "user_id" => $data["user_id"] ?? $overtime->user_id,
            "title" => $data["title"] ?? $overtime->title,
            "description" => array_key_exists("description", $data)
                ? $data["description"]
                : $overtime->description,
            "date" => $data["date"] ?? $overtime->date,
            "start_time" => $startTime,
            "end_time" => $endTime,
            "price" => $price,
        ]);

        return $overtime;
    }

    /**
     * Menghapus catatan lembur.
     */
    public function delete(int $id): void
    {
        $overtime = Overtime::findOrFail($id);
        $overtime->delete();
    }
}
