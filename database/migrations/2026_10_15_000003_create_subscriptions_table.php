<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable("subscriptions")) {
            Schema::create("subscriptions", function (Blueprint $table) {
                $table->id();
                $table->foreignId("customer_id")->unique()->constrained("users")->cascadeOnDelete();
                $table->foreignId("wifi_package_id")->constrained("wifi_packages");
                $table->integer("billing_day");
                $table->timestamp("next_due_date");
                $table->string("status", 20)->default("active");
                $table
                    ->foreignId("created_by_id")
                    ->nullable()
                    ->constrained("users")
                    ->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("subscriptions");
    }
};
