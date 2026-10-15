<?php

use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WifiPackageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ==========================================
// RUTE PUBLIK (Authentication & Information)
// ==========================================
Route::get("/", function () {
    return redirect()->route("login");
})->name("home");

Route::get("/login", [AuthController::class, "showLogin"])->name("login");
Route::post("/login", [AuthController::class, "login"])->name("login.post");
Route::post("/logout", [AuthController::class, "logout"])->name("logout");
Route::get("/admin-contact", [AuthController::class, "adminContact"])->name("admin.contact");

// ==========================================
// RUTE TERAUTENTIKASI DENGAN MIDDLEWARE ACCESS
// ==========================================
Route::middleware(["access:admin,employee,customer"])->group(function () {
    // Pengaturan Profil & Ganti Password
    Route::put("/profile", [ProfileController::class, "update"])->name("profile.update");
    Route::put("/profile/password", [ProfileController::class, "updatePassword"])->name(
        "profile.password",
    );

    // Customer Protected Routes
    Route::prefix("customer")
        ->name("customer.")
        ->group(function () {
            Route::get("/subscription", [SubscriptionController::class, "show"])->name(
                "subscription",
            );
            Route::get("/payments", [PaymentController::class, "history"])->name("payments");
        });

    // Employee & Admin Shared Resource Management
    Route::middleware(["access:admin,employee"])->group(function () {
        Route::resource("users", UserController::class)->only(["index", "store", "show", "update"]);
        Route::resource("subscriptions", SubscriptionController::class)->only([
            "index",
            "store",
            "show",
        ]);
        Route::resource("payments", PaymentController::class)->only([
            "index",
            "store",
            "show",
            "update",
        ]);
        Route::resource("overtimes", OvertimeController::class);
        Route::resource("allowances", AllowanceController::class);
    });

    // Employee Attendance Routes
    Route::middleware(["access:employee"])
        ->prefix("employee")
        ->name("employee.")
        ->group(function () {
            Route::get("/attendance", [AttendanceController::class, "employeePage"])->name(
                "attendance",
            );
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

    // Admin Exclusive Routes
    Route::middleware(["access:admin"])
        ->prefix("admin")
        ->name("admin.")
        ->group(function () {
            Route::get(
                "/dashboard",
                fn() => Inertia::render("Welcome", ["title" => "Admin Dashboard"]),
            )->name("dashboard");
            Route::delete("/users/{id}", [UserController::class, "destroy"])->name("users.destroy");
            Route::put("/users/{id}/reset-device", [UserController::class, "resetDevice"])->name(
                "users.resetDevice",
            );

            Route::resource("wifi-packages", WifiPackageController::class);
            Route::delete("/subscriptions/{id}", [SubscriptionController::class, "destroy"])->name(
                "subscriptions.destroy",
            );
            Route::delete("/payments/{id}", [PaymentController::class, "destroy"])->name(
                "payments.destroy",
            );

            Route::get("/attendance", [AttendanceController::class, "adminIndex"])->name(
                "attendance.index",
            );
            Route::put("/attendance/{id}", [AttendanceController::class, "update"])->name(
                "attendance.update",
            );
        });
});
