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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code')->unique(); // EP-1558 / EM-1559
            $table->enum('type', ['pelajar', 'non_pelajar'])->default('non_pelajar');
            $table->string('name');
            $table->string('whatsapp', 20)->nullable();
            $table->string('photo')->nullable();
            $table->enum('gender', ['laki-laki', 'perempuan'])->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
