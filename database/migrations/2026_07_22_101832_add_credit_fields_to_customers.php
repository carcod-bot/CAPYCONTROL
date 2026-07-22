<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('credit_level_id')->nullable()->constrained('credit_levels')->nullOnDelete();
            $table->integer('total_purchases')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['credit_level_id']);
            $table->dropColumn(['credit_level_id', 'total_purchases']);
        });
    }
};
