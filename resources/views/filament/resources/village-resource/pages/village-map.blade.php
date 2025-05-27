<x-filament-panels::page>
    <div class="top-0 left-0 w-full h-full position-absolute">
        <div style="height:calc(100vh - 65px);" class="z-10 w-full p-0 m-0 overflow-hidden position-relative">
            <div wire:ignore id='map' class="top-0 left-0 z-10 w-full h-full position-absolute" ></div>
        </div>
    </div>
    @assets('scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.locatecontrol/0.71.0/L.Control.Locate.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.locatecontrol/0.71.0/L.Control.Locate.min.js"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <script>
            const village_id = @json($this->village_id);
            const fieldsGeoJsonData = @json($this->fieldsGeoJson);
        </script>
        @vite('resources/js/leaflet-map.js')
    @endassets
</x-filament-panels::page>
