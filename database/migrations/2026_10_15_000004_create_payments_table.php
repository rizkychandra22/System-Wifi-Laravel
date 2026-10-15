<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('users');
                $table->foreignId('wifi_package_id')->constrained('wifi_packages');
                $table->decimal('package_price', 12, 2);
                $table->decimal('ppn', 12, 2);
                $table->decimal('total_amount', 12, 2);
                $table->string('status', 20)->default('paid');
                $table->string('payment_method', 50)->default('Cash');
                $table->string('invoice_number', 100)->nullable();
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
