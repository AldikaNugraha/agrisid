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
        Schema::create('field_warehouse', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->foreignId("field_id")->constrained("fields")->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId("warehouse_id")->constrained("warehouses")->cascadeOnDelete()->cascadeOnUpdate();
            $table->string("comodity_name");
            $table->integer("qty")->default(0);
            $table->date("tanggal_panen");
            $table->timestampsTz();
            $table->primary(['warehouse_id', 'field_id', "tanggal_panen"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_warehouse');
    }
};
