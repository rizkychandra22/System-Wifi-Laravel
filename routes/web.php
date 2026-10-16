<?php

use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WifiPackageController;
use Illuminate\Support\Facades\Route;

// ==========================================
// RUTE PUBLIK (Authentication & Information)
// ==========================================
Route::get("/", fn() => redirect()->route("login"))->name("home");

Route::get("/login", [AuthController::class, "showLogin"])->name("login");
Route::post("/login", [AuthController::class, "login"])->name("login.post");
Route::post("/logout", [AuthController::class, "logout"])->name("logout");
Route::get("/admin-contact", [AuthController::class, "adminContact"])->name("admin.contact");

// ==========================================
// RUTE TERAUTENTIKASI — Semua Role (URL Unified)
// ==========================================
Route::middleware(["access:admin,employee,customer"])->group(function () {
    // Dashboard — semua role, tampilan dibedakan di frontend berdasarkan role
    Route::get("/dashboard", [DashboardController::class, "index"])->name("dashboard");

    // Profil & Ganti Password
    Route::put("/profile", [ProfileController::class, "update"])->name("profile.update");
    Route::put("/profile/password", [ProfileController::class, "updatePassword"])->name(
        "profile.password",
    );

    // Pengguna — Admin & Employee (internal: employee hanya lihat pelanggan milik sendiri)
    Route::middleware(["access:admin,employee"])->group(function () {
        Route::get("/users", [UserController::class, "index"])->name("users.index");
        Route::post("/users", [UserController::class, "store"])->name("users.store");
        Route::get("/users/{id}", [UserController::class, "show"])->name("users.show");
        Route::put("/users/{id}", [UserController::class, "update"])->name("users.update");
        // Admin only — diproteksi di dalam controller
        Route::delete("/users/{id}", [UserController::class, "destroy"])->name("users.destroy");
        Route::put("/users/{id}/reset-device", [UserController::class, "resetDevice"])->name(
            "users.resetDevice",
        );
    });

    // Langganan — Admin & Employee + Customer (internal: customer lihat milik sendiri)
    Route::get("/subscriptions", [SubscriptionController::class, "index"])->name(
        "subscriptions.index",
    );
    Route::middleware(["access:admin,employee"])->group(function () {
        Route::post("/subscriptions", [SubscriptionController::class, "store"])->name(
            "subscriptions.store",
        );
        Route::get("/subscriptions/{id}", [SubscriptionController::class, "show"])->name(
            "subscriptions.show",
        );
        // Admin only — diproteksi di dalam controller
        Route::delete("/subscriptions/{id}", [SubscriptionController::class, "destroy"])->name(
            "subscriptions.destroy",
        );
    });

    // Pembayaran — Admin & Employee + Customer (internal: customer lihat milik sendiri)
    Route::get("/payments", [PaymentController::class, "index"])->name("payments.index");
    Route::middleware(["access:admin,employee"])->group(function () {
        Route::post("/payments", [PaymentController::class, "store"])->name("payments.store");
        Route::get("/payments/{id}", [PaymentController::class, "show"])->name("payments.show");
        // Admin only — diproteksi di dalam controller
        Route::put("/payments/{id}", [PaymentController::class, "update"])->name("payments.update");
        Route::delete("/payments/{id}", [PaymentController::class, "destroy"])->name(
            "payments.destroy",
        );
    });

    // Paket WiFi — semua role bisa lihat, mutasi diproteksi di controller (Admin only)
    Route::get("/wifi-packages", [WifiPackageController::class, "index"])->name(
        "wifi-packages.index",
    );
    Route::middleware(["access:admin"])->group(function () {
        Route::post("/wifi-packages", [WifiPackageController::class, "store"])->name(
            "wifi-packages.store",
        );
        Route::put("/wifi-packages/{id}", [WifiPackageController::class, "update"])->name(
            "wifi-packages.update",
        );
        Route::delete("/wifi-packages/{id}", [WifiPackageController::class, "destroy"])->name(
            "wifi-packages.destroy",
        );
    });

    // Presensi — Admin & Employee (internal: admin lihat semua, employee lihat sendiri)
    Route::middleware(["access:admin,employee"])->group(function () {
        Route::get("/attendance", [AttendanceController::class, "index"])->name("attendance.index");
        // Admin only — diproteksi di dalam controller
        Route::put("/attendance/{id}", [AttendanceController::class, "update"])->name(
            "attendance.update",
        );
        // Employee only actions
        Route::middleware(["access:employee"])->group(function () {
            Route::post("/attendance/clock-in", [AttendanceController::class, "clockIn"])->name(
                "attendance.clockIn",
            );
            Route::post("/attendance/clock-out", [AttendanceController::class, "clockOut"])->name(
                "attendance.clockOut",
            );
            Route::post("/attendance/izin", [AttendanceController::class, "izin"])->name(
                "attendance.izin",
            );
            Route::get("/attendance/today", [AttendanceController::class, "today"])->name(
                "attendance.today",
            );
            Route::get("/attendance/history", [AttendanceController::class, "history"])->name(
                "attendance.history",
            );
        });
    });

    // Lembur — Admin & Employee (internal: admin lihat semua, employee lihat sendiri)
    Route::middleware(["access:admin,employee"])->group(function () {
        Route::get("/overtimes", [OvertimeController::class, "index"])->name("overtimes.index");
        Route::post("/overtimes", [OvertimeController::class, "store"])->name("overtimes.store");
        Route::get("/overtimes/{id}", [OvertimeController::class, "show"])->name("overtimes.show");
        Route::put("/overtimes/{id}", [OvertimeController::class, "update"])->name(
            "overtimes.update",
        );
        Route::delete("/overtimes/{id}", [OvertimeController::class, "destroy"])->name(
            "overtimes.destroy",
        );
    });

    // Tunjangan / Bonus — Admin & Employee (internal: admin lihat semua, employee lihat sendiri)
    Route::middleware(["access:admin,employee"])->group(function () {
        Route::get("/allowances", [AllowanceController::class, "index"])->name("allowances.index");
        Route::post("/allowances", [AllowanceController::class, "store"])->name("allowances.store");
        Route::get("/allowances/{id}", [AllowanceController::class, "show"])->name(
            "allowances.show",
        );
        Route::put("/allowances/{id}", [AllowanceController::class, "update"])->name(
            "allowances.update",
        );
        Route::delete("/allowances/{id}", [AllowanceController::class, "destroy"])->name(
            "allowances.destroy",
        );
    });
});
