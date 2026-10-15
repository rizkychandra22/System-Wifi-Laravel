# Indeks Dokumentasi Rencana Pengembangan (Blueprint Docs)

**Sistem Manajemen WiFi & Presensi Karyawan (v2.0 Monolith)**

## 🏛️ Dokumentasi Arsitektur (Panduan Developer)

| File                                                                                      | Audience     | Isi Singkat                                                                             |
| ----------------------------------------------------------------------------------------- | ------------ | --------------------------------------------------------------------------------------- |
| [`docs/architecture/arc-backend-developer.md`](./architecture/arc-backend-developer.md)   | Backend Dev  | Pola Service-Oriented Monolith, Model Eloquent, PostgreSQL conventions, cron scheduling |
| [`docs/architecture/arc-frontend-developer.md`](./architecture/arc-frontend-developer.md) | Frontend Dev | Inertia.js React TypeScript, Radix/Shadcn UI, Layouts, ThemeProvider                    |

---

## 🚀 Daftar Task Migrasi (Status Pengerjaan)

|  No   | Modul / Fase Migrasi                               | Dokumen Markdown (`tasks/`)                                                                        | Status                  |
| :---: | :------------------------------------------------- | :------------------------------------------------------------------------------------------------- | :---------------------- |
| **0** | **Master Blueprint Migrasi v2.0**                  | [`plan-migration-v2-wifi-system.md`](./tasks/plan-migration-v2-wifi-system.md)                     | **In Progress**         |
| **1** | **Fase 1: Database, Migrations & Eloquent Models** | Terangkum di Blueprint v2.0                                                                        | **Selesai (Completed)** |
| **2** | **Fase 2A: Middleware Akses & Shared Data Global** | [`plan-middleware-and-shared-data.md`](./tasks/plan-middleware-and-shared-data.md)                 | **Selesai**             |
| **3** | **Fase 2B: Services & Controllers Logika Bisnis**  | [`plan-controllers-and-services-migration.md`](./tasks/plan-controllers-and-services-migration.md) | **Selesai**             |
| **4** | **Fase 3: Scheduler Presensi & Auto Checkout**     | Terangkum di Blueprint v2.0                                                                        | **Siap Dikerjakan**     |
| **5** | **Fase 4: Migrasi Frontend UI (Inertia React)**    | Terangkum di Blueprint v2.0                                                                        | Menunggu Fase 3         |

---

## 📌 Rincian Progress Fase 1 (Database & Models)

- [x] Backup Data Riil Pre-Migration (`database/backup_wifi_system_before_laravel.sql`)
- [x] Konfigurasi `.env` PostgreSQL (`DB_DATABASE=postgres`, port 5432)
- [x] Migration `users` (dengan `device_id`, `role`, `locked_until`, dll + `remember_token` guard)
- [x] Migration `device_lockouts`
- [x] Migration `wifi_packages`
- [x] Migration `subscriptions`
- [x] Migration `payments`
- [x] Migration `attendances` (dengan composite unique `(user_id, date)`)
- [x] Migration `overtimes`
- [x] Migration `allowances`
- [x] Model Eloquent: `User`, `DeviceLockout`, `WifiPackage`, `Subscription`, `Payment`, `Attendance`, `Overtime`, `Allowance`
- [x] Database Seeder: `AdminUserSeeder` & `DatabaseSeeder`
- [x] Validasi Query Eloquent ke Data Riil (5 Users, 4 Packages, 2 Subscriptions terdeteksi aktif)

---

## 📌 Rincian Progress Fase 2A (Middleware, Shared Data & Pure Inertia React TS)

- [x] Install & konfigurasi package: `inertiajs/inertia-laravel`, `tightenco/ziggy`, `@inertiajs/react`, `react@19`, `react-dom@19`.
- [x] Implementasi `app/Http/Middleware/Access.php` (Multi-role RBAC: `admin`, `employee`, `customer` + session check + 403 JSON support).
- [x] Implementasi `app/Http/Middleware/HandleInertiaRequests.php` (Root view: `app`, shared props: `auth.user`, `flash.success`, `flash.error`, `app_name`).
- [x] Pendaftaran Middleware di `bootstrap/app.php` (append `web` dan alias `'access'`).
- [x] Blade Template `resources/views/app.blade.php` dengan `@routes` dinamis (Bebas dari keharusan `ziggy:generate`).
- [x] Setup TypeScript `tsconfig.json` & global declaration `resources/js/types/global.d.ts` & `resources/js/types/index.d.ts`.
- [x] Entry point Inertia React: `resources/js/app.tsx` & halaman verifikasi `resources/js/Pages/Welcome.tsx`.
- [x] Konfigurasi Linter Cepat berbasis Rust: **Oxlint** (`.oxlintrc.json`, `npm run lint`).
- [x] Konfigurasi Formatter: **Prettier** (`.prettierrc`, `.prettierignore`, `.editorconfig` dengan 2 spasi untuk JS/TS/React dan 4 spasi untuk PHP).

---

## 📌 Rincian Progress Fase 2B (Services & Controllers Logika Bisnis)

- [x] Implementasi 8 Services (Clean Architecture):
  - `App\Services\AuthService`: Login multi-role, device lockout 24 jam untuk employee, rate limit 5x salah, profil & password.
  - `App\Services\UserService`: CRUD pengguna, proteksi scope employee hanya melihat customer yang didaftarkannya, reset kunci perangkat (IP/device_id).
  - `App\Services\WifiPackageService`: CRUD paket WiFi dengan proteksi penghapusan jika masih ada langganan aktif.
  - `App\Services\SubscriptionService`: Manajemen langganan paket pelanggan dengan auto tanggal jatuh tempo.
  - `App\Services\PaymentService`: Transaksi pembayaran/invoice dengan proteksi lock 14 hari, kalkulasi PPN 11%, dan auto-advance jatuh tempo +1 bulan.
  - `App\Services\AttendanceService`: Presensi karyawan dengan validasi geofencing Haversine (<=100m dari kantor), grade kedisiplinan, clock in/out, dan izin.
  - `App\Services\OvertimeService`: Pencatatan lembur dengan kalkulasi upah proporsional Rp 5.000 / jam.
  - `App\Services\AllowanceService`: Tunjangan & bonus karyawan dengan distribusi target Personal dan Global.
- [x] Implementasi 9 Controllers:
  - `AuthController`, `ProfileController`, `UserController`, `WifiPackageController`, `SubscriptionController`, `PaymentController`, `AttendanceController`, `OvertimeController`, `AllowanceController`.
  - Pengecekan peran menggunakan Spatie-style `hasRole(...)` dan `hasAnyRole(...)`.
- [x] Pendaftaran 59 Rute di `routes/web.php` terintegrasi dengan middleware `access`.
- [x] Pembersihan unit test awal sesuai instruksi user.
