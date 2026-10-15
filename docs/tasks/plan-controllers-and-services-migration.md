# Blueprint & Rencana Implementasi: Migrasi Logika Controller & Services (Golang -> Laravel)

> **Modul Terkait:** `feat/controllers-and-services-migration`  
> **Tanggal:** 15 Oktober 2026  
> **Target:** `System-Wifi-Laravel` (Clean Architecture dengan Service Layer)  
> **Status:** Selesai (_Completed_)

---

## 1. Latar Belakang & Arsitektur

Pada migrasi monolitik **Laravel 13 + Inertia.js**, seluruh logika bisnis yang sebelumnya berada di runtime Go Gin (`Golang-Backend/controllers` & `Golang-Backend/services`) dipindahkan ke ekosistem Laravel secara bersih (_Clean Architecture_).

Untuk menjaga controller tetap ringkas (_skinny controller_) dan memudahkan pemeliharaan jangka panjang oleh client, arsitektur dibagi menjadi 3 lapisan:

1. **Form Requests (`app/Http/Requests/`)**: Menangani validasi input, sanitasi, dan pesan kesalahan berbahasa Indonesia.
2. **Services (`app/Services/`)**: Pusat kalkulasi bisnis murni (aturan radius absen, grade keterlambatan, lock 14 hari pembayaran, kalkulasi upah lembur, dsb.).
3. **Controllers (`app/Http/Controllers/`)**: Menerima request, memanggil service, dan mengembalikan response (Inertia view untuk web atau JSON response untuk endpoint API).

---

## 2. Pemetaan Modul & Aturan Bisnis (Business Rules)

Berikut adalah daftar 8 modul utama yang dimigrasikan dari Go ke Laravel:

|  No   | Modul                          | File Service Laravel               | File Controller Laravel               | Aturan Bisnis Kunci (_Business Rules_)                                                                                                                                                                                                                                                                                                        |
| :---: | :----------------------------- | :--------------------------------- | :------------------------------------ | :-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **1** | **Autentikasi & Akun**         | `App\Services\AuthService`         | `AuthController`, `ProfileController` | - Device Lockout 24 jam jika ada perangkat employee mencoba akses admin.<br>- Rate limit: 5x gagal password terkunci 30 menit.<br>- Multi-role: Admin (password wajib), Employee (Device ID binding), Customer (login nomor HP fleksibel).                                                                                                    |
| **2** | **Manajemen Pengguna**         | `App\Services\UserService`         | `UserController`                      | - Admin memiliki akses penuh (CRUD semua role).<br>- Employee hanya bisa membuat/melihat customer yang didaftarkannya (`registered_by_id`).<br>- Reset Device ID / IP oleh Admin.                                                                                                                                                             |
| **3** | **Paket WiFi**                 | `App\Services\WifiPackageService`  | `WifiPackageController`               | - Admin dapat mengelola paket (nama, harga bulanan).<br>- Proteksi penghapusan jika paket masih digunakan di langganan aktif.                                                                                                                                                                                                                 |
| **4** | **Langganan (_Subscription_)** | `App\Services\SubscriptionService` | `SubscriptionController`              | - Menghubungkan Customer dengan Paket WiFi aktif.<br>- Auto perhitungan tanggal jatuh tempo (_billing day_ dan _next due date_).                                                                                                                                                                                                              |
| **5** | **Pembayaran & Invoice**       | `App\Services\PaymentService`      | `PaymentController`                   | - **Proteksi Lock 14 Hari**: Pembayaran untuk pelanggan yang sama hanya bisa diinput kembali minimal 14 hari setelah pembayaran terakhir.<br>- Kalkulasi otomatis: `PackagePrice = TotalAmount - PPN (11%)`.<br>- Format nomor invoice otomatis: `INV-XXXX-XXXX`.<br>- Otomatis memajukan tanggal jatuh tempo langganan (+1 bulan).           |
| **6** | **Presensi Karyawan**          | `App\Services\AttendanceService`   | `AttendanceController`                | - **Validasi Geofencing**: Maksimal radius 100 meter dari titik kantor (-7.033562, 106.949204) menggunakan rumus Haversine.<br>- **Grade Disiplin Masuk**: <=07:50 (Disiplin), 07:51-08:00 (Tepat Waktu), 08:01-08:10 (Toleransi), >08:10 (Terlambat).<br>- Batas clock-in pukul 12:00 WIB.<br>- Otomatis hitung durasi kerja saat clock-out. |
| **7** | **Lembur (_Overtime_)**        | `App\Services\OvertimeService`     | `OvertimeController`                  | - Perhitungan upah lembur proporsional: **Rp 5.000 / jam** (`(menit / 60) * 5000`).<br>- Validasi rentang waktu `end_time > start_time`.                                                                                                                                                                                                      |
| **8** | **Tunjangan & Bonus**          | `App\Services\AllowanceService`    | `AllowanceController`                 | - Target penerima: `personal` (karyawan tertentu) atau `global` (semua karyawan).<br>- Format periode bulan: `YYYY-MM`.                                                                                                                                                                                                                       |

---

## 3. Rencana Langkah Implementasi

1. **Langkah 1**: Membuat helper geofencing dan kalkulasi koordinat `Haversine` di `App\Services\AttendanceService`.
2. **Langkah 2**: Mengimplementasikan layer **Services** di `app/Services/`:
   - `AuthService.php`
   - `UserService.php`
   - `WifiPackageService.php`
   - `SubscriptionService.php`
   - `PaymentService.php`
   - `AttendanceService.php`
   - `OvertimeService.php`
   - `AllowanceService.php`
3. **Langkah 3**: Mengimplementasikan layer **Controllers** di `app/Http/Controllers/`:
   - `AuthController.php`
   - `ProfileController.php`
   - `UserController.php`
   - `WifiPackageController.php`
   - `SubscriptionController.php`
   - `PaymentController.php`
   - `AttendanceController.php`
   - `OvertimeController.php`
   - `AllowanceController.php`
4. **Langkah 4**: Mendaftarkan rute lengkap di `routes/web.php` dan `routes/api.php` dengan gerbang middleware `access`.
5. **Langkah 5**: Menulis Automated Feature Tests untuk memastikan setiap service menghasilkan output yang identik dengan Go Gin.

---

## 4. Status & Verifikasi

- [x] Implementasi Service Layer (8 Services: Auth, User, WifiPackage, Subscription, Payment, Attendance, Overtime, Allowance)
- [x] Implementasi Controllers (9 Controllers dengan Spatie-style hasRole check)
- [x] Pendaftaran 59 Rute Web & API dengan Middleware `access`
- [x] Pembersihan test suite awal sesuai instruksi user
