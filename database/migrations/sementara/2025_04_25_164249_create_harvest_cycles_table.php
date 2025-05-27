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
        Schema::create('harvest_cycles', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId("plantation_block_id")->constrained("plantation_blocks")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string("name");
            $table->boolean("status")->default(false);
            $table->date("start_date");
            $table->date("end_date");
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_cycles');
    }
};
