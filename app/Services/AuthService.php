<?php

namespace App\Services;

use App\Models\DeviceLockout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Memproses login multi-role dengan aturan keamanan device lockout.
     */
    public function login(string $phone, ?string $password, ?string $deviceId): array
    {
        // 1. Cek apakah device_id sedang di-lockout
        if ($deviceId) {
            $lockout = DeviceLockout::where("device_id", $deviceId)->first();
            if (
                $lockout &&
                $lockout->locked_until &&
                Carbon::parse($lockout->locked_until)->isFuture()
            ) {
                throw ValidationException::withMessages([
                    "phone" => ["Akses login dari perangkat Anda diblokir selama 24 jam."],
                ]);
            }
        }

        // 2. Cari pengguna berdasarkan nomor telepon
        $user = User::where("phone", $phone)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                "phone" => ["Nomor telepon tidak terdaftar."],
            ]);
        }

        // 3. Logika Role Admin
        if ($user->hasRole("admin")) {
            if ($user->locked_until && Carbon::parse($user->locked_until)->isFuture()) {
                throw ValidationException::withMessages([
                    "phone" => [
                        "Akun terkunci karena terlalu banyak percobaan salah. Coba lagi dalam 30 menit.",
                    ],
                ]);
            }

            // Cegah device karyawan mencoba login admin
            if ($deviceId) {
                $isEmployeeDevice = User::where("role", "employee")
                    ->where("device_id", $deviceId)
                    ->exists();

                if ($isEmployeeDevice) {
                    DeviceLockout::updateOrCreate(
                        ["device_id" => $deviceId],
                        ["locked_until" => Carbon::now()->addHours(24)],
                    );

                    throw ValidationException::withMessages([
                        "phone" => [
                            "Perangkat ini telah diblokir selama 24 jam karena pelanggaran keamanan percobaan akses admin.",
                        ],
                    ]);
                }
            }

            if (empty($password)) {
                throw ValidationException::withMessages([
                    "password" => ["Password diperlukan untuk akun Admin."],
                ]);
            }

            if (!Hash::check($password, $user->password)) {
                $attempts = $user->failed_login_attempts + 1;

                if ($attempts >= 5) {
                    $user->update([
                        "failed_login_attempts" => 0,
                        "locked_until" => Carbon::now()->addMinutes(30),
                    ]);

                    throw ValidationException::withMessages([
                        "password" => [
                            "Terlalu banyak percobaan salah. Akun terkunci selama 30 menit.",
                        ],
                    ]);
                }

                $user->update(["failed_login_attempts" => $attempts]);

                throw ValidationException::withMessages([
                    "password" => ["Password salah."],
                ]);
            }

            // Reset percobaan salah jika login sukses
            $user->update([
                "failed_login_attempts" => 0,
                "locked_until" => null,
            ]);
        } elseif ($user->hasRole("employee")) {
            // 4. Logika Role Employee (Device Lock)
            if ($deviceId) {
                if (empty($user->device_id)) {
                    // Pertama kali login, bind device_id ke karyawan ini
                    $user->update(["device_id" => $deviceId]);
                } elseif ($user->device_id !== $deviceId) {
                    throw ValidationException::withMessages([
                        "phone" => [
                            "Akun karyawan ini sudah terdaftar di device lain. Silakan hubungi Admin.",
                        ],
                    ]);
                }
            }
        }
        // Catatan: Role 'customer' dibebaskan dari password dan device lock (passwordless)

        // 5. Login sesi Laravel
        Auth::login($user, true);

        return [
            "message" => "Login berhasil.",
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "phone" => $user->phone,
                "role" => $user->role,
                "address" => $user->address,
            ],
        ];
    }

    /**
     * Mengakhiri sesi pengguna.
     */
    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Mengambil kontak admin utama untuk bantuan.
     */
    public function getAdminContact(): ?User
    {
        return User::where("role", "admin")->first(["id", "name", "phone", "address"]);
    }

    /**
     * Memperbarui profil pengguna yang sedang login.
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            "name" => $data["name"] ?? $user->name,
            "phone" => $data["phone"] ?? $user->phone,
            "address" => array_key_exists("address", $data) ? $data["address"] : $user->address,
        ]);

        return $user;
    }

    /**
     * Memperbarui password akun admin.
     */
    public function updatePassword(User $user, string $oldPassword, string $newPassword): void
    {
        if (!$user->hasRole("admin")) {
            throw ValidationException::withMessages([
                "password" => ["Fitur ganti password hanya diperuntukkan bagi akun Admin."],
            ]);
        }

        if (!Hash::check($oldPassword, $user->password)) {
            throw ValidationException::withMessages([
                "old_password" => ["Password lama salah."],
            ]);
        }

        $user->update([
            "password" => Hash::make($newPassword),
        ]);
    }
}
