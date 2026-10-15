# Indeks Dokumentasi Rencana Pengembangan (Blueprint Docs)

**Sistem Manajemen WiFi & Presensi Karyawan (v2.0 Monolith)**

## 🏛️ Dokumentasi Arsitektur (Panduan Developer)

| File                                                                                      | Audience     | Isi Singkat                                                                             |
| ----------------------------------------------------------------------------------------- | ------------ | --------------------------------------------------------------------------------------- |
| [`docs/architecture/arc-backend-developer.md`](./architecture/arc-backend-developer.md)   | Backend Dev  | Pola Service-Oriented Monolith, Model Eloquent, PostgreSQL conventions, cron scheduling |
| [`docs/architecture/arc-frontend-developer.md`](./architecture/arc-frontend-developer.md) | Frontend Dev | Inertia.js React TypeScript, Radix/Shadcn UI, Layouts, ThemeProvider                    |

---

## 🚀 Daftar Task Migrasi (Status Pengerjaan)

|  No   | Modul / Fase Migrasi                               | Dokumen Markdown (`tasks/`)                                                        | Status                  |
| :---: | :------------------------------------------------- | :--------------------------------------------------------------------------------- | :---------------------- |
| **0** | **Master Blueprint Migrasi v2.0**                  | [`plan-migration-v2-wifi-system.md`](./tasks/plan-migration-v2-wifi-system.md)     | **In Progress**         |
| **1** | **Fase 1: Database, Migrations & Eloquent Models** | Terangkum di Blueprint v2.0                                                        | **Selesai (Completed)** |
| **2** | **Fase 2A: Middleware Akses & Shared Data Global** | [`plan-middleware-and-shared-data.md`](./tasks/plan-middleware-and-shared-data.md) | **Selesai**             |
| **3** | **Fase 2B: Services & Controllers Logika Bisnis**  | Terangkum di Blueprint v2.0                                                        | **Siap Dikerjakan**     |
| **4** | **Fase 3: Scheduler Presensi & Auto Checkout**     | Terangkum di Blueprint v2.0                                                        | Menunggu Fase 2B        |
| **5** | **Fase 4: Migrasi Frontend UI (Inertia React)**    | Terangkum di Blueprint v2.0                                                        | Menunggu Fase 3         |

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
- [x] Automated Feature Testing: `tests/Feature/MiddlewareAndInertiaTest.php` (7 tests, 23 assertions passed 100%).
