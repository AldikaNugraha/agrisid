<?php

namespace App\Filament\Resources\FieldResource\Pages;

use App\Filament\Resources\FieldResource;
use Filament\Resources\Pages\Page;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Clickbar\Magellan\IO\Generator\Geojson\GeojsonGenerator;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Support\Facades\Log; // Import Log facade

class FieldMap extends Page
{
    protected static string $resource = FieldResource::class;
    protected static string $view = 'filament.resources.field-resource.pages.field-map';
    use InteractsWithRecord;

    public array $fieldsGeoJson =
    [
        'type' => 'FeatureCollection',
        'features' => [],
    ];
    public ?array $mapCenter = null;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $geoJsonFeatures = [];
        $geoJsonGenerator = new GeojsonGenerator();
        $batas = $this->record->batas;

        if ($batas instanceof MultiPolygon && !$batas->isEmpty()) {
            try {
                // Use the generator to convert the MultiPolygon object to a GeoJSON array
                $geometryGeoJsonArray = $geoJsonGenerator->generate($batas);
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
                    Log::warning("GeojsonGenerator did not produce expected GeoJSON structure for Field ID: {$this->record->id}.");
                }
            } catch (\Clickbar\Magellan\Exception\MissingGeodeticSRIDException $e) {
                // Catch specific exception from the generator if SRID is not geodetic
                Log::error("MissingGeodeticSRIDException for Field ID: {$this->record->id} - " . $e->getMessage());
                // You might want to skip this feature or handle it differently
            } catch (\Exception $e) {
                Log::error("Error generating GeoJSON for Field ID: {$this->record->id} - " . $e->getMessage());
            }
        }

        if (!empty($geoJsonFeatures)) {
            $this->fieldsGeoJson['features'] = $geoJsonFeatures;
        }
    }
}
