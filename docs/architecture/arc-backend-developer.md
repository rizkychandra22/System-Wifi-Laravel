# Arsitektur Backend Developer - WiFi & Attendance System (v2.0)

> **Stack**: Laravel 13 • PostgreSQL 15+ • PHP 8.3+  
> **Pola**: Service-Oriented Monolith • Role-Based Access Control (Spatie / Policy)  
> **Tanggal**: 15 Oktober 2026

---

## 🏛️ Pola Arsitektur: Service-Oriented Monolith

Pola ini diadopsi dari standar project `Ukm-Lises` untuk memastikan kode backend tetap ramping, mudah diuji, dan mudah dipahami oleh tim klien secara mandiri.

```
Request  --->  Controller  --->  Service  --->  Model / DB  --->  Response / Inertia
```

| Layer | Tanggung Jawab | Dilarang |
|---|---|---|
| **Controller** | Validasi input (`$request->validate()`), panggil method Service, return Inertia/Redirect | Menulis query database mentah, menulis logika bisnis panjang |
| **Service** | Seluruh logika bisnis (kalkulasi billing, presensi GPS, device lock), transaksi DB | Mengakses global helper `request()` secara langsung |
| **Model** | Definisi `$fillable`, `$casts`, relasi Eloquent (`hasMany`, `belongsTo`) | Menampung logika bisnis berat |

---

## 📁 Struktur Direktori Backend

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php            # Login multi-role, logout, session
│   │   ├── DashboardController.php       # Beranda statistik (Admin, Employee, Customer)
│   │   ├── UserController.php            # Manajemen pengguna & reset device_id
│   │   ├── AttendanceController.php      # Presensi GPS masuk/pulang
│   │   ├── WifiPackageController.php     # Paket internet WiFi
│   │   ├── SubscriptionController.php    # Pendaftaran & jatuh tempo langganan
│   │   ├── PaymentController.php         # Verifikasi & invoice pembayaran
│   │   ├── OvertimeController.php        # Pengajuan & persetujuan lembur
│   │   ├── AllowanceController.php       # Pengajuan tunjangan karyawan
│   │   └── ReportController.php          # Rekapitulasi absensi & slip gaji
│   └── Middleware/
│       ├── HandleInertiaRequests.php     # Shared props global ke frontend
│       └── RoleMiddleware.php            # Proteksi akses per role
│
├── Models/
│   ├── User.php                          # Akun pengguna (Admin, Employee, Customer)
│   ├── DeviceLockout.php                 # Log pemblokiran perangkat 24 jam
│   ├── WifiPackage.php                   # Master paket internet
│   ├── Subscription.php                  # Data langganan pelanggan
│   ├── Payment.php                       # Riwayat tagihan & pembayaran
│   ├── Attendance.php                    # Data presensi harian karyawan
│   ├── Overtime.php                      # Lembur karyawan
│   └── Allowance.php                     # Tunjangan karyawan
│
└── Services/                             # SELURUH LOGIKA BISNIS DI SINI
    ├── AuthService.php                   # Autentikasi & device lock guard
    ├── UserService.php                   # CRUD user & reset device
    ├── AttendanceService.php             # Presensi GPS & anti-duplicate
    ├── SubscriptionService.php           # Siklus billing bulanan
    ├── PaymentService.php                # Kalkulasi PPN & status invoice
    ├── OvertimeService.php               # Alur persetujuan lembur
    └── AllowanceService.php              # Alur tunjangan
```

---

## 🔒 Aturan Keamanan & Integritas Data

1. **Device Lock Policy**:
   - Khusus `role === 'employee'`: Wajib mencocokkan `device_id`. Jika mismatch, tolak login.
   - Untuk `role === 'customer'` dan `role === 'admin'`: Bebas login dari berbagai perangkat.
   - Jika perangkat karyawan mencoba login ke akun admin, blokir perangkat selama 24 jam di tabel `device_lockouts`.
2. **Anti-Duplicate Attendance**:
   - Menggunakan composite unique constraint `(user_id, date)` di level database.
3. **Password Hashing**:
   - Menggunakan `Hash::make()` (Bcrypt standar), 100% kompatibel dengan hash Go sebelumnya.
