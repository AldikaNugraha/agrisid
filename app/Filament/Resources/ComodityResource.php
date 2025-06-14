<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComodityResource\Pages;
use App\Filament\Resources\ComodityResource\RelationManagers;
use App\Models\Comodity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComodityResource extends Resource
{
    protected static ?string $model = Comodity::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 14;
    protected static ?string $navigationLabel = 'Komoditas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('varietas')
                    ->maxLength(255),
                Forms\Components\TextInput::make('stok')
                    ->label("Stok (kg)")
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('varietas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stok')
                    ->label("Stok (kg)")
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
            'index' => Pages\ListComodities::route('/'),
            'create' => Pages\CreateComodity::route('/create'),
            'edit' => Pages\EditComodity::route('/{record}/edit'),
        ];
    }
}
