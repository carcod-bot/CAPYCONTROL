<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_session_id')->constrained('cash_sessions');
            $table->foreignId('user_id')->constrained('users'); // Cajero que hizo la venta
            
            // Payment info
            $table->string('payment_method')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('tendered_amount', 12, 2)->nullable(); // Cuanto pagó el cliente
            $table->decimal('change_amount', 12, 2)->nullable(); // Vuelto
            
            // Status and metadata
            $table->enum('status', ['completed', 'voided', 'refunded'])->default('completed');
            $table->string('ticket_number')->unique();
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            
            // Snapshot of prices at the time of sale
            $table->string('product_name');
            $table->string('product_code');
            $table->decimal('quantity', 12, 3); // Puede ser decimal para productos a granel
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
