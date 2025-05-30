<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    use HasFactory;

    protected $casts = [
        'batas' => MultiPolygon::class,
    ];

    protected $fillable = [
        'village_id',
        'farmer_id',
        'name',
        'luas',
        'batas'
    ];

    public function farmers() : BelongsTo
    {
        return $this->belongsTo(Farmer::class, "farmer_id");
    }

    public function villages() : BelongsTo
    {
        return $this->belongsTo(Village::class, "village_id");
    }

    public function comodities(): BelongsToMany
    {
        return $this->belongsToMany(Comodity::class, 'comodity_field', 'field_id', 'comodity_id')
            ->using(ComodityField::class)
            ->withPivot("id", "qty", "tanggal_tanam")
            ->withTimestamps();
    }

    public function fertilizers(): BelongsToMany
    {
        return $this->belongsToMany(Fertilizer::class, 'field_fertilizer','field_id', 'fertilizer_id')
            ->withPivot("id", "qty", "start_fertilize")
            ->withTimestamps();
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'field_warehouse', 'field_id', 'warehouse_id')
            ->using(FieldWarehouse::class)
            ->withPivot("id", "comodity_name","qty", "tanggal_panen")
            ->withTimestamps();
    }
}
