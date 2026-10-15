# Blueprint & Rencana Implementasi: Middleware Akses & Shared Data Global (Inertia)

> **Modul Terkait:** `feat/middleware-and-shared-data`  
> **Tanggal:** 15 Oktober 2026  
> **Target:** `System-Wifi-Laravel`
> **Penanggung Jawab:** Lead Developer  
> **Status:** Selesai (*Completed & Tested*)

---

## 1. Latar Belakang & Tujuan

Pada arsitektur monolitik modern berbasis **Laravel 13 + Inertia.js (React + TypeScript)**:

1. **Otorisasi Multi-Role**: Sistem WiFi memiliki 3 jenis peran pengguna (`admin`, `employee`, dan `customer`). Kita membutuhkan satu gerbang middleware otorisasi yang terpusat (`Access.php`) untuk memastikan pengguna hanya bisa membuka halaman dan fitur yang diizinkan sesuai perannya.
2. **Shared Data Global**: Komponen antarmuka React di sisi frontend membutuhkan data tertentu yang selalu tersedia di setiap halaman (seperti info pengguna yang sedang login, role, nomor telepon, dan pesan flash notifikasi toast `success`/`error`). Hal ini ditangani secara efisien oleh middleware `HandleInertiaRequests.php` tanpa perlu memanggil API berulang kali.

---

## 2. Diagram Alur Kerja Request

```
User Browser Request
        │
        ▼
[ Laravel Web Middleware Pipeline ]
        │
        ▼
[ HandleInertiaRequests ] ──► Menyisipkan data global (auth.user, flash, app_version)
        │
        ▼
[ Access:admin,employee ] ──► Memeriksa Auth::check() & role pengguna
        │
   ┌────┴──────────────────────────┐
   │                               │
 [ Cocok ]                   [ Tidak Cocok / Belum Login ]
   │                               │
   ▼                               ▼
Controller / Page Inertia   Redirect ke /login dengan pesan Flash Toast
```

---

## 3. Rincian Komponen yang Dibuat

### A. Dependensi Kunci

- `inertiajs/inertia-laravel`: Package utama untuk menghubungkan controller Laravel ke frontend React.
- `tightenco/ziggy`: Memungkinkan pemanggilan rute Laravel bernyawa di dalam React (`route('admin.users')`).

### B. Middleware Otorisasi: `app/Http/Middleware/Access.php`

- Menerima parameter peran variadic: `access:admin`, `access:admin,employee`, atau `access:customer`.
- **Logika Keamanan**:
  1. Jika pengguna belum login (`!Auth::check()`):
     - Redirect ke route `login` dengan pesan: _"Sesi login telah berakhir. Silakan login kembali."_
  2. Jika pengguna sudah login, periksa apakah `$user->role` ada dalam daftar `$roles`:
     - Jika cocok: Izinkan request melanjutkan ke controller (`$next($request)`).
     - Jika tidak cocok: Logout sesi secara aman, invalidate token, dan redirect ke login dengan pesan error: _"Anda tidak memiliki akses ke halaman tersebut."_ (atau HTTP 403 jika request API/JSON).

### C. Middleware Shared Data: `app/Http/Middleware/HandleInertiaRequests.php`

- Mengatur _root view_ template: `app.blade.php`.
- Menginjeksi _Shared Props_ ke setiap halaman React:
  - **`auth.user`**: ID, Nama, No. HP, Role, Alamat (data akun aktif).
  - **`flash`**: Pesan flash session (`success` & `error`), otomatis disambungkan ke komponen Sonner Toast.
  - **`app_name`** & **`app_version`**: Metadata aplikasi.

### D. Konfigurasi `bootstrap/app.php`

- Mendaftarkan `HandleInertiaRequests` ke dalam grup middleware `web`.
- Mendaftarkan alias middleware `'access'` agar siap digunakan di file `routes/web.php`:
  ```php
  Route::middleware(['access:admin'])->prefix('dashboard')->group(function () { ... });
  ```

---

## 4. Rencana Verifikasi & Validasi

1. Menjalankan `composer require inertiajs/inertia-laravel tightenco/ziggy`.
2. Pengujian unit/tinker untuk memastikan middleware `Access` memfilter user dengan benar.
3. Pengujian respon `Inertia::render()` untuk memastikan shared props (`auth.user` & `flash`) berhasil diterima oleh komponen.
