<?php

namespace App\Filament\Resources\FarmerResource\Pages;

use App\Filament\Resources\FarmerResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class FarmerMap extends Page implements HasInfolists, HasForms
{
    use InteractsWithInfolists;
    use InteractsWithRecord;
    use InteractsWithForms;

    protected static string $resource = FarmerResource::class;
    protected static string $view = 'filament.resources.farmer-resource.pages.farmer-map';

    // This public property will hold the form data.
    // Filament's InteractsWithForms trait uses 'data' as the default state path.
    public ?array $data = [];

    // These will now be primarily for initial map setup in Blade,
    // or could be removed if the map always loads empty and waits for JS.
    // For simplicity, let's keep them for initial empty/default state.
    public array $initialFieldsGeoJson = ['type' => 'FeatureCollection', 'features' => []];
    public ?array $initialMapCenter = [-6.595038, 106.816635]; // Default center

    // This remains crucial for populating the Select dropdown options
    public ?array $fieldOptionsForForm = null;

    // public $village_id; // If you use this

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->loadFieldOptionsForForm(); // For the Select dropdown

        // if ($this->record && $this->record->village) {
        //     $this->village_id = $this->record->village->id;
        // }

        $initialSelectedIds = array_keys($this->fieldOptionsForForm ?? []);

        // The fill() method populates $this->data (or the configured state path)
        $this->form->fill([
            'selectedFieldIds' => $initialSelectedIds,
        ]);

        // Dispatch initial set of IDs so the map can load them on page load
        // This makes the button-driven update more consistent with initial load.
        $this->dispatchSelectedIds($initialSelectedIds);
    }

    protected function loadFieldOptionsForForm(): void
    {
        // This query is only for populating the dropdown.
        // It doesn't need to fetch 'batas' or other geometry details anymore.
        $farmerFieldsCollection = $this->record->fields()
            // ->whereNotNull('batas') // Not strictly needed for options if some fields might not have geometry yet
            ->select(['id', 'name']) // Only need id and name for the dropdown
            ->get();

        $options = [];
        foreach ($farmerFieldsCollection as $field) {
            $options[$field->id] = $field->name ?? "Field #{$field->id}";
        }
        $this->fieldOptionsForForm = $options;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('selectedFieldIds')
                    ->label('Select Fields to Display on Map')
                    ->options($this->fieldOptionsForForm ?? [])
                    ->multiple()
                    ->searchable()
                    // No ->live() or ->afterStateUpdated() as we use a button
            ])
            ->statePath('data'); // Explicitly set state path to 'data' for clarity, though it's the default.
    }

    /**
     * Action method called by the button.
     * Gets selected IDs and dispatches them to the frontend.
     */
    public function applyFieldFilters(): void
    {
        // Access the 'selectedFieldIds' directly from the component's $data property.
        // The form components bind their state to this property.
        $currentlySelectedIds = $this->data['selectedFieldIds'] ?? [];

        $this->dispatchSelectedIds($currentlySelectedIds);
    }

    /**
     * Helper method to dispatch the selected field IDs to the frontend.
     */
    protected function dispatchSelectedIds(array $ids): void
    {
        // Ensure IDs are integers or strings as expected by your JS/pygeoapi
        $sanitizedIds = array_map(function($id) {
            return is_numeric($id) ? (int)$id : (string)$id;
        }, $ids);

        $this->dispatch('selectedFieldIdsUpdated', selectedIds: $sanitizedIds);
    }

    // processSelectedFieldsGeoJson and calculateMapCenter are no longer needed here
    // as this logic moves to the frontend JavaScript.

    // infolist method can remain if you use it
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('address'),
                Infolists\Components\TextEntry::make('age')->numeric(),
                Infolists\Components\TextEntry::make('phone')->numeric(),
            ]);
    }
}
