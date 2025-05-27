<?php

namespace App\Filament\Resources\FieldResource\Pages;

use App\Filament\Resources\FieldResource;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage; // Import Storage facade
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Validation\ValidationException; // Import ValidationException

// Magellan specific imports
use Clickbar\Magellan\IO\GeometryModelFactory; // For GeojsonParser constructor
use Clickbar\Magellan\IO\Parser\Geojson\GeojsonParser;
use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Clickbar\Magellan\Data\Geometries\Polygon;
use Clickbar\Magellan\Data\Geometries\Dimension; // Import Dimension

class CreateField extends CreateRecord
{
    protected static string $resource = FieldResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['batas_file']) && is_string($data['batas_file']) && !empty($data['batas_file'])) {
            $filePath = $data['batas_file'];
            $diskName = config('filament.default_filesystem_disk') ?: config('filesystems.default');
            $originalExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $fileNameForLog = pathinfo($filePath, PATHINFO_BASENAME);

            if ($originalExtension === 'geojson') {
                try {
                    if (!Storage::disk($diskName)->exists($filePath)) {
                        Log::error('Uploaded GeoJSON file not found on disk.', ['disk' => $diskName, 'path' => $filePath]);
                        throw ValidationException::withMessages(['batas_file' => 'The uploaded file could not be found. Please try uploading again.']);
                    }

                    $fileContents = Storage::disk($diskName)->get($filePath);
                    if ($fileContents === null || $fileContents === false) {
                        Log::error('Failed to read GeoJSON file content from disk.', ['disk' => $diskName, 'path' => $filePath]);
                        throw new \Exception('Failed to read file content.');
                    }

                    $factory = app(GeometryModelFactory::class);
                    $parser = new GeojsonParser($factory);
                    $srid = 4326; // Default SRID, will be updated if geometry has one
                    // Default dimension, can be updated if geometry has one.
                    // The MultiPolygon::make method has a default for dimension, so this might not be strictly needed here
                    // unless you want to derive it specifically from the input geometries.
                    $dimension = Dimension::DIMENSION_2D;


                    // Decode the GeoJSON string to inspect its type
                    $decodedGeoJson = json_decode($fileContents, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('Invalid GeoJSON format: Could not decode JSON string.');
                    }

                    if (!isset($decodedGeoJson['type'])) {
                        throw new \Exception('Invalid GeoJSON: Missing top-level "type" property.');
                    }

                    $finalPolygons = [];

                    if ($decodedGeoJson['type'] === 'FeatureCollection') {
                        if (!isset($decodedGeoJson['features']) || !is_array($decodedGeoJson['features'])) {
                            throw new \Exception('Invalid GeoJSON FeatureCollection: Missing or invalid "features" array.');
                        }
                        foreach ($decodedGeoJson['features'] as $index => $feature) {
                            if (!isset($feature['geometry'])) {
                                Log::warning("Feature at index {$index} in FeatureCollection is missing a geometry.", ['file' => $fileNameForLog]);
                                continue;
                            }
                            $geometryOfFeature = $parser->parse($feature['geometry']);
                            $srid = $geometryOfFeature->getSrid() ?? $srid;
                            $dimension = $geometryOfFeature->getDimension(); // Get dimension from parsed geometry

                            if ($geometryOfFeature instanceof Polygon) {
                                $finalPolygons[] = $geometryOfFeature;
                            } elseif ($geometryOfFeature instanceof MultiPolygon) {
                                foreach ($geometryOfFeature->getPolygons() as $p) {
                                    $finalPolygons[] = $p;
                                }
                            } else {
                                Log::warning("Feature at index {$index} contains an unhandled geometry type: " . get_class($geometryOfFeature), ['file' => $fileNameForLog]);
                            }
                        }
                    } else {
                        $geometry = $parser->parse($fileContents);
                        $srid = $geometry->getSrid() ?? $srid;
                        $dimension = $geometry->getDimension(); // Get dimension

                        if ($geometry instanceof Polygon) {
                            $finalPolygons[] = $geometry;
                        } elseif ($geometry instanceof MultiPolygon) {
                            foreach ($geometry->getPolygons() as $p) {
                                $finalPolygons[] = $p;
                            }
                        } else {
                            $geoType = is_object($geometry) ? get_class($geometry) : gettype($geometry);
                            throw new \Exception("The parsed GeoJSON is not a supported geometry type for the 'batas' field. Parsed type: " . $geoType);
                        }
                    }

                    if (empty($finalPolygons)) {
                        throw new \Exception("No valid Polygons could be extracted from the uploaded GeoJSON file to form a MultiPolygon.");
                    }
                    // Use the static make method
                    $data['batas'] = MultiPolygon::make($finalPolygons, $srid, $dimension);


                } catch (\RuntimeException $e) {
                    Log::error('GeoJSON Parsing RuntimeException for field boundary: ' . $e->getMessage(), [
                        'file' => $fileNameForLog, 'path_in_data' => $data['batas_file'], 'disk' => $diskName, 'trace' => $e->getTraceAsString(),
                    ]);
                    $userMessage = 'The uploaded GeoJSON file is invalid or uses an unsupported structure. Parser error: ' . $e->getMessage();
                    throw ValidationException::withMessages(['batas_file' => $userMessage]);
                } catch (\Exception $e) {
                    Log::error('Error processing GeoJSON file for field boundary: ' . $e->getMessage(), [
                        'file' => $fileNameForLog, 'path_in_data' => $data['batas_file'], 'disk' => $diskName, 'trace' => $e->getTraceAsString(),
                    ]);
                    throw ValidationException::withMessages(['batas_file' => 'Could not process GeoJSON: ' . $e->getMessage()]);
                }
            } else {
                throw ValidationException::withMessages(['batas_file' => 'Invalid file type. Only GeoJSON files (.geojson) are accepted.']);
            }
            unset($data['batas_file']);
        } elseif (isset($data['batas_file']) && empty($data['batas_file'])) {
            unset($data['batas_file']);
        }
        return $data;
    }
    protected function handleRecordCreation(array $data): Model
    {
        // dd($data);
        $record = static::getModel()::create($data);
        return $record;
    }
}
