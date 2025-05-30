<?php

namespace App\Filament\Resources\FieldResource\RelationManagers;

use App\Filament\Resources\HarvestResource;
use App\Models\Comodity;
use App\Models\ComodityField;
use App\Models\Warehouse;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class WarehousesRelationManager extends RelationManager
{
    protected static string $relationship = 'warehouses';

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
                Group::make('tanggal_panen')
                    ->label('Tanggal Panen') // Optional: set a label for the group header
                    ->date() // Important: Tell Filament this is a date for grouping
                    // You can also specify the format if needed, e.g., ->date('Y-m-d')
            ])
            ->defaultGroup('tanggal_panen')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comodity_name')
                    ->label('Komoditas Panen'),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Jumlah Panen (kg)')
                    // ->summarize(Sum::make()->label("Total Dosis (Kg)"))
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_panen')
                    ->label('Tanggal Panen')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label("Tambah Panen")
                    ->form(fn (Tables\Actions\AttachAction $action): array =>[
                        $action->getRecordSelect()
                            ->label('Pilih Gudang')
                            ->preload() // Customize label if needed
                            ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                // This function runs when the user selects a Comodity.
                                // $state contains the ID of the selected Comodity.
                                if (blank($state)) {
                                    // If no comodity is selected, clear the stock display
                                    $set('warehouse_capacity', null);
                                    return;
                                }
                                $warehouse = Warehouse::find($state);
                                if ($warehouse) {
                                    // Set the value of the 'warehouse_capacity' field.
                                    $set('warehouse_capacity', $warehouse->capacity);
                                } else {
                                    // Handle cases where warehouse isn't found or stok isn't available.
                                    $set('warehouse_capacity', 'N/A (capacity info unavailable)');
                                }
                            }), // Crucial: Makes this field reactive.
                        Forms\Components\TextInput::make('warehouse_capacity')
                            ->label("Kapasitas Gudang (Kg)") // Label as per your snippet
                            ->readOnly() // Make it read-only
                            ->numeric(),  // If the quantity is numeric
                        Forms\Components\Select::make('comodity_name')
                            ->label("Komoditas Lahan")
                            ->required()
                            ->searchable()
                            ->options(function (Get $get, RelationManager $livewire): array {
                                return $livewire->getOwnerRecord()->comodities()
                                    ->pluck('comodities.name', 'comodities.id')   // Use qualified names in pluck
                                    ->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                // Clear the planting date when commodity changes
                                $set('field_comodity_plantingDate', null);
                            })
                            ->preload(),
                        Forms\Components\Select::make('field_comodity_plantingDate')
                            ->label("Tanggal Tanam")
                            ->options(function (Get $get, RelationManager $livewire): array {
                                $fieldId = $livewire->getOwnerRecord()->id;
                                $comodityId = $get('comodity_name'); // This is the ID of the selected Comodity

                                if (!$fieldId || !$comodityId) return [];

                                $plantingDates = ComodityField::forFieldAndComodity($fieldId, $comodityId)
                                    ->orderBy('tanggal_tanam')
                                    ->select('tanggal_tanam')
                                    ->distinct()
                                    ->get();

                                // Format for options: [value => label]
                                // Here, both value and label will be the tanggal_tanam date string.
                                // You might want to format the label differently.
                                return $plantingDates->mapWithKeys(function ($item) {
                                    $dateValue = $item->tanggal_tanam; // e.g., '2023-10-15'
                                    // Format the label for better readability
                                    $dateLabel = Carbon::parse($dateValue)->translatedFormat('d F Y'); // Example: "15 Oktober 2023"
                                    return [$dateValue => $dateLabel];
                                })->all();
                            })
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state, RelationManager $livewire) { // $state is the selected tanggal_tanam
                                $set('field_comodity_plantingQty', null); // Clear previous quantity first

                                if ($state) { // If a date is selected
                                    $fieldId = $livewire->getOwnerRecord()->id;
                                    $comodityId = $get('comodity_name');

                                    if ($fieldId && $comodityId) {
                                        $pivotEntry = ComodityField::forFieldAndComodity($fieldId, $comodityId)
                                            ->withDate($state) // $state is tanggal_tanam
                                            ->select('qty')
                                            ->first();

                                        if ($pivotEntry && isset($pivotEntry->qty)) {
                                            $set('field_comodity_plantingQty', $pivotEntry->qty);
                                        }
                                    }
                                }
                            })
                            ->preload(),
                        Forms\Components\TextInput::make('field_comodity_plantingQty')
                            ->label("Jumlah Tanam (Kg)") // Label as per your snippet
                            ->readOnly() // Make it read-only
                            ->numeric(), // If this value must be present for the form submission
                            // No ->preload() or ->searchable() needed for a read-only TextInput driven by other fields.,
                        Forms\Components\DateTimePicker::make('tanggal_panen')
                            ->required(),
                        Forms\Components\TextInput::make('qty')
                            ->label('Jumlah Panen (kg)')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->preloadRecordSelect()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (isset($data['comodity_name'])) {
                            $comodity = Comodity::find($data['comodity_name']);
                            if ($comodity) {
                                $data['comodity_name'] = $comodity->name;
                            }
                        }
                        return $data;
                    }),
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
