<?php

namespace App\Filament\Resources\ComodityResource\Pages;

use App\Filament\Resources\ComodityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComodity extends EditRecord
{
    protected static string $resource = ComodityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
