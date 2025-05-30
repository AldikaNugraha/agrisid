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
        Schema::create('fertilizers', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string("name")->unique();
            $table->string("jenis");
            $table->date("expired_date")->nullable();
            $table->integer("stok")->default(0);
            $table->index(['name', "stok"]);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fertilizers');
    }
};
