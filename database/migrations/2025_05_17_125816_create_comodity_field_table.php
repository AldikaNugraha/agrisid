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
        Schema::create('comodity_field', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId('field_id')->constrained("fields")->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('comodity_id')->constrained("comodities")->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('qty');
            $table->dateTimeTz('tanggal_tanam');
            $table->timestampsTz();
            $table->primary(['field_id', 'comodity_id', "tanggal_tanam"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantings');
    }
};
