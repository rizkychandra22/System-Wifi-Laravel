# Blueprint & Rencana Migrasi: System WiFi v2.0 Monolith

> **Modul Terkait:** `feat/v2-laravel-monolith-migration`  
> **Tanggal Pembaruan:** 15 Oktober 2026  
> **Penanggung Jawab:** Lead Developer  
> **Target:** Migrasi penuh dari Go + React SPA ke Laravel 13 Monolith + Inertia React (TypeScript)  
> **Prinsip Utama:** Zero Data Loss (PostgreSQL) & Zero UI Regression

---

## 1. Analisis Kebutuhan & Ruang Lingkup

Migrasi ini menyatukan dua repository terpisah (`Golang-Backend` dan `React-Frontend`) menjadi satu project monolitik berbasis **Laravel 13** dan **Inertia.js React (TypeScript)**.

### Batasan Penting:

1. **Database PostgreSQL**: Data pengguna, paket, langganan, pembayaran, dan presensi lama tidak boleh dihapus atau di-reset.
2. **Antarmuka (UI)**: Seluruh tata letak, komponen Shadcn UI, tema Dark/Light, dan alur interaksi tetap 100% sama dengan desain saat ini.
3. **Penyederhanaan Arsitektur**: Mengeliminasi ketergantungan Docker multi-port dan kendala CORS, sehingga mudah dikembangkan secara in-house oleh tim klien.

---

## 2. Rincian Fase Eksekusi

### Fase 1: Database, Migration & Model Eloquent

1. Konfigurasi koneksi PostgreSQL `sistem-wifi` pada file `.env`.
2. Pembuatan file migration dengan pengaman `if (!Schema::hasTable(...))`:
   - `create_users_table`
   - `create_device_lockouts_table`
   - `create_wifi_packages_table`
   - `create_subscriptions_table`
   - `create_payments_table`
   - `create_attendances_table` (dengan unique index `[user_id, date]`)
   - `create_overtimes_table`
   - `create_allowances_table`
3. Pembuatan Model Eloquent di `app/Models/` beserta relasi dan casting tipe data.
4. Pembuatan `AdminUserSeeder` untuk memastikan akun admin default tersedia jika database baru.

### Fase 2: Services, Controller & Middleware

1. Implementasi `AuthService` dan `AuthController` (logika password hashing, device lock per role, sesi 3 jam).
2. Implementasi `AttendanceService` (validasi presensi GPS, status Hadir/Libur/Izin).
3. Implementasi `SubscriptionService` & `PaymentService` (kalkulasi billing bulanan & PPN).
4. Implementasi `HandleInertiaRequests` middleware untuk _global share_ props (`auth.user`, `flash.success`, `flash.error`).
5. Registrasi Task Scheduler di `routes/console.php` (Auto-checkout jam 17:01 WIB).

### Fase 3: Merakit Puzzle UI (Inertia + React + TypeScript)

1. Integrasi frontend template seperti di `Ukm-Lises` (Vite, `@app` alias, Tailwind CSS v4, Lucide React).
2. Pemindahan komponen UI atomik (`@app/Components/ui/`) dan Layouts (`AppLayout`, `AppSidebar`, `Header`).
3. Pemindahan Halaman ke `resources/js/Inertia-React-Ts/Pages/` (Admin, Employee, Customer).
4. Pengujian alur dari Login hingga Dashboard dan Laporan.
