<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Poktan extends Model
{
    use HasFactory;
    protected $fillable = [
        'village_id',
        'name',
        'ketua',
        'jumlah_anggota',
    ];

    public function villages(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function farmers(): HasMany
    {
        return $this->hasMany(Farmer::class, 'poktan_id');
    }
}
