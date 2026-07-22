<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('required_purchases')->default(0);
            $table->enum('down_payment_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('down_payment_value', 10, 2)->default(0);
            $table->integer('installments_count')->default(1);
            $table->enum('payment_frequency', ['weekly', 'biweekly', 'monthly'])->default('monthly');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_levels');
    }
};
