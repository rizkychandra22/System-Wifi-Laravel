<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\WifiPackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected WifiPackageService $wifiPackageService,
    ) {}

    /**
     * Menampilkan halaman langganan sesuai role.
     * Admin & Employee → daftar semua langganan pelanggan.
     * Customer         → langganan milik sendiri.
     */
    public function index(Request $request): Response|JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user?->hasRole("customer")) {
            $subscription = $this->subscriptionService->getByCustomerId($user->id);

            if ($request->wantsJson()) {
                return response()->json($subscription);
            }

            return Inertia::render("Customer/Subscription", [
                "subscription" => $subscription,
            ]);
        }

        $subscriptions = $this->subscriptionService->getAll();

        if ($request->wantsJson()) {
            return response()->json($subscriptions);
        }

        return Inertia::render("Admin/Subscriptions/Index", [
            "subscriptions" => $subscriptions,
            "packages" => $this->wifiPackageService->getAll(),
        ]);
    }

    /**
     * Menampilkan langganan milik satu pelanggan berdasarkan ID — Admin & Employee.
     */
    public function show(int $customerId, Request $request): Response|JsonResponse
    {
        $subscription = $this->subscriptionService->getByCustomerId($customerId);

        if ($request->wantsJson()) {
            return response()->json($subscription);
        }

        return Inertia::render("Admin/Subscriptions/Show", [
            "subscription" => $subscription,
        ]);
    }

    /**
     * Membuat atau memperbarui langganan paket pelanggan — Admin & Employee.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            "customer_id" => ["required", "integer", "exists:users,id"],
            "wifi_package_id" => ["required", "integer", "exists:wifi_packages,id"],
            "billing_day" => ["nullable", "integer", "min:1", "max:31"],
            "next_due_date" => ["nullable", "date"],
            "status" => ["nullable", "string", "in:active,suspended,cancelled"],
        ]);

        $subscription = $this->subscriptionService->createOrUpdate($validated, Auth::id());

        if ($request->wantsJson()) {
            return response()->json(
                [
                    "message" => "Langganan berhasil disimpan.",
                    "subscription" => $subscription,
                ],
                201,
            );
        }

        return back()->with("success", "Langganan berhasil disimpan.");
    }

    /**
     * Menghapus data langganan — Admin only.
     */
    public function destroy(int $id, Request $request): JsonResponse|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user?->hasRole("admin")) {
            abort(403, "Hanya Admin yang dapat menghapus data langganan.");
        }

        $this->subscriptionService->delete($id);

        if ($request->wantsJson()) {
            return response()->json(["message" => "Langganan berhasil dihapus."]);
        }

        return back()->with("success", "Langganan berhasil dihapus.");
    }
}
