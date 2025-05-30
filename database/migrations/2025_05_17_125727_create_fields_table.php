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
        Schema::create('fields', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId("village_id")->constrained("villages")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("farmer_id")->constrained("farmers")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->float('luas');
            $table->magellanMultiPolygonZ("batas")->nullable();
            $table->index(['name']);
            $table->spatialIndex(["batas"]);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
