<?php

namespace App\Filament\Resources\FieldResource\RelationManagers;

use App\Models\Comodity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComoditiesRelationManager extends RelationManager
{
 // 1. Define the relationship name exactly as it is defined in your Field model.
    //    e.g., if Field model has `public function comodities()`, then this is 'comodities'.
    protected static string $relationship = 'comodities';

    // Optional: Customize the title for records in this relation manager (e.g., in notifications).
    // Assumes your Comodity model has a 'name' attribute.
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Defines the form schema for creating and editing records within this relation manager.
     * For a many-to-many relationship, this form is primarily used for:
     * - Editing PIVOT data when you click 'Edit' on a table row.
     * - Potentially, creating a NEW `Comodity` record AND attaching it if you use `Tables\Actions\CreateAction::make()`.
     * - The fields for pivot data (`qty`, `tanggal_tanam`) are also used by the `AttachAction`'s form.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Fields for PIVOT data (qty, tanggal_tanam).
                // These are displayed when editing an existing association's pivot data
                // or when attaching records via AttachAction if it's configured to collect pivot data.

                // Example: If you wanted to select a Comodity (less comon directly in this main form for pivot editing,
                // as the Comodity is already established for the row being edited).
                // Forms\Components\Select::make('comodity_id') // Assuming this is the foreign key on the pivot table
                //     ->label('comodity')
                //     ->relationship(name: 'comodity', titleAttribute: 'name') // Relationship from PIVOT to Comodity if defined
                //     ->required()
                //     ->disabledOn('edit'), // Or handle differently if needed

                Forms\Components\TextInput::make('qty')
                    ->label('Quantity (qty)')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                Forms\Components\DatePicker::make('tanggal_tanam')
                    ->label('Tanggal Tanam')
                    ->required(),
            ]);
    }

    /**
     * Defines the table structure for displaying related records.
     */
    public function table(Table $table): Table
    {
        return $table
            // Uses $recordTitleAttribute ('name' of Comodity) for identifying records in some contexts.
            ->recordTitleAttribute("name")
            ->allowDuplicates()
            ->columns([
                // Column from the related Comodity model
                Tables\Columns\TextColumn::make('name') // Assumes 'name' attribute on Comodity model
                    ->label('Nama Komoditas')
                    ->searchable()
                    ->sortable(),
                // Columns for PIVOT data
                Tables\Columns\TextColumn::make('qty')
                    ->label('Jumlah tanam (kg)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_tanam')
                    ->label('Tanggal Tanam')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                // Add any table filters here. If a Select filter uses ->relationship(),
                // ensure the relationship name is correct for the `Field` model (owner model).
                // Example:
                // Tables\Filters\SelectFilter::make('comodity_id')
                //    ->relationship('comodities', 'name') // Filters based on available comodities
            ])
            ->headerActions([
                // Action to attach existing Comodity records to the current Field.
                Tables\Actions\AttachAction::make()
                    ->label('Attach comodity')
                    // The `form()` method here is for collecting PIVOT data (`qty`, `tanggal_tanam`)
                    // AT THE TIME OF ATTACHING.
                    // The selection of `Comodity` records to attach is handled by an internal
                    // Select component within AttachAction. This internal Select uses the
                    // `$relationship` ('comodities') defined in this RelationManager.
                    // If this internal Select fails (causing the null relationship error),
                    // it means the 'comodities' relationship on your `Field` model is problematic.
                    // fn (Tables\Actions\AttachAction $action): array =>
                    ->form([
                        // 1. The Select component for choosing the Comodity to attach.
                        // We get Filament's default Select component for this action and customize it.
                        // $action->getRecordSelect()
                        //     ->label('comodity to Attach') // Customize label if needed
                        //     ->live() // Crucial: Makes this field reactive.
                        //     ->afterStateUpdated(function ($state, Set $set) {
                        //         // This function runs when the user selects a Comodity.
                        //         // $state contains the ID of the selected Comodity.
                        //         if (blank($state)) {
                        //             // If no comodity is selected, clear the stock display
                        //             $set('selected_comodity_stok', null);
                        //             return;
                        //         }
                        //         $comodity = Comodity::find($state);
                        //         if ($comodity) {
                        //             // Set the value of the 'selected_comodity_stok' field.
                        //             $set('selected_comodity_stok', $comodity->stok);
                        //         } else {
                        //             // Handle cases where comodity isn't found or stok isn't available.
                        //             $set('selected_comodity_stok', 'N/A (Stok info unavailable)');
                        //         }
                        //     }),
                        Forms\Components\Select::make("recordId")
                            ->label('Select Comodity')
                            ->options(Comodity::all()->pluck('name', 'id')) // Fetches all Comodities
                            ->live() // Crucial: Makes this field reactive.
                            ->afterStateUpdated(function ($state, Set $set) {
                                // This function runs when the user selects a Comodity.
                                // $state contains the ID of the selected Comodity.
                                if (blank($state)) {
                                    // If no comodity is selected, clear the stock display
                                    $set('selected_comodity_stok', null);
                                    return;
                                }
                                $comodity = Comodity::find($state);
                                if ($comodity) {
                                    // Set the value of the 'selected_comodity_stok' field.
                                    $set('selected_comodity_stok', $comodity->stok);
                                } else {
                                    // Handle cases where comodity isn't found or stok isn't available.
                                    $set('selected_comodity_stok', 'N/A (Stok info unavailable)');
                                }
                            })
                            ->preload(),
                        // 2. A TextInput to display the stok of the selected Comodity.
                        // This field is for display purposes only.
                        Forms\Components\TextInput::make('selected_comodity_stok')
                            ->label('Current Available Stok')
                            ->disabled() // User cannot edit this field.
                            ->placeholder('Select a comodity to view its stok')
                            // ->dehydrated(false) // Ensures this field's value isn't persisted if its name accidentally matches a column.
                            ,
                        // Additional fields for PIVOT data:
                        Forms\Components\TextInput::make('qty')
                            ->reactive()
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $selectedStok = $get('selected_comodity_stok');
                                            if (is_numeric($selectedStok) && $value > $selectedStok) {
                                                $fail("The quantity to attach ({$value}) cannot exceed the available stok of {$selectedStok}.");
                                            }
                                        },
                            ])
                            ->label('Quantity (qty)')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\DatePicker::make('tanggal_tanam')
                            ->label('Tanggal Tanam')
                            ->required(),
                    ])->preloadRecordSelect() // Preloads options in the Select component for better UX.
                    // ->multiple() // By default, AttachAction allows selecting multiple records.
                    ,
                // Optional: Action to create a new Comodity and attach it.
                // This would use the main `form()` method of the RelationManager.
                // Tables\Actions\CreateAction::make()
                //     ->label('Create and Attach comodity'),
            ])
            ->actions([
                // Action to edit the PIVOT data of an existing association.
                // This uses the main `form()` method of the RelationManager.
                Tables\Actions\EditAction::make()
                    ->label('Edit Pivot Data')
                    ->mutateRecordDataUsing(function (array $data, $record) {
                    // If you need to load pivot data into the edit form
                    $data['qty'] = $record->pivot->qty;
                    $data['tanggal_tanam'] = $record->pivot->tanggal_tanam;
                    // ... any other pivot fields
                    return $data;
                }),
                Tables\Actions\DetachAction::make(),
                // Tables\Actions\DeleteAction::make(), // Use with caution: deletes the Comodity record itself.
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    // Tables\Actions\DeleteBulkAction::make(), // Use with caution.
                ]),
            ]);
    }

    /**
     * Optional: If you need to modify the base query for the relation manager.
     * For example, to add default ordering for the related comodities.
     */
    // public function getTableQuery(): Builder
    // {
    //     return parent::getTableQuery()->orderBy('name', 'asc'); // Order comodities by name
    // }

    /**
     * Optional: Check if the relation manager can associate records.
     * Used by AttachAction.
     */
    // public static function canAssociate(Model $ownerRecord): bool
    // {
    //     return true; // Or some condition
    // }
}
