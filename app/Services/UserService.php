<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * Mengambil daftar pengguna berdasarkan wewenang (Role).
     * Admin melihat semua pengguna, Employee hanya melihat pelanggan yang didaftarkannya.
     */
    public function getUsers(?User $currentUser = null): Collection
    {
        $query = User::with(["registeredBy", "subscription.wifiPackage"]);

        if ($currentUser && $currentUser->hasRole("employee")) {
            $query->where("role", "customer")->where("registered_by_id", $currentUser->id);
        }

        return $query->orderBy("created_at", "desc")->get();
    }

    /**
     * Mengambil detail pengguna berdasarkan ID dengan otorisasi akses.
     */
    public function getUserById(int $id, ?User $currentUser = null): User
    {
        $user = User::with(["registeredBy", "subscription.wifiPackage"])->findOrFail($id);

        if ($currentUser && $currentUser->hasRole("employee")) {
            if ($user->role !== "customer" || $user->registered_by_id !== $currentUser->id) {
                throw ValidationException::withMessages([
                    "access" => ["Anda tidak memiliki akses untuk melihat pelanggan ini."],
                ]);
            }
        }

        return $user;
    }

    /**
     * Mendaftarkan pengguna baru (Admin atau Customer oleh Employee).
     */
    public function createUser(array $data, ?User $creator = null): User
    {
        // Validasi keunikan nomor telepon
        $existing = User::where("phone", $data["phone"])->first();
        if ($existing) {
            throw ValidationException::withMessages([
                "phone" => ["Nomor telepon sudah terdaftar di sistem."],
            ]);
        }

        // Tentukan role & registered_by
        $role = $data["role"] ?? "customer";
        $registeredById = null;

        if ($creator) {
            if ($creator->hasRole("employee")) {
                // Karyawan hanya boleh mendaftarkan customer
                $role = "customer";
                $registeredById = $creator->id;
            } elseif ($creator->hasRole("admin")) {
                $registeredById = $data["registered_by_id"] ?? $creator->id;
            }
        }

        $password = null;
        if ($role === "admin" && !empty($data["password"])) {
            $password = Hash::make($data["password"]);
        }

        return User::create([
            "name" => $data["name"],
            "phone" => $data["phone"],
            "role" => $role,
            "password" => $password,
            "address" => $data["address"] ?? null,
            "registered_by_id" => $registeredById,
        ]);
    }

    /**
     * Memperbarui data pengguna.
     */
    public function updateUser(int $id, array $data, ?User $editor = null): User
    {
        $user = User::findOrFail($id);

        if ($editor && $editor->hasRole("employee")) {
            if ($user->registered_by_id !== $editor->id) {
                throw ValidationException::withMessages([
                    "access" => ["Anda tidak memiliki wewenang untuk mengubah data pelanggan ini."],
                ]);
            }
        }

        // Cek keunikan nomor HP jika diganti
        if (isset($data["phone"]) && $data["phone"] !== $user->phone) {
            $phoneExists = User::where("phone", $data["phone"])
                ->where("id", "!=", $user->id)
                ->exists();

            if ($phoneExists) {
                throw ValidationException::withMessages([
                    "phone" => ["Nomor telepon sudah digunakan oleh pengguna lain."],
                ]);
            }
            $user->phone = $data["phone"];
        }

        if (isset($data["name"])) {
            $user->name = $data["name"];
        }

        if (array_key_exists("address", $data)) {
            $user->address = $data["address"];
        }

        // Hanya admin yang boleh mengubah role pengguna
        if (isset($data["role"]) && $editor && $editor->hasRole("admin")) {
            $user->role = $data["role"];
        }

        $user->save();

        return $user;
    }

    /**
     * Menghapus pengguna secara soft delete (Admin only).
     */
    public function deleteUser(int $id): void
    {
        $user = User::findOrFail($id);
        $user->delete();
    }

    /**
     * Me-reset Device ID (Kunci Perangkat) pengguna agar dapat login dari perangkat baru.
     */
    public function resetUserDevice(int $id): User
    {
        $user = User::findOrFail($id);
        $user->update(["device_id" => null]);

        return $user;
    }
}
