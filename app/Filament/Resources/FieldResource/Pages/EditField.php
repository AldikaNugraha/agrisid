<?php

namespace App\Filament\Resources\FieldResource\Pages;

use App\Filament\Resources\FieldResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;

class EditField extends EditRecord
{
    protected static string $resource = FieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['batas']) && $data['batas'] instanceof MultiPolygon) {
            // Option: Remove 'batas' from the form data if it's causing issues
            // and not directly editable as a raw object.
            // Since 'batas_file' FileUpload is hidden on edit,
            // there's no form field directly trying to display this raw object.
            unset($data['batas']);

            // Alternatively, if you had a custom field to display it (e.g., as a GeoJSON string):
            // $data['batas_display_string'] = $data['batas']->toGeoJSON(); // Assuming toGeoJSON() exists
            // unset($data['batas']);
        }
        return $data;
    }
}
