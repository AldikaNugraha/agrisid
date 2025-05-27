<?php

namespace App\Filament\Resources\BuyerResource\RelationManagers;

use App\Models\Farmer;
use App\Models\Harvest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HarvestsRelationManager extends RelationManager
{
    protected static string $relationship = 'harvests';
    protected static ?string $recordTitleAttribute = 'name';

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
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label("Nama Pemanenan")
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah_beli')
                    ->label("Jumlah Beli")
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->label("Harga Beli")
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('wilayah')
                    ->label("Daerah Pemasaran")
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label("Tambah Pembelian")
                    ->form(fn(Tables\Actions\AttachAction $action): array =>[
                        Forms\Components\Select::make('farmer_name')
                            ->label("Nama Petani")
                            ->required()
                            ->searchable()
                            ->options(Farmer::all()->pluck('name','id'))
                            ->live() // Ensures dependent fields update when this changes
                            ->afterStateUpdated(function (Set $set) use ($action) { // <<< CORRECTED: Pass $action using 'use'
                                // When farmer changes, clear the selected harvest and its quantity.
                                // The record select will re-fetch its options.
                                // Clearing the state of the recordSelect and the custom dependent field.
                                $recordSelectFieldName = $action->getRecordSelect()->getName();
                                $set($recordSelectFieldName, null); // Clear selected harvest ID
                                $set('selected_harvest_qty', null); // Clear displayed harvest quantity
                            })
                            ->preload(),
                        $action->getRecordSelect() // This is the select for choosing the Harvest record to attach
                            ->label("Nama Panen")
                            ->live() // So that afterStateUpdated for this field works
                            ->options(function (Get $get): array {
                                $farmerId = $get('farmer_name'); // This correctly gets the selected farmer's ID

                                if (!$farmerId) {
                                    return []; // No farmer selected, so no harvests to show
                                }
                                $harvests = Harvest::forFarmer($farmerId) // Use the custom scope
                                    ->pluck('name', 'id')
                                    ->all();
                                return $harvests;
                            })
                            ->afterStateUpdated(function ($state, Set $set) { // $state here is the selected harvest_id
                                if (blank($state)) {
                                    $set('selected_harvest_qty', null);
                                    return;
                                }
                                $harvest = Harvest::find($state);
                                if ($harvest) {
                                    $set('selected_harvest_qty', $harvest->qty); // Assuming 'qty' is the attribute on Harvest model
                                } else {
                                    $set('selected_harvest_qty', 'N/A (Quantity info unavailable)');
                                }
                            }),
                        Forms\Components\TextInput::make('selected_harvest_qty')
                            ->label("Jumlah Panen")
                            ->disabled() // User cannot edit this field.
                            ->placeholder('Select a harvest to view its quantity'),
                        Forms\Components\TextInput::make('jumlah_beli')
                            ->label("Jumlah Beli (Kg)") // Clarified label
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->live(onBlur: true) // Or ->reactive() if you prefer immediate feedback
                            ->rules([ // Custom rule to check against available quantity
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $selectedHarvestQty = $get('selected_harvest_qty');
                                    // Ensure $selectedHarvestQty is numeric before comparison
                                    if (is_numeric($selectedHarvestQty) && $value > $selectedHarvestQty) {
                                        $fail("Jumlah beli ({$value} Kg) tidak boleh melebihi jumlah panen tersedia ({$selectedHarvestQty} Kg).");
                                    } elseif ($selectedHarvestQty === 'N/A (Quantity info unavailable)' || $selectedHarvestQty === null) {
                                        $fail("Tidak dapat memvalidasi jumlah beli karena jumlah panen tidak tersedia.");
                                    }
                                },
                            ]),
                        Forms\Components\TextInput::make('harga_beli')
                            ->label("Harga Beli")
                            ->prefix('Rp')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('wilayah')
                            ->label("Daerah Pemasaran")
                            ->required(),
                    ])->preloadRecordSelect()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\DetachAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
