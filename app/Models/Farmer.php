<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'poktan_id',
        'name',
        'age',
        'address',
        'phone',
        'email',
    ];

    public function poktans() : BelongsTo
    {
        return $this->belongsTo(Poktan::class, 'poktan_id');
    }

    protected static function booted()
    {
        parent::booted();

        static::created(function ($farmer) {
            // Increment jumlah_anggota by 1 for the related Poktan
            $farmer->poktans->increment('jumlah_anggota');
        });
    }

    public function fields() : HasMany
    {
        return $this->hasMany(Field::class, "farmer_id");
    }
}
