<?php

namespace App\Filament\Resources\FarmerResource\Pages;

use Filament\Resources\Pages\Page;
use App\Filament\Resources\FarmerResource;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Clickbar\Magellan\IO\Generator\Geojson\GeojsonGenerator;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Support\Facades\Log; // Import Log facade
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms;
use Filament\Forms\Form;

class FarmerMap extends Page implements HasInfolists, HasForms
{
    use InteractsWithInfolists;
    use InteractsWithRecord;
    use InteractsWithForms;

    protected static string $resource = FarmerResource::class;
    protected static string $view = 'filament.resources.farmer-resource.pages.farmer-map';

    public array $fieldsGeoJson = [ // Initialize as an empty FeatureCollection structure
        'type' => 'FeatureCollection',
        'features' => [],
    ];
    public ?array $mapCenter = null; // To store the center for Leaflet setView
    public array $selectedFieldIds = [];
    public ?array $fieldOptionsForForm = null;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->loadFieldOptionsForForm();

        // Initialize the form. This will make $this->form available.
        // The fill method populates the form with initial data.
        // Here, we pre-select all field IDs.
        $this->form->fill([
            'selectedFieldIds' => array_keys($this->fieldOptionsForForm ?? []),
        ]);

        $this->processSelectedFieldsGeoJson(); // Generate initial GeoJSON based on default selection
    }

    protected function loadFieldOptionsForForm(): void
    {
        // Assuming $this->record->fields is the relationship returning a collection of Field models
        // And each Field model has an 'id' and a 'name' (or some other identifiable property)
        $farmerFieldsCollection = $this->record->relationLoaded('fields') ?
            $this->record->fields :
            $this->record->fields()->whereNotNull('batas')->get();

        $options = [];
        foreach ($farmerFieldsCollection as $field) {
            // Use a descriptive name for the field in the checklist
            $options[$field->id] = $field->name ?? "Field #{$field->id}"; // Adjust 'name' if your field attribute is different
        }
        $this->fieldOptionsForForm = $options;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('selectedFieldIds') // Changed from CheckboxList
                    ->label('Select Fields to Display on Map')
                    ->options($this->fieldOptionsForForm ?? [])
                    ->multiple()     // Allows selecting multiple fields
                    ->searchable()   // Makes the dropdown searchable
                    ->preload()
                    // ->live()
                    // ->afterStateUpdated(fn() => $this->processSelectedFieldsGeoJson()),
            ]);
    }

    public function applyFieldFilters(): void
    {
        $this->processSelectedFieldsGeoJson();
    }

    protected function processSelectedFieldsGeoJson(): void
    {
        // Get the currently selected field IDs from the form's state
        $currentlySelectedIds = $this->form->getState()['selectedFieldIds'] ?? [];

        // Early exit if no fields are selected
        if (empty($currentlySelectedIds)) {
            $this->fieldsGeoJson = ['type' => 'FeatureCollection', 'features' => []];
            $this->mapCenter = null; // Or a default center for the farmer
            $this->dispatchEvents();
            return;
        }

        // Fetch only the selected fields that have geometry data and necessary columns
        $selectedFieldsWithGeometry = $this->record->fields()
            ->whereNotNull('batas')
            ->whereIn('id', $currentlySelectedIds) // Filter by selected IDs at the DB level
            ->select(['id', 'name', 'batas'])      // Select only necessary columns for performance
            ->get();

        // If, after filtering, no valid fields are found (e.g., selected IDs had no geometry)
        if ($selectedFieldsWithGeometry->isEmpty()) {
            $this->fieldsGeoJson = ['type' => 'FeatureCollection', 'features' => []];
            $this->mapCenter = null;
            $this->dispatchEvents();
            return;
        }

        $geoJsonGenerator = new GeojsonGenerator();

        // Use collection map to transform fields into GeoJSON features
        $geoJsonFeatures = $selectedFieldsWithGeometry->map(function ($field) use ($geoJsonGenerator) {
            // Ensure 'batas' is a MultiPolygon and not empty
            if ($field->batas instanceof MultiPolygon && !$field->batas->isEmpty()) {
                try {
                    $geometryGeoJsonArray = $geoJsonGenerator->generate($field->batas);
                    // Validate the generated GeoJSON structure
                    if (isset($geometryGeoJsonArray['type']) && isset($geometryGeoJsonArray['coordinates'])) {
                        return [
                            'type' => 'Feature',
                            'geometry' => $geometryGeoJsonArray,
                            'properties' => [
                                'id' => $field->id,
                                'name' => $field->name ?? "Field #{$field->id}",
                                // Add other relevant field properties here if needed for popups
                            ],
                        ];
                    } else {
                        Log::warning("GeojsonGenerator did not produce expected GeoJSON structure for Field ID: {$field->id}. Output: " . json_encode($geometryGeoJsonArray));
                    }
                } catch (\Clickbar\Magellan\Exception\MissingGeodeticSRIDException $e) {
                    Log::error("MissingGeodeticSRIDException for Field ID: {$field->id} - " . $e->getMessage());
                } catch (\Exception $e) {
                    Log::error("Error generating GeoJSON for Field ID: {$field->id} - " . $e->getMessage());
                }
            }
            return null; // Return null for fields that couldn't be processed or don't meet criteria
        })
        ->filter() // Remove any null entries (failed conversions or invalid fields)
        ->values()   // Re-index the array to ensure it's a zero-indexed array for JSON
        ->all();     // Convert the collection to a plain PHP array

        $this->fieldsGeoJson = [
            'type' => 'FeatureCollection',
            'features' => $geoJsonFeatures,
        ];

        $this->calculateMapCenter(); // Recalculate center based on the new set of features
        $this->dispatchEvents();
    }

    protected function dispatchEvents(): void
    {
        $this->dispatch('geoJsonUpdated', geoJson: $this->fieldsGeoJson);
        $this->dispatch('mapCenterUpdated', center: $this->mapCenter);
    }

    protected function calculateMapCenter(): void
    {
        // Reset map center
        $this->mapCenter = null;

        if (!empty($this->fieldsGeoJson['features'])) {
            // Basic approach: center on the first point of the first feature
            // A more robust solution would calculate the bounding box of all features and find its center.
            $firstFeature = $this->fieldsGeoJson['features'][0];
            if (isset($firstFeature['geometry']['coordinates'])) {
                $coords = $firstFeature['geometry']['coordinates'];

                // GeoJSON coordinates are [longitude, latitude]
                // Leaflet's setView expects [latitude, longitude]
                if ($firstFeature['geometry']['type'] === 'MultiPolygon' && !empty($coords[0][0][0])) {
                    $this->mapCenter = [$coords[0][0][0][1], $coords[0][0][0][0]];
                } elseif ($firstFeature['geometry']['type'] === 'Polygon' && !empty($coords[0][0])) {
                    $this->mapCenter = [$coords[0][0][1], $coords[0][0][0]];
                }
                // Add more sophisticated logic here if needed, e.g., calculate centroid of all features
            }
        }

        if (!$this->mapCenter && $this->record) {
            // Fallback to a default location, e.g., farmer's main location or a general default
            // Example: $this->mapCenter = [$this->record->latitude ?? -6.595038, $this->record->longitude ?? 106.816635];
            $this->mapCenter = [-6.595038, 106.816635]; // Default to Bogor, Indonesia or your preferred default
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('address'),
                Infolists\Components\TextEntry::make('age')
                    ->numeric(),
                Infolists\Components\TextEntry::make('phone')
                    ->numeric(),
            ]);
    }

}
