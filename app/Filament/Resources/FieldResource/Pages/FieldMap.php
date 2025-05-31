<?php

namespace App\Filament\Resources\FieldResource\Pages;

use App\Filament\Resources\FieldResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class FieldMap extends Page
{
    protected static string $resource = FieldResource::class;
    protected static string $view = 'filament.resources.field-resource.pages.field-map';
    use InteractsWithRecord;

    public int $field_id;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->field_id = $this->record->id;
    }
}
