<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->foreignId('currency_id')->constrained('currencies');
            $table->string('payment_method_name'); // For snapshot
            $table->decimal('amount_base', 12, 2); // USD amount
            $table->decimal('amount_local', 12, 2); // Original currency amount
            $table->decimal('exchange_rate', 12, 4); // Rate at the time
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
