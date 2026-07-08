<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cajas registradoras físicas
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->string('number', 10)->unique(); // Nº Caja (003, 004, etc.)
            $table->string('name')->nullable(); // Nombre descriptivo
            $table->string('location')->nullable(); // Ubicación física
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Sesiones/turnos de caja
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Cajero
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->integer('turn_number')->default(1); // Nº Turno
            $table->decimal('opening_amount', 12, 2)->default(0); // Fondo inicial
            $table->decimal('expected_amount', 12, 2)->default(0); // Monto esperado al cierre
            $table->decimal('actual_amount', 12, 2)->nullable(); // Monto real al cierre
            $table->decimal('difference', 12, 2)->nullable(); // Diferencia (sobrante/faltante)
            $table->integer('total_sales')->default(0); // Nº Ventas
            $table->integer('total_returns')->default(0); // Nº Devoluciones
            $table->integer('total_withdrawals')->default(0); // Nº Retiros
            $table->integer('pending_invoices')->default(0); // Facturas pendientes
            $table->timestamp('opened_at')->useCurrent(); // Fecha/Hora apertura
            $table->timestamp('closed_at')->nullable(); // Fecha/Hora cierre
            $table->text('closing_notes')->nullable(); // Notas del cierre
            $table->timestamps();

            $table->index(['cash_register_id', 'status']);
        });

        // Movimientos de caja (retiros parciales, depósitos, etc.)
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_session_id')->constrained('cash_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users'); // Quién autorizó
            $table->enum('type', ['withdrawal', 'deposit', 'adjustment']); // Tipo
            $table->decimal('amount', 12, 2);
            $table->string('reason')->nullable(); // Motivo
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('cash_sessions');
        Schema::dropIfExists('cash_registers');
    }
};
