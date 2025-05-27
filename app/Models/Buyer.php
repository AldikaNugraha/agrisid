<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buyer extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "addres",
        "phone",
        "email",
        "is_validate",
    ];

    public function warehouses() : BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'buyer_warehouse', 'buyer_id', 'warehouse_id')
            ->withPivot("id", "jumlah_beli", "harga_beli", "wilayah")
            ->withTimestamps();
    }
}
