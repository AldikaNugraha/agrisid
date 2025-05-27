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
        Schema::create('cleanings', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId('cleaning_cycle_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('cleaning_type_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->float('qty');
            $table->timestampsTz();
            $table->primary(['cleaning_cycle_id', 'cleaning_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanings');
    }
};
