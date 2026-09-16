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
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                     // contoh: 1 Bulan, 3 Bulan
            $table->unsignedInteger('duration');        // angka: 30, 90, 180, 365
            $table->string('duration_unit')->default('days'); // days / months
            $table->unsignedInteger('price');           // dalam rupiah (tanpa koma)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
