<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FieldWarehouse extends Pivot
{
    protected $table = 'field_warehouse';

    // Indicates if the IDs are auto-incrementing.
    // Set to true if your pivot "id" column is auto-incrementing.
    public $incrementing = true;

    protected $fillable = [
        'field_id',
        'warehouse_id',
        "comodity_name",
        'qty',
        'tanggal_panen',
    ];

    protected $casts = [
        'comodity_name' => 'string',
        'qty' => 'integer',
        'tanggal_panen' => 'date',
    ];

    // No need for timestamps if they are handled by withTimestamps() on the relationship,
    // unless you want to manage them directly on this model.
    // public $timestamps = true; // if your pivot table has created_at and updated_at from withTimestamps()

    /**
     * Get the warehouse associated with this pivot record.
     * Useful for the observer.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // You can also define a relationship to Field if needed
    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id');
    }
}
