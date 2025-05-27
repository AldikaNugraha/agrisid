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
        Schema::create('plantation_trees', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId("plantation_block_id")->constrained("plantation_blocks")->cascadeOnUpdate()->cascadeOnDelete();
            $table->double("latt");
            $table->double("long");
            // $table->geography("geom")->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantation_trees');
    }
};
