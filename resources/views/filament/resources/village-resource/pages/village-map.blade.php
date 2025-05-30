<x-filament-panels::page>
    {{-- Field Selection Form --}}
    <div class="z-20 p-4 mb-6 bg-white rounded-lg shadow dark:bg-gray-800 filament-forms-component-container">
        {{-- The class above helps with Filament's default styling for form containers --}}
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Filter Fields on Map</h3>
        <form wire:submit.prevent class="z-20"> {{-- Using wire:submit.prevent as the form is reactive via Livewire --}}
            {{ $this->form }}
            {{-- No explicit submit button needed due to ->live() and ->afterStateUpdated() in the PHP form definition --}}
        </form>
    </div>

    {{-- Your Map Container --}}
    <div class="top-0 left-0 w-full h-full position-absolute">
        <div style="height:calc(100vh - 150px);" class="z-10 w-full p-0 m-0 overflow-hidden position-relative">
            {{-- Adjusted height slightly to accommodate the form if it's above the map viewport --}}
            {{-- Pass initial data as data attributes --}}
            <div wire:ignore id='map' class="top-0 left-0 z-10 w-full h-full position-absolute"
                    data-initial-geojson='@json($fieldsGeoJson)'
                    data-initial-center='@json($mapCenter ?? [-6.595038, 106.816635])'
            ></div>
        </div>
    </div>
    @assets('scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.locatecontrol/0.71.0/L.Control.Locate.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.locatecontrol/0.71.0/L.Control.Locate.min.js"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <script>
            const fieldsGeoJsonData = @json($this->fieldsGeoJson);
        </script>
        @vite('resources/js/leaflet-map.js')
    @endassets
</x-filament-panels::page>
