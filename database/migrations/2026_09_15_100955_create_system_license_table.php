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
        Schema::create('system_license', function (Blueprint $table) {
            $table->id();
            $table->string('system_id');
            $table->string('license_type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('license_key');
            $table->string('signature');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_license');
    }
};
