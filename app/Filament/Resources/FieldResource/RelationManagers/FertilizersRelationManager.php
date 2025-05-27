<?php

namespace App\Filament\Resources\FieldResource\RelationManagers;

use App\Models\Fertilizer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Sum;

class FertilizersRelationManager extends RelationManager
{
    protected static string $relationship = 'fertilizers';

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
                Group::make('start_fertilize')
                    ->label('Tanggal Pemupukan') // Optional: set a label for the group header
                    ->date() // Important: Tell Filament this is a date for grouping
                    // You can also specify the format if needed, e.g., ->date('Y-m-d')
            ])
            ->defaultGroup('start_fertilize')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Dosis Pemupukan (kg)')
                    // ->summarize(Sum::make()->label("Total Dosis (Kg)"))
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_fertilize')
                    ->label('Tanggal Pemupukan')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label("Tambah Pemupukan")
                    ->form([
                        Forms\Components\Select::make("recordId")
                            ->label('Pilih Pupuk')
                            ->options(Fertilizer::all()->pluck('name', 'id'))
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (blank($state)) {
                                    $set('selected_fertilizer_stok', null);
                                    return;
                                }
                                $fertilizer = Fertilizer::find($state);
                                if ($fertilizer) {
                                    $set('selected_fertilizer_stok', $fertilizer->stok);
                                } else {
                                    $set('selected_fertilizer_stok', 'N/A (Stok info unavailable)');
                                }
                            })
                            ->preload(),
                        Forms\Components\TextInput::make('selected_fertilizer_stok')
                            ->label('Current Available Stok')
                            ->disabled() // User cannot edit this field.
                            ->placeholder('Select a fertilizer to view its stok'),
                            // ->dehydrated(false) // Ensures this field's value isn't persisted if its name accidentally matches a column.
                        Forms\Components\TextInput::make('qty')
                            ->reactive()
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $selectedStok = $get('selected_fertilizer_stok');
                                            if (is_numeric($selectedStok) && $value > $selectedStok) {
                                                $fail("The quantity to attach ({$value}) cannot exceed the available stok of {$selectedStok}.");
                                            }
                                        },
                            ])
                            ->label('Quantity (qty)')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\DatePicker::make('start_fertilize')
                            ->label('Tanggal Pemupukan')
                            ->required(),
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
