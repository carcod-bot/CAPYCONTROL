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
        Schema::table('credit_levels', function (Blueprint $table) {
            $table->decimal('limit_increase_percentage', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_levels', function (Blueprint $table) {
            $table->dropColumn('limit_increase_percentage');
        });
    }
};
