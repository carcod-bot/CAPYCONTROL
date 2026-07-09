<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->string('hostname')->nullable()->after('location'); // Nombre del PC (hostname)
            $table->string('ip_address')->nullable()->after('hostname'); // IP del PC en la red local
        });
    }

    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn(['hostname', 'ip_address']);
        });
    }
};
