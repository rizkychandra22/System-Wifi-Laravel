# Indeks Dokumentasi Rencana Pengembangan (Blueprint Docs)

**Sistem Manajemen WiFi & Presensi Karyawan (v2.0 Monolith)**

## 🏛️ Dokumentasi Arsitektur (Panduan Developer)

| File | Audience | Isi Singkat |
|---|---|---|
| [`docs/architecture/arc-backend-developer.md`](./architecture/arc-backend-developer.md) | Backend Dev | Pola Service-Oriented Monolith, Model Eloquent, PostgreSQL conventions, cron scheduling |
| [`docs/architecture/arc-frontend-developer.md`](./architecture/arc-frontend-developer.md) | Frontend Dev | Inertia.js React TypeScript, Radix/Shadcn UI, Layouts, ThemeProvider |

---

## 🚀 Daftar Task Migrasi (Status Pengerjaan)

| No | Modul / Fase Migrasi | Dokumen Markdown (`tasks/`) | Status |
| :-: | :--- | :--- | :--- |
| **0** | **Master Blueprint Migrasi v2.0** | [`plan-migration-v2-wifi-system.md`](./tasks/plan-migration-v2-wifi-system.md) | **In Progress** |
| **1** | **Fase 1: Database, Migrations & Eloquent Models** | Terangkum di Blueprint v2.0 | **Selesai (Completed)** |
| **2** | **Fase 2: Services, Form Requests & Middlewares** | Terangkum di Blueprint v2.0 | **Siap Dikerjakan** |
| **3** | **Fase 3: Scheduler Presensi & Auto Checkout** | Terangkum di Blueprint v2.0 | Menunggu Fase 2 |
| **4** | **Fase 4: Migrasi Frontend UI (Inertia React)** | Terangkum di Blueprint v2.0 | Menunggu Fase 3 |

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
