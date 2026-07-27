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
        Schema::table('credit_installments', function (Blueprint $table) {
            $table->foreign('payment_cash_session_id')->references('id')->on('cash_sessions')->onDelete('set null');
            $table->foreign('payment_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_installments', function (Blueprint $table) {
            $table->dropForeign(['payment_cash_session_id']);
            $table->dropForeign(['payment_user_id']);
            $table->dropForeign(['payment_method_id']);
        });
    }
};
