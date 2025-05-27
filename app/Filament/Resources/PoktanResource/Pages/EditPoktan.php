<?php

namespace App\Filament\Resources\PoktanResource\Pages;

use App\Filament\Resources\PoktanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPoktan extends EditRecord
{
    protected static string $resource = PoktanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
