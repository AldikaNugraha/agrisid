<?php

namespace App\Filament\Resources\FarmerResource\Pages;

use Filament\Resources\Pages\Page;
use App\Filament\Resources\FarmerResource;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Clickbar\Magellan\IO\Generator\Geojson\GeojsonGenerator;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Support\Facades\Log; // Import Log facade


class FarmerMap extends Page
{
    protected static string $resource = FarmerResource::class;

    protected static string $view = 'filament.resources.farmer-resource.pages.farmer-map';
    use InteractsWithRecord;
    public array $fieldsGeoJson = [ // Initialize as an empty FeatureCollection structure
        'type' => 'FeatureCollection',
        'features' => [],
    ];
    public ?array $mapCenter = null; // To store the center for Leaflet setView

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $geoJsonFeatures = [];
        $geoJsonGenerator = new GeojsonGenerator(); // Instantiate the generator

        if ($this->record->relationLoaded('fields')) {
            $fields = $this->record->fields;
        } else {
            $fields = $this->record->fields()->whereNotNull('batas')->get();
        }

        foreach ($fields as $field) {
            if ($field->batas instanceof MultiPolygon && !$field->batas->isEmpty()) {
                try {
                    $geometryGeoJsonArray = $geoJsonGenerator->generate($field->batas);

                    if (isset($geometryGeoJsonArray['type']) && isset($geometryGeoJsonArray['coordinates'])) {
                        $geoJsonFeatures[] = [
                            'type' => 'Feature',
                            'geometry' => $geometryGeoJsonArray,
                            'properties' => [],
                        ];
                    } else {
                        Log::warning("GeojsonGenerator did not produce expected GeoJSON structure for Field ID: {$field->id}.");
                    }
                } catch (\Clickbar\Magellan\Exception\MissingGeodeticSRIDException $e) {
                    Log::error("MissingGeodeticSRIDException for Field ID: {$field->id} - " . $e->getMessage());
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
