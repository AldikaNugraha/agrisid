<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comodity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'varietas',
        'stok',
        'qty',
    ];

    public function fields() : BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'comodity_field', 'field_id', 'comodity_id')
            ->using(ComodityField::class)
            ->withPivot("id", "qty", "tanggal_tanam")
            ->withTimestamps();
    }
}
