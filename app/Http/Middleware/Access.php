<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Access
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()
                ->route("login")
                ->with("error", "Sesi login telah berakhir. Silakan login kembali.");
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(
                [
                    "message" => "Anda tidak memiliki akses untuk melakukan tindakan ini.",
                ],
                403,
            );
        }

        return redirect()
            ->route("login")
            ->with("error", "Anda tidak memiliki akses ke halaman tersebut.");
    }
}
