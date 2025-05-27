<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuyerResource\Pages;
use App\Filament\Resources\BuyerResource\RelationManagers;
use App\Models\Buyer;
use App\Models\Harvest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BuyerResource extends Resource
{
    protected static ?string $model = Buyer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Pengepul';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('addres')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_validate')
                    ->required(),

                // Forms\Components\Repeater::make("sales")
                //     ->columnSpanFull()
                //     ->relationship()
                //     ->schema([
                //         Forms\Components\Select::make('harvest_id')
                //             ->relationship('harvests', 'name')
                //             ->label("Nama Panen")
                //             ->preload()
                //             ->afterStateUpdated(function (Set $set, ?string $state, ?string $old) {
                //                 if ($state){
                //                     $harvest =  Harvest::find($state);
                //                     $set('jumlah_panen', $harvest->qty);
                //                 }else {
                //                     $set('jumlah_panen', "");
                //                 }
                //             })
                //             ->reactive()
                //             ->searchable()
                //             ->required(),
                //         Forms\Components\TextInput::make('jumlah_panen')
                //             ->readOnly()
                //             ->label("Jumlah Panen"),
                //         Forms\Components\TextInput::make('jumlah_beli')
                //             ->label("Jumlah Beli (kg)")
                //             ->numeric()
                //             ->required(),
                //         Forms\Components\TextInput::make('harga_beli')
                //             ->label("Harga Beli (Rp/kg)")
                //             ->numeric()
                //             ->required(),
                //         Forms\Components\TextInput::make('wilayah')
                //             ->label("Daerah Pemasaran")
                //             ->required(),
                //         ])
                //     ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                //             unset($data['jumlah_panen']);
                //             return $data;
                //         }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pengepul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('addres')
                    ->label("Alamat")
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label("Kontak")
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label("Email")
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_validate')
                    ->label("Terverifikasi")
                    ->boolean(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuyers::route('/'),
            'create' => Pages\CreateBuyer::route('/create'),
            'edit' => Pages\EditBuyer::route('/{record}/edit'),
        ];
    }
}
