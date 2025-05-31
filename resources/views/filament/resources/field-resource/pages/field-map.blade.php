<x-filament-panels::page>
    <div class="top-0 left-0 w-full h-full position-absolute">
        <div style="height:calc(100vh - 65px);" class="z-10 w-full p-0 m-0 overflow-hidden position-relative">
            <div wire:ignore id='map' class="top-0 left-0 z-10 w-full h-full position-absolute" ></div>
        </div>
    </div>
    @assets('scripts')
        <script>
            const fieldId = @json($this->field_id);
        </script>
        @vite('resources/js/leaflet-map-static.js')
    @endassets
</x-filament-panels::page>
