<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('device_lockouts')) {
            Schema::create('device_lockouts', function (Blueprint $table) {
                $table->id();
                $table->string('device_id', 255)->unique();
                $table->timestamp('locked_until');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('device_lockouts');
    }
};
