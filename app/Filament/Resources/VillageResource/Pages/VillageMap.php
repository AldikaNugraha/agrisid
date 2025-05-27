<?php

namespace App\Filament\Resources\VillageResource\Pages;

use App\Filament\Resources\VillageResource;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Clickbar\Magellan\IO\Generator\Geojson\GeojsonGenerator;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Support\Facades\Log; // Import Log facade

class VillageMap extends Page
{
    protected static string $resource = VillageResource::class;

    protected static string $view = 'filament.resources.village-resource.pages.village-map';

    use InteractsWithRecord;

    public $village_id;
    public array $fieldsGeoJson = [ // Initialize as an empty FeatureCollection structure
        'type' => 'FeatureCollection',
        'features' => [],
    ];
    public ?array $mapCenter = null; // To store the center for Leaflet setView

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->village_id = $this->record->id;

        $geoJsonFeatures = [];
        $geoJsonGenerator = new GeojsonGenerator(); // Instantiate the generator

        // Assuming your Village model has a 'fields' relationship
        // e.g., public function fields() { return $this->hasMany(Field::class); }
        if ($this->record->relationLoaded('fields')) {
            $fields = $this->record->fields;
        } else {
            $fields = $this->record->fields()->whereNotNull('batas')->get();
        }

        foreach ($fields as $field) {
            if ($field->batas instanceof MultiPolygon && !$field->batas->isEmpty()) {
                try {
                    // Use the generator to convert the MultiPolygon object to a GeoJSON array
                    $geometryGeoJsonArray = $geoJsonGenerator->generate($field->batas);
                    // The generate method should handle specific types like MultiPolygon,
                    // or you could use $geoJsonGenerator->generateMultiPolygon($field->batas);

                    // Ensure the generated array looks like a valid GeoJSON geometry
                    if (isset($geometryGeoJsonArray['type']) && isset($geometryGeoJsonArray['coordinates'])) {
                        $geoJsonFeatures[] = [
                            'type' => 'Feature',
                            'geometry' => $geometryGeoJsonArray, // This is now the GeoJSON geometry array
                            'properties' => [],
                        ];
                    } else {
                        Log::warning("GeojsonGenerator did not produce expected GeoJSON structure for Field ID: {$field->id}.");
                    }
                } catch (\Clickbar\Magellan\Exception\MissingGeodeticSRIDException $e) {
                    // Catch specific exception from the generator if SRID is not geodetic
                    Log::error("MissingGeodeticSRIDException for Field ID: {$field->id} - " . $e->getMessage());
                    // You might want to skip this feature or handle it differently
                } catch (\Exception $e) {
                    Log::error("Error generating GeoJSON for Field ID: {$field->id} - " . $e->getMessage());
                }
            }
        }

        if (!empty($geoJsonFeatures)) {
            $this->fieldsGeoJson['features'] = $geoJsonFeatures;
        }
    }
}
