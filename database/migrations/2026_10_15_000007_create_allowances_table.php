<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('allowances')) {
            Schema::create('allowances', function (Blueprint $table) {
                $table->id();
                $table->string('title', 150);
                $table->text('description')->nullable();
                $table->decimal('amount', 12, 2);
                $table->string('month', 7); // YYYY-MM
                $table->string('target_type', 20)->default('personal'); // personal, all
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignId('created_by_id')->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('allowances');
    }
};
