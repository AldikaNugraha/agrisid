<x-filament-panels::page>
    {{-- Field Selection Form --}}
    <div class="p-4 mb-6 bg-white rounded-lg shadow dark:bg-gray-800 filament-forms-component-container">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Filter Fields on Map</h3>
        <div>
            {{ $this->form }}
            <div class="pt-4 mt-4">
                <x-filament::button
                    wire:click="applyFieldFilters"
                    type="button"
                    icon="heroicon-m-magnifying-glass"
                >
                    Update Map Filters
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- Your Map Container --}}
    <div class="top-0 left-0 w-full h-full position-absolute">
        <div style="height:calc(100vh - 220px);" class="z-10 w-full p-0 m-0 overflow-hidden position-relative">
            <div wire:ignore id='map' class="top-0 left-0 z-10 w-full h-full position-absolute"
                    {{-- Pass initial map center. initialFieldsGeoJson is no longer directly used by JS if mount dispatches IDs --}}
                    data-initial-center='@json($initialMapCenter ?? [-6.595038, 106.816635])'
                    {{-- Configure the ID property name your pygeoapi uses in its feature properties and for CQL filtering --}}
                    data-id-property-name="id" {{-- IMPORTANT: Change 'id' if your pygeoapi uses a different property name like 'gid' or 'field_pk' --}}
            ></div>
        </div>
    </div>

    @assets('scripts')
        @vite('resources/js/leaflet-map.js')
    @endassets
</x-filament-panels::page>
