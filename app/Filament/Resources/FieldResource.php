<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FieldResource\Pages;
use App\Filament\Resources\FieldResource\RelationManagers;
use App\Models\Field;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FieldResource extends Resource
{
    protected static ?string $model = Field::class;
    protected static ?int $navigationSort = 7;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Lahan';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('village_id')
                    ->preload()
                    ->searchable()
                    ->relationship("villages","name")
                    ->label("Nama Desa")
                    ->required(),
                Forms\Components\Select::make('farmer_id')
                    ->preload()
                    ->searchable()
                    ->relationship("farmers","name")
                    ->label("Nama Petani")
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label("Nama Lahan")
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('luas')
                    ->label("Luas Lahan")
                    ->required()
                    ->numeric(),
                Forms\Components\FileUpload::make('batas_file')
                    ->label("Masukan Batas Lahan")
                    ->disk('public')
                    ->previewable(false)
                    ->preserveFilenames()
                    ->helperText('Format batas lahan harus berupa file GeoJSON.')
                    ->required()
                    ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('villages.name')
                    ->label('Desa')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('farmers.name')
                    ->searchable()
                    ->label('Nama Petani')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lahan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('luas')
                    ->label("Luas Lahan (Ha)")
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
            RelationManagers\ComoditiesRelationManager::class,
            RelationManagers\FertilizersRelationManager::class,
            RelationManagers\WarehousesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFields::route('/'),
            'create' => Pages\CreateField::route('/create'),
            'edit' => Pages\EditField::route('/{record}/edit'),
        ];
    }
}
