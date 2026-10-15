<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = "app";

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            "auth" => [
                "user" => $request->user()
                    ? [
                        "id" => $request->user()->id,
                        "name" => $request->user()->name,
                        "phone" => $request->user()->phone,
                        "role" => $request->user()->role,
                        "address" => $request->user()->address,
                        "device_id" => $request->user()->device_id,
                        "registered_by_id" => $request->user()->registered_by_id,
                    ]
                    : null,
            ],
            "flash" => [
                "success" => fn() => $request->session()->get("success"),
                "error" => fn() => $request->session()->get("error"),
            ],
            "app_name" => config("app.name", "NetVerse System WiFi"),
        ];
    }
}
