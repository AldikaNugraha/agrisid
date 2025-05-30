<x-filament-panels::page>
    {{-- Field Selection Form --}}
    <div class="p-4 mb-6 bg-white rounded-lg shadow dark:bg-gray-800 filament-forms-component-container">
        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Filter Fields on Map</h3>
        {{--
            The form tag itself isn't strictly necessary for submission if using a Livewire action button,
            but it helps group the form elements visually and semantically.
            We use wire:submit.prevent on the button's action if it were a type="submit".
            Here, we'll use a standard button with wire:click.
        --}}
        <div> {{-- Changed form to div or you can keep form tag without wire:submit --}}
            {{ $this->form }}

            <div class="pt-4 mt-4">
                <x-filament::button
                    wire:click="applyFieldFilters"
                    type="button" {{-- Important: type="button" to prevent default form submission --}}
                    icon="heroicon-m-magnifying-glass" {{-- Example icon --}}
                >
                    Update Map Filters
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- Your Map Container --}}
    <div class="top-0 left-0 w-full h-full position-absolute">
        <div style="height:calc(100vh - 220px);" class="w-full p-0 m-0 overflow-hidden z-1 position-relative">
            {{-- Adjusted height slightly to accommodate the form if it's above the map viewport --}}
            {{-- Pass initial data as data attributes --}}
            <div wire:ignore id='map' class="top-0 left-0 w-full h-full z-1 position-absolute"
                    data-initial-geojson='@json($fieldsGeoJson)'
                    data-initial-center='@json($mapCenter ?? [-6.595038, 106.816635])'
            ></div>
        </div>
    </div>

    @assets('scripts')
        <script>
            const fieldsGeoJsonData = @json($this->fieldsGeoJson);
        </script>
        @vite('resources/js/leaflet-map.js')
    @endassets
</x-filament-panels::page>
