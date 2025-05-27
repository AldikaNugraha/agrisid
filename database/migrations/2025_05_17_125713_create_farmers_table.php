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
        Schema::create('farmers', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId('poktan_id')->constrained("poktans")->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->string('age');
            $table->string('address');
            $table->string('phone');
            $table->string('email')->unique()->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};
