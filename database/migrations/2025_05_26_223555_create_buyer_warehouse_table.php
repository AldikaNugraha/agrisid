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
        Schema::create('buyer_warehouse', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId("buyer_id")->constrained("buyers")->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId("warehouse_id")->constrained("warehouses")->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer("jumlah_beli");
            $table->float("harga_beli");
            $table->string("wilayah");
            $table->timestampsTz();
            $table->primary(['warehouse_id', 'buyer_id', "wilayah"]);
            $table->index(['wilayah']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_warehouse');
    }
};
