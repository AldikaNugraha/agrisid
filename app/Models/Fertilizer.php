<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Fertilizer extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'jenis',
        'expired_date',
        'stok',
    ];

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'field_fertilizer','field_id', 'fertilizer_id')
            ->withPivot("id", "qty", "start_fertilize")
            ->withTimestamps();
    }
}
