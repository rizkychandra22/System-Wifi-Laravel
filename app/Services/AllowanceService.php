<?php

namespace App\Services;

use App\Models\Allowance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class AllowanceService
{
    /**
     * Mencatat tunjangan / bonus baru untuk karyawan (Personal atau Global).
     */
    public function create(array $data, int $createdById): Allowance
    {
        $targetType = $data["target_type"] ?? "personal";
        $userId = null;

        if ($targetType === "personal") {
            if (empty($data["user_id"])) {
                throw ValidationException::withMessages([
                    "user_id" => ["Karyawan harus dipilih jika target penerima adalah personal."],
                ]);
            }

            $user = User::find($data["user_id"]);
            if (!$user || !$user->hasRole("employee")) {
                throw ValidationException::withMessages([
                    "user_id" => [
                        "Tunjangan hanya dapat diberikan kepada pengguna dengan role karyawan.",
                    ],
                ]);
            }
            $userId = $user->id;
        }

        $allowance = Allowance::create([
            "title" => $data["title"],
            "description" => $data["description"] ?? null,
            "amount" => (float) $data["amount"],
            "month" => $data["month"],
            "target_type" => $targetType,
            "user_id" => $userId,
            "created_by_id" => $createdById,
        ]);

        $allowance->load(["user", "createdBy"]);

        return $allowance;
    }

    /**
     * Mengambil seluruh data tunjangan / bonus untuk Admin.
     */
    public function getAll(?string $monthFilter = null): Collection
    {
        $query = Allowance::with(["user", "createdBy"]);

        if ($monthFilter && $monthFilter !== "all") {
            $query->where("month", $monthFilter);
        }

        return $query->orderBy("month", "desc")->orderBy("id", "desc")->get();
    }

    /**
     * Mengambil data tunjangan milik karyawan (Personal + Global).
     */
    public function getEmployeeAllowances(int $employeeId, ?string $monthFilter = null): Collection
    {
        $query = Allowance::with("createdBy")->where(function ($q) use ($employeeId) {
            $q->where("user_id", $employeeId)->orWhere("target_type", "global");
        });

        if ($monthFilter && $monthFilter !== "all") {
            $query->where("month", $monthFilter);
        }

        return $query->orderBy("month", "desc")->orderBy("id", "desc")->get();
    }

    /**
     * Mengambil detail tunjangan berdasarkan ID.
     */
    public function getById(int $id): Allowance
    {
        return Allowance::with(["user", "createdBy"])->findOrFail($id);
    }

    /**
     * Memperbarui data tunjangan.
     */
    public function update(int $id, array $data): Allowance
    {
        $allowance = Allowance::findOrFail($id);

        $targetType = $data["target_type"] ?? $allowance->target_type;
        $userId = $allowance->user_id;

        if ($targetType === "personal") {
            $checkUserId = $data["user_id"] ?? $userId;
            $user = User::find($checkUserId);
            if (!$user || !$user->hasRole("employee")) {
                throw ValidationException::withMessages([
                    "user_id" => ["Karyawan terpilih tidak valid."],
                ]);
            }
            $userId = $user->id;
        } else {
            $userId = null;
        }

        $allowance->update([
            "title" => $data["title"] ?? $allowance->title,
            "description" => array_key_exists("description", $data)
                ? $data["description"]
                : $allowance->description,
            "amount" => isset($data["amount"]) ? (float) $data["amount"] : $allowance->amount,
            "month" => $data["month"] ?? $allowance->month,
            "target_type" => $targetType,
            "user_id" => $userId,
        ]);

        $allowance->load(["user", "createdBy"]);

        return $allowance;
    }

    /**
     * Menghapus tunjangan (Admin only).
     */
    public function delete(int $id): void
    {
        $allowance = Allowance::findOrFail($id);
        $allowance->delete();
    }
}
