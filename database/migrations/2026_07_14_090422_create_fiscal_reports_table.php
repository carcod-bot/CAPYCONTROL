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
        Schema::create('fiscal_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_event_id')->constrained('pos_events')->onDelete('cascade');
            $table->string('report_type'); // 'Z' or 'X'
            $table->string('report_number')->nullable(); // e.g. "0123"
            $table->text('raw_data')->nullable(); // the full ASCII string from printer
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_reports');
    }
};
