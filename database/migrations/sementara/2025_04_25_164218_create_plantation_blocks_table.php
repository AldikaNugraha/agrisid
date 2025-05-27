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
        Schema::create('plantation_blocks', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId("plantation_id")->constrained("plantations")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string("name");
            $table->float("area");
            $table->integer("total_trees");
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantation_blocks');
    }
};
