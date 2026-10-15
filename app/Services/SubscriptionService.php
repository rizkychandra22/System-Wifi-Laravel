<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Models\WifiPackage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    /**
     * Mengambil semua data langganan pelanggan.
     */
    public function getAll(): Collection
    {
        return Subscription::with(["customer", "wifiPackage", "createdBy"])
            ->orderBy("created_at", "desc")
            ->get();
    }

    /**
     * Mengambil langganan aktif milik pelanggan tertentu.
     */
    public function getByCustomerId(int $customerId): ?Subscription
    {
        return Subscription::with(["customer", "wifiPackage", "createdBy"])
            ->where("customer_id", $customerId)
            ->first();
    }

    /**
     * Membuat atau memperbarui langganan paket WiFi pelanggan.
     */
    public function createOrUpdate(array $data, int $createdById): Subscription
    {
        // 1. Validasi keberadaan dan peran pelanggan
        $customer = User::find($data["customer_id"]);
        if (!$customer || !$customer->hasRole("customer")) {
            throw ValidationException::withMessages([
                "customer_id" => ["Pengguna yang dipilih bukan merupakan pelanggan aktif."],
            ]);
        }

        // 2. Validasi paket WiFi
        $package = WifiPackage::find($data["wifi_package_id"]);
        if (!$package) {
            throw ValidationException::withMessages([
                "wifi_package_id" => ["Paket WiFi tidak ditemukan."],
            ]);
        }

        $billingDay = (int) ($data["billing_day"] ?? 1);
        $nextDueDate = isset($data["next_due_date"])
            ? Carbon::parse($data["next_due_date"])
            : Carbon::now()->addMonth();

        $status = in_array($data["status"] ?? "active", ["active", "suspended", "cancelled"], true)
            ? $data["status"]
            : "active";

        // 3. Upsert langganan pelanggan
        $subscription = Subscription::updateOrCreate(
            ["customer_id" => $customer->id],
            [
                "wifi_package_id" => $package->id,
                "billing_day" => $billingDay,
                "next_due_date" => $nextDueDate,
                "status" => $status,
                "created_by_id" => $createdById,
            ],
        );

        $subscription->load(["customer", "wifiPackage", "createdBy"]);

        return $subscription;
    }

    /**
     * Menghapus langganan (Admin only).
     */
    public function delete(int $id): void
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->delete();
    }
}
