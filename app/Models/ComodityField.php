<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Builder;
class ComodityField extends Pivot
{
    protected $table = 'comodity_field';
    public $incrementing = true;

    protected $fillable = [
        'field_id',
        'comodity_id',
        "qty",
        'tanggal_tanam',
    ];

    public function field()
    {
        return $this->belongsTo(Field::class, 'field_id');
    }

    public function comodity()
    {
        return $this->belongsTo(Comodity::class, 'comodity_id');
    }

    // Query Scope examples
    public function scopeForFieldAndComodity(Builder $query, int $fieldId, int $comodityId): Builder
    {
        return $query->where('field_id', $fieldId)->where('comodity_id', $comodityId);
    }

    public function scopeWithDate(Builder $query, string $tanggalTanam): Builder
    {
        return $query->where('tanggal_tanam', $tanggalTanam);
    }
}
