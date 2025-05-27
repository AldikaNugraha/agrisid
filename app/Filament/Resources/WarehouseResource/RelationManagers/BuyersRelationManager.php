<?php

namespace App\Filament\Resources\WarehouseResource\RelationManagers;

use App\Models\Buyer;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BuyersRelationManager extends RelationManager
{
    protected static string $relationship = 'buyers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->allowDuplicates()
            ->groups([
                Group::make('wilayah')
                    ->label('Daerah Pemasaran')
            ])
            ->defaultGroup('wilayah')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('jumlah_beli')
                    ->label('Jumlah Beli (kg)')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->label('Harga Beli (Rp)')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('wilayah')
                    ->label('Daerah Pemasaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label("Tambah Pembeli")
                    ->form([
                        Forms\Components\Select::make("recordId")
                            ->options(Buyer::where('is_validate', true)->pluck('name', 'id'))
                            ->label("Pilih Pembeli")
                            ->preload(),
                        Forms\Components\TextInput::make('jumlah_beli')
                            ->label('Jumlah Beli (kg)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('harga_beli')
                            ->label('Harga Beli (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('wilayah')
                            ->label('Daerah Pemasaran')
                            ->required()
                            ->maxLength(255),
                    ])->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
