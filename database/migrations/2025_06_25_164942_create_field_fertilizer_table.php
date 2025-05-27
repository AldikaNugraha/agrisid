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
        Schema::create('field_fertilizer', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId('field_id')->constrained("fields")->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('fertilizer_id')->constrained("fertilizers")->cascadeOnDelete()->cascadeOnUpdate();
            $table->float('qty');
            $table->date("start_fertilize");
            $table->timestampsTz();
            $table->primary(['field_id', 'fertilizer_id', "start_fertilize"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_fertilizer');
    }
};
