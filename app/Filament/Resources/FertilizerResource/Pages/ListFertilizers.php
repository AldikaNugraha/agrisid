<?php

namespace App\Filament\Resources\FertilizerResource\Pages;

use App\Filament\Resources\FertilizerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFertilizers extends ListRecords
{
    protected static string $resource = FertilizerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
