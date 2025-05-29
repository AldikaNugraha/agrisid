<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VillageResource\Pages;
use App\Filament\Resources\VillageResource\RelationManagers;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use \KodePandai\Indonesia\Models\Province;
use \KodePandai\Indonesia\Models\City;
use \KodePandai\Indonesia\Models\District;
// use

class VillageResource extends Resource
{
    protected static ?string $model = Village::class;
    protected static ?string $navigationLabel = 'Desa';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('province')
                    ->label('Provinsi')
                    ->options(Province::all()->pluck('name', 'code'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('city', null);
                    }),
                Forms\Components\Select::make('city')
                    ->label('Kabupaten/Kota')
                    ->searchable()
                    ->options(function (Get $get): array {
                        $provinceId = $get('province');

                        if (!$provinceId) {
                            return []; // Not enough info to fetch planting dates
                        }
                        return City::where('province_code', $provinceId)
                            ->pluck('name', 'code')
                            ->toArray();
                        })
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('district', null);
                    }),
                Forms\Components\Select::make('district')
                    ->label('Nama Kecamatan')
                    ->live()
                    ->options(function (Get $get): array {
                        $regencyId = $get('city');

                        if (!$regencyId) {
                            return [];
                        }
                        return District::where('city_code', $regencyId)
                            ->pluck('name', 'code')
                            ->toArray();
                        })
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('name')
                    ->label('Nama Desa')
                    ->live()
                    ->options(function (Get $get): array {
                        $districtId = $get('district');

                        if (!$districtId) {
                            return []; // Not enough info to fetch planting dates
                        }
                        return \KodePandai\Indonesia\Models\Village::where('district_code', $districtId)
                            ->pluck('name', 'code')
                            ->toArray();
                        })
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('province')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label("Map"),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVillages::route('/'),
            'create' => Pages\CreateVillage::route('/create'),
            'edit' => Pages\EditVillage::route('/{record}/edit'),
            'view' => Pages\VillageMap::route('/{record}/map'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            // VillageResource\Widgets\VillageOverview::class,
        ];
    }
}
