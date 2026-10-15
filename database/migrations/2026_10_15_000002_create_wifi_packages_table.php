<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wifi_packages')) {
            Schema::create('wifi_packages', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->decimal('price', 12, 2);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wifi_packages');
    }
};
