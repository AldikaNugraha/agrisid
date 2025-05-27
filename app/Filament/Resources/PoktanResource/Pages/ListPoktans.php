<?php

namespace App\Filament\Resources\PoktanResource\Pages;

use App\Filament\Resources\PoktanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPoktans extends ListRecords
{
    protected static string $resource = PoktanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
