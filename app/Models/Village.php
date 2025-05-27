<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Village extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'province',
        'city',
        'district',
        'description',
    ];

    public function poktans(): HasMany
    {
        return $this->hasMany(Poktan::class, 'village_id');
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'village_id');
    }

    public function fields() : HasMany
    {
        return $this->hasMany(Field::class, 'village_id');
    }

    // in App\Models\Group.php

    public function farmers(): HasManyThrough
    {
        // Group → Poktan → Farmer
        return $this->hasManyThrough(
            \App\Models\Farmer::class,    // final model
            \App\Models\Poktan::class,    // intermediate
            'village_id',                   // foreign key on poktans table...
            'poktan_id',                  // foreign key on farmers table...
            'id',                         // local key on groups table...
            'id'                          // local key on poktans table...
        );
    }

}
