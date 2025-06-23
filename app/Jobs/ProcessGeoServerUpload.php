<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Http\Client\ConnectionException;
use Exception;
use Illuminate\Support\Str;

class ProcessGeoServerUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Job properties for retrying on failure
    public int $tries = 3; // Attempt the job 3 times
    public int $backoff = 60; // Wait 60 seconds between retries

    /**
     * Create a new job instance.
     * The controller will pass the file path to this job.
     */
    public function __construct(
        public string $absolutePath,
        public string | null $layerName = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fastApiUrl = 'http://127.0.0.1:8070/create_coverage_store/';

        // If a layer name wasn't provided, derive it from the path
        $layer = $this->layerName ?? Str::of($this->absolutePath)->basename('.tif')->basename('.tiff')->slug('_');

        Log::info("Processing GeoServer upload for layer '{$layer}' from path: {$this->absolutePath}");

        try {
            $response = Http::post($fastApiUrl, [
                'file_path' => $this->absolutePath,
                'workspace' => 'WMS',
                'layer_name' => $layer,
            ]);

            if ($response->successful()) {
                Log::info("Successfully created GeoServer layer '{$layer}'.");
                // OPTIONAL: Send a notification to the user, update database, etc.
            } else {
                // The API returned an error, so we throw an exception
                // The queue worker will catch this and handle the failure/retry.
                $errorMessage = $response->json()['detail'] ?? 'An unknown API error occurred.';
                throw new Exception("Failed to create GeoServer layer. API Response: " . $errorMessage);
            }

        } catch (ConnectionException $e) {
            Log::error("Could not connect to the API service while processing layer '{$layer}'.", ['exception' => $e]);
            // Release the job back onto the queue to be retried later
            $this->release(120); // Wait 2 minutes before the next attempt
        } catch (Exception $e) {
            Log::error("An exception occurred while processing layer '{$layer}'.", ['exception' => $e]);
            // By re-throwing, we let the queue worker know the job failed
            // It will be moved to the failed_jobs table after all retries are exhausted.
            throw $e;
        }
    }
}

?>
