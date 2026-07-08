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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->string('code');
            $table->string('description');
            $table->decimal('value', 18, 2)->nullable();
            $table->decimal('max_change_amount', 18, 2)->default(0);
            $table->decimal('min_purchase_amount', 18, 2)->default(0);
            $table->boolean('is_real_denomination')->default(false);
            $table->boolean('allows_change')->default(false);
            $table->boolean('used_in_pos')->default(true);
            $table->boolean('electronic_verification')->default(false);
            $table->boolean('cash_advance')->default(false);
            $table->boolean('admin_serial')->default(false);
            $table->boolean('auto_declare')->default(false);
            $table->boolean('auto_deposit')->default(false);
            $table->boolean('used_in_admin_billing')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
