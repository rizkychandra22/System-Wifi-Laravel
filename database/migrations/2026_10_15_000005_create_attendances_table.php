<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('date', 10); // YYYY-MM-DD
                $table->timestamp('clock_in')->nullable();
                $table->timestamp('clock_out')->nullable();
                $table->string('grade', 50)->nullable();
                $table->double('clock_in_lat')->nullable();
                $table->double('clock_in_lng')->nullable();
                $table->double('clock_out_lat')->nullable();
                $table->double('clock_out_lng')->nullable();
                $table->string('status', 20)->default('Proses'); // Proses, Hadir, Libur, Izin
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Composite Unique Index untuk mencegah absensi ganda per user per hari
                $table->unique(['user_id', 'date'], 'idx_user_attendance_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
