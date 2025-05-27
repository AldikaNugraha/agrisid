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
        Schema::create('poktans', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId('village_id')->constrained('villages')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->string('ketua');
            $table->integer('jumlah_anggota');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poktans');
    }
};
