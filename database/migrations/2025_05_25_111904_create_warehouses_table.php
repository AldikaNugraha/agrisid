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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained("villages")->cascadeOnDelete()->cascadeOnUpdate(); // diisi dengan nama desa dari petani pemilik lahan yang sedang dipanen
            $table->string('name');
            $table->integer('capacity');
            $table->integer('current_stock'); // total dari qty pada warehouse_id yang sama di pivot field_warehouse
            $table->string('pic')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
