<?php

namespace App\Filament\Resources\FertilizerResource\Pages;

use App\Filament\Resources\FertilizerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFertilizer extends EditRecord
{
    protected static string $resource = FertilizerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
