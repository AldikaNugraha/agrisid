<?php

namespace App\Filament\Resources\FieldResource\Pages;

use App\Filament\Resources\FieldResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Support\Facades\Storage;

class FieldMap extends Page
{
    protected static string $resource = FieldResource::class;
    protected static string $view = 'filament.resources.field-resource.pages.field-map';
    use InteractsWithRecord;

    public int $field_id;
    public string $cog_name = "drone_20242005_visual_cog";

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->field_id = $this->record->id;
    }
}
