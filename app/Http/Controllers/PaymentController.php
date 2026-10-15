<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * Menampilkan daftar semua transaksi pembayaran / invoice.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $payments = $this->paymentService->getAll();

        if ($request->wantsJson()) {
            return response()->json($payments);
        }

        return Inertia::render("Admin/Payments/Index", [
            "payments" => $payments,
        ]);
    }

    /**
     * Menampilkan riwayat pembayaran milik pelanggan tertentu.
     */
    public function history(int $customerId, Request $request): Response|JsonResponse
    {
        $payments = $this->paymentService->getByCustomerId($customerId);

        if ($request->wantsJson()) {
            return response()->json($payments);
        }

        return Inertia::render("Customer/PaymentHistory", [
            "payments" => $payments,
        ]);
    }

    /**
     * Menampilkan detail satu invoice pembayaran.
     */
    public function show(int $id, Request $request): Response|JsonResponse
    {
        $payment = $this->paymentService->getById($id);

        if ($request->wantsJson()) {
            return response()->json($payment);
        }

        return Inertia::render("Admin/Payments/Invoice", [
            "payment" => $payment,
        ]);
    }

    /**
     * Mencatat transaksi pembayaran baru (dengan validasi lock 14 hari).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "customer_id" => ["required", "integer", "exists:users,id"],
            "payment_method" => ["nullable", "string", "in:cash,transfer,qris"],
        ]);

        $payment = $this->paymentService->createPayment(
            $validated["customer_id"],
            Auth::id(),
            $validated["payment_method"] ?? "cash",
        );

        if ($request->wantsJson()) {
            return response()->json(
                [
                    "message" => "Pembayaran berhasil dicatat.",
                    "payment" => $payment,
                ],
                201,
            );
        }

        return back()->with(
            "success",
            "Pembayaran invoice #{$payment->invoice_number} berhasil dicatat.",
        );
    }

    /**
     * Memperbarui metode atau status pembayaran.
     */
    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "payment_method" => ["nullable", "string"],
            "status" => ["nullable", "string", "in:paid,pending,cancelled"],
        ]);

        $payment = $this->paymentService->updatePayment($id, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                "message" => "Data pembayaran berhasil diperbarui.",
                "payment" => $payment,
            ]);
        }

        return back()->with("success", "Data pembayaran berhasil diperbarui.");
    }

    /**
     * Menghapus invoice pembayaran (Admin only).
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $this->paymentService->deletePayment($id);

        if ($request->wantsJson()) {
            return response()->json(["message" => "Catatan pembayaran berhasil dihapus."]);
        }

        return back()->with("success", "Catatan pembayaran berhasil dihapus.");
    }
}
