<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoktanResource\Pages;
use App\Filament\Resources\PoktanResource\RelationManagers;
use App\Models\Poktan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PoktanResource extends Resource
{
    protected static ?string $model = Poktan::class;
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Poktan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('village_id')
                    ->label("Nama Desa")
                    ->relationship("villages", "name")
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ketua')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jumlah_anggota')
                    ->default("0")
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('villages.name')
                    ->label("Desa")
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label("Nama Poktan")
                    ->searchable(),
                Tables\Columns\TextColumn::make('ketua')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah_anggota')
                    ->numeric()
                    ->sortable(),
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
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPoktans::route('/'),
            'create' => Pages\CreatePoktan::route('/create'),
            'edit' => Pages\EditPoktan::route('/{record}/edit'),
        ];
    }
}
