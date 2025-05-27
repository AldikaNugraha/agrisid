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
        Schema::create('harvests', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId("field_id")->constrained("fields")->cascadeOnDelete()->cascadeOnUpdate();
            $table->string("name");
            $table->dateTimeTz("start_panen");
            $table->integer("qty");
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
