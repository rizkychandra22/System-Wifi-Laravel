<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Mengambil seluruh riwayat pembayaran pelanggan (Invoice).
     */
    public function getAll(): Collection
    {
        return Payment::with(["customer", "wifiPackage", "createdBy"])
            ->orderBy("created_at", "desc")
            ->get();
    }

    /**
     * Mengambil riwayat pembayaran milik pelanggan tertentu.
     */
    public function getByCustomerId(int $customerId): Collection
    {
        return Payment::with(["customer", "wifiPackage", "createdBy"])
            ->where("customer_id", $customerId)
            ->orderBy("created_at", "desc")
            ->get();
    }

    /**
     * Mengambil detail transaksi pembayaran / invoice berdasarkan ID.
     */
    public function getById(int $id): Payment
    {
        return Payment::with(["customer", "wifiPackage", "createdBy"])->findOrFail($id);
    }

    /**
     * Mencatat pembayaran baru dengan proteksi lock 14 hari dan auto-advance jatuh tempo.
     */
    public function createPayment(
        int $customerId,
        int $createdById,
        string $paymentMethod = "cash",
    ): Payment {
        // 1. Validasi pelanggan
        $customer = User::find($customerId);
        if (!$customer || !$customer->hasRole("customer")) {
            throw ValidationException::withMessages([
                "customer_id" => ["Pengguna bukan pelanggan yang sah."],
            ]);
        }

        // 2. Dapatkan data langganan aktif pelanggan
        $subscription = Subscription::with("wifiPackage")
            ->where("customer_id", $customerId)
            ->where("status", "active")
            ->first();

        if (!$subscription || !$subscription->wifiPackage) {
            throw ValidationException::withMessages([
                "customer_id" => [
                    "Pelanggan tidak memiliki langganan aktif atau langganan telah dinonaktifkan.",
                ],
            ]);
        }

        // 3. Validasi Proteksi Lock 14 Hari berdasarkan pembayaran terakhir
        $latestPayment = Payment::where("customer_id", $customerId)->latest("created_at")->first();

        if ($latestPayment && $latestPayment->created_at) {
            $daysSinceLastPayment = Carbon::parse($latestPayment->created_at)->diffInDays(
                Carbon::now(),
            );
            if ($daysSinceLastPayment < 14) {
                $nextInputDate = Carbon::parse($latestPayment->created_at)
                    ->addDays(14)
                    ->format("d-m-Y");
                $lastDate = Carbon::parse($latestPayment->created_at)->format("d-m-Y");

                throw ValidationException::withMessages([
                    "payment" => [
                        "Pembayaran terkunci. Pembayaran terakhir baru diinput pada {$lastDate}. Penginputan kembali baru dapat dilakukan setelah 14 hari (mulai {$nextInputDate}).",
                    ],
                ]);
            }
        }

        // 4. Kalkulasi harga paket dan PPN 11%
        $activePackage = $subscription->wifiPackage;
        $totalAmount = (float) $activePackage->price;
        $ppn = round($totalAmount * 0.11, 2);
        $packagePrice = round($totalAmount - $ppn, 2);

        // 5. Generate Nomor Invoice Unik (Format: INV-XXXX-XXXX)
        $invoiceNumber = sprintf("INV-%04d-%04d", random_int(1, 9999), random_int(1, 9999));

        return DB::transaction(function () use (
            $customerId,
            $subscription,
            $packagePrice,
            $ppn,
            $totalAmount,
            $paymentMethod,
            $invoiceNumber,
            $createdById,
        ) {
            // Simpan transaksi pembayaran
            $payment = Payment::create([
                "customer_id" => $customerId,
                "wifi_package_id" => $subscription->wifi_package_id,
                "package_price" => $packagePrice,
                "ppn" => $ppn,
                "total_amount" => $totalAmount,
                "status" => "paid",
                "payment_method" => $paymentMethod,
                "invoice_number" => $invoiceNumber,
                "created_by_id" => $createdById,
            ]);

            // Majukan tanggal jatuh tempo (NextDueDate) langganan 1 bulan ke depan
            $subscription->update([
                "next_due_date" => Carbon::parse($subscription->next_due_date)->addMonth(),
            ]);

            $payment->load(["customer", "wifiPackage", "createdBy"]);

            return $payment;
        });
    }

    /**
     * Memperbarui metode pembayaran.
     */
    public function updatePayment(int $id, array $data): Payment
    {
        $payment = Payment::findOrFail($id);

        if (!empty($data["payment_method"])) {
            $payment->payment_method = $data["payment_method"];
        }

        if (!empty($data["status"])) {
            $payment->status = $data["status"];
        }

        $payment->save();
        $payment->load(["customer", "wifiPackage", "createdBy"]);

        return $payment;
    }

    /**
     * Menghapus catatan pembayaran (Admin only).
     */
    public function deletePayment(int $id): void
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
    }
}
