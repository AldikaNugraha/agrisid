<?php

namespace App\Filament\Resources\VillageResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\VillageResource\Pages\listVillages;

class VillageOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return listVillages::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Desa', $this->getPageTableRecords()
                ->count()),
            Stat::make('Total Poktan', $this->getPageTableRecords()
                ->load('poktans')
                ->pluck('poktans')
                ->flatten()
                ->count()),
            Stat::make('Total Lahan', $this->getPageTableRecords()
                ->load('fields')
                ->pluck('fields')
                ->flatten()
                ->count()),
            Stat::make('Total Petani', $this->getPageTableQuery()
                ->withCount('farmers')
                ->get()
                ->sum('farmers_count')),
        ];
    }
}
