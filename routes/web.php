<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get("/", function () {
    return Inertia::render("Welcome", [
        "title" => "NetVerse System WiFi",
        "laravelVersion" => app()->version(),
        "phpVersion" => PHP_VERSION,
    ]);
})->name("home");

Route::get("/login", function () {
    return Inertia::render("Welcome", [
        "title" => "Login",
    ]);
})->name("login");
