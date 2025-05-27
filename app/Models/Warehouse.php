<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'village_id',
        'capacity',
        'current_stock', // total dari qty pada warehouse_id yang sama di pivot field_warehouse
        "pic"
    ];

    public function villages():BelongsTo
    {
        return $this->belongsTo(Village::class, "village_id");
    }

    public function fields() : BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'field_warehouse', 'field_id', 'warehouse_id')
            ->withPivot("id", "comodity_name","qty", "tanggal_panen")
            ->withTimestamps();
    }

    /**
     * Recalculate and update the current stock for this warehouse.
     * This method can be called by the observer.
     */
    public function updateCurrentStock(): void
    {
        // Sum 'qty' directly from the pivot table for this warehouse
        $totalQty = DB::table('field_warehouse')
                        ->where('warehouse_id', $this->id)
                        ->sum('qty');

        $this->current_stock = $totalQty ?? 0; // Set to 0 if sum is null (e.g., no entries)
        $this->saveQuietly(); // Save without firing events to prevent potential loops
    }

    public function buyers() : BelongsToMany
    {
        return $this->belongsToMany(Buyer::class, 'buyer_warehouse', 'buyer_id', 'warehouse_id')
            ->withPivot("id", "jumlah_beli", "harga_beli", "wilayah")
            ->withTimestamps();
    }
}
