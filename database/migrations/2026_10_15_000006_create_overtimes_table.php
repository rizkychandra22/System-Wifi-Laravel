<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('overtimes')) {
            Schema::create('overtimes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('title', 150);
                $table->text('description')->nullable();
                $table->string('date', 10); // YYYY-MM-DD
                $table->timestamp('start_time')->nullable();
                $table->timestamp('end_time')->nullable();
                $table->decimal('price', 12, 2)->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
