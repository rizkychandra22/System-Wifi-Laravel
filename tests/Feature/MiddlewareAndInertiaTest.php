<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MiddlewareAndInertiaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register dummy routes for testing Access middleware
        Route::middleware(["web", "access:admin"])->get("/test-admin-only", function () {
            return response()->json(["status" => "admin_ok"]);
        });

        Route::middleware(["web", "access:employee,admin"])->get(
            "/test-employee-access",
            function () {
                return response()->json(["status" => "staff_ok"]);
            },
        );
    }

    protected function makeUser(string $role): User
    {
        $user = new User([
            "name" => ucfirst($role) . " User",
            "phone" => "081234567890",
            "role" => $role,
        ]);
        $user->id = rand(1, 999);
        $user->exists = true;

        return $user;
    }

    public function test_inertia_welcome_page_renders_with_shared_props(): void
    {
        $response = $this->get("/");

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component("Welcome")
                ->has("auth")
                ->has("flash")
                ->has("app_name"),
        );
    }

    public function test_access_middleware_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get("/test-admin-only");

        $response->assertRedirect(route("login"));
        $response->assertSessionHas("error", "Sesi login telah berakhir. Silakan login kembali.");
    }

    public function test_access_middleware_allows_user_with_valid_role(): void
    {
        $admin = $this->makeUser("admin");

        $response = $this->actingAs($admin)->get("/test-admin-only");

        $response->assertStatus(200);
        $response->assertJson(["status" => "admin_ok"]);
    }

    public function test_access_middleware_blocks_user_with_invalid_role(): void
    {
        $customer = $this->makeUser("customer");

        $response = $this->actingAs($customer)->get("/test-admin-only");

        $response->assertRedirect(route("login"));
        $response->assertSessionHas("error", "Anda tidak memiliki akses ke halaman tersebut.");
    }

    public function test_access_middleware_returns_json_403_for_json_request(): void
    {
        $customer = $this->makeUser("customer");

        $response = $this->actingAs($customer)->getJson("/test-admin-only");

        $response->assertStatus(403);
        $response->assertJson([
            "message" => "Anda tidak memiliki akses untuk melakukan tindakan ini.",
        ]);
    }
}
