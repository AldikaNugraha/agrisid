<?php

namespace App\Filament\Resources\BuyerResource\Pages;

use App\Filament\Resources\BuyerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;


class CreateBuyer extends CreateRecord
{
    protected static string $resource = BuyerResource::class;

    // protected function mutateFormDataBeforeCreate(array $respone_data): array
    // {

    // }
    //     if ($respone_data['source'] == 'satellite') {
    //         $file_name = $respone_data['region'];
    //         // $this->file_path = storage_path("app/public/{$file_name}");
    //         $context = stream_context_create([
    //             'ssl' => [
    //                 'verify_peer' => false,
    //                 'verify_peer_name' => false,
    //             ],
    //         ]);
    //         $file_content = file_get_contents($this->file_path, false, $context);
    //         $this->region_geojson = json_encode(json_decode($file_content, true));

    //         $this->satellite_data = [
    //             'project_id' => $respone_data['project_id'],
    //             'name' => $respone_data['name'],
    //             'source' => $respone_data['source'],
    //             'sattelite_source' => $respone_data['sattelite_source'],
    //             'do_monitoring' => $respone_data['do_monitoring'],
    //             'region' => $this->region_geojson,
    //             'start_date' => $respone_data['start_date'],
    //             'end_date' => $respone_data['end_date'],
    //         ];
    //     }

    //     return $respone_data;
    // }

    protected function handleRecordCreation(array $data): Model
    {
        // dd($data);
        $record = static::getModel()::create($data);
        return $record;
    }
}
