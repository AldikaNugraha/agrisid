<?php

namespace App\Filament\Resources\VillageResource\Pages;

use Filament\Resources\Pages\Page;
use App\Filament\Resources\VillageResource;
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
class VillageMap extends Page implements HasInfolists, HasForms
{
    use InteractsWithInfolists;
    use InteractsWithRecord;
    use InteractsWithForms;

    protected static string $resource = VillageResource::class;
    protected static string $view = 'filament.resources.village-resource.pages.village-map';


    public $village_id;
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
        $this->village_id = $this->record->id;
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
                    ->live()
                    ->afterStateUpdated(fn() => $this->processSelectedFieldsGeoJson()),
            ]);
    }

    protected function processSelectedFieldsGeoJson(): void
    {
        $geoJsonFeatures = [];
        $geoJsonGenerator = new GeojsonGenerator();

        // Fetch all farmer fields that have geometry data, keyed by ID for easy lookup
        $allFarmerFieldsWithGeometry = $this->record->fields()
            ->whereNotNull('batas')
            ->get()
            ->keyBy('id');

        // Get the currently selected field IDs from the form's state
        $currentlySelectedIds = $this->form->getState()['selectedFieldIds'] ?? [];

        if (empty($currentlySelectedIds)) {
            $this->fieldsGeoJson = ['type' => 'FeatureCollection', 'features' => []];
            $this->mapCenter = null; // Or a default center for the farmer
            $this->dispatch('geoJsonUpdated', geoJson: $this->fieldsGeoJson);
            $this->dispatch('mapCenterUpdated', center: $this->mapCenter);
            return;
        }

        foreach ($currentlySelectedIds as $fieldId) {
            $field = $allFarmerFieldsWithGeometry->get($fieldId);

            if ($field && $field->batas instanceof MultiPolygon && !$field->batas->isEmpty()) {
                try {
                    $geometryGeoJsonArray = $geoJsonGenerator->generate($field->batas);

                    if (isset($geometryGeoJsonArray['type']) && isset($geometryGeoJsonArray['coordinates'])) {
                        $geoJsonFeatures[] = [
                            'type' => 'Feature',
                            'geometry' => $geometryGeoJsonArray,
                            'properties' => [ // Add any properties you want in the GeoJSON popup
                                'id' => $field->id,
                                'name' => $field->name ?? "Field #{$field->id}",
                                // Add other relevant field properties here
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
        }

        $this->fieldsGeoJson = [
            'type' => 'FeatureCollection',
            'features' => $geoJsonFeatures,
        ];

        $this->calculateMapCenter(); // Recalculate center based on the new set of features

        // Dispatch events for AlpineJS/Leaflet to update
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
}
