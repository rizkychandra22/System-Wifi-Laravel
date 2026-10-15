<?php

namespace App\Services;

use App\Models\WifiPackage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class WifiPackageService
{
    /**
     * Mengambil semua paket WiFi yang tersedia.
     */
    public function getAll(): Collection
    {
        return WifiPackage::orderBy("price", "asc")->get();
    }

    /**
     * Mengambil detail paket WiFi berdasarkan ID.
     */
    public function getById(int $id): WifiPackage
    {
        return WifiPackage::findOrFail($id);
    }

    /**
     * Membuat paket WiFi baru (Admin only).
     */
    public function create(array $data): WifiPackage
    {
        return WifiPackage::create([
            "name" => $data["name"],
            "price" => $data["price"],
        ]);
    }

    /**
     * Memperbarui informasi paket WiFi.
     */
    public function update(int $id, array $data): WifiPackage
    {
        $package = WifiPackage::findOrFail($id);

        $package->update([
            "name" => $data["name"] ?? $package->name,
            "price" => $data["price"] ?? $package->price,
        ]);

        return $package;
    }

    /**
     * Menghapus paket WiFi dengan validasi proteksi langganan aktif.
     */
    public function delete(int $id): void
    {
        $package = WifiPackage::withCount("subscriptions")->findOrFail($id);

        if ($package->subscriptions_count > 0) {
            throw ValidationException::withMessages([
                "package" => [
                    "Paket WiFi tidak dapat dihapus karena masih digunakan oleh pelanggan aktif.",
                ],
            ]);
        }

        $package->delete();
    }
}
