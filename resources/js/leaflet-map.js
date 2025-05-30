// Import Leaflet's JavaScript
import L from 'leaflet';

// Import Leaflet's CSS
import 'leaflet/dist/leaflet.css';

// It's good practice to also handle Leaflet's default icon image paths
// when bundling with tools like Vite/Webpack.
// This ensures markers display correctly.
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.error('Map element not found!');
        return;
    }

    // Retrieve initial data from data attributes
    let initialGeoJson;
    let initialCenter;
    // let villageId; // Example if you use village_id

    try {
        initialGeoJson = JSON.parse(mapElement.dataset.initialGeojson);
        initialCenter = JSON.parse(mapElement.dataset.initialCenter);
    } catch (e) {
        console.error('Error parsing initial map data from data attributes:', e);
        // Fallback initial data if parsing fails
        initialGeoJson = { type: 'FeatureCollection', features: [] };
        initialCenter = [-6.595038, 106.816635]; // Default center (e.g., Bogor)
    }

    // Initialize the map
    var map = L.map('map').setView(initialCenter, 13);
    var geoJsonLayer = null; // To store the GeoJSON layer

    // Add Tile Layer (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Function to render/update GeoJSON features on the map
    function renderFeatures(geoJsonData) {
        if (geoJsonLayer) {
            map.removeLayer(geoJsonLayer); // Remove existing layer
            geoJsonLayer = null;
        }

        if (geoJsonData && geoJsonData.features && geoJsonData.features.length > 0) {
            geoJsonLayer = L.geoJSON(geoJsonData, {
                onEachFeature: function (feature, layer) {
                    // Customize popups or other interactions here
                    if (feature.properties) {
                        let popupContent = '';
                        if (feature.properties.name) {
                            popupContent += `<strong>${feature.properties.name}</strong><br>`;
                        }
                        // Add more properties to the popup if needed
                        // for (const key in feature.properties) {
                        //     if (key !== 'name' && Object.prototype.hasOwnProperty.call(feature.properties, key)) {
                        //         popupContent += `${key}: ${feature.properties[key]}<br>`;
                        //     }
                        // }
                        if (popupContent) {
                            layer.bindPopup(popupContent);
                        }
                    }
                }
            }).addTo(map);

            // Fit map to the bounds of the new features
            try {
                if (geoJsonLayer.getBounds().isValid()) {
                    map.fitBounds(geoJsonLayer.getBounds());
                }
            } catch (e) {
                console.warn("Could not fit map to bounds, possibly no valid geometries.", e);
                // If fitBounds fails (e.g., single point or invalid data), set view to initial/current center
                if (initialCenter) map.setView(initialCenter, 15);
            }
        } else {
            // No features to display, maybe clear map or show default message
            console.log('No features to display.');
            if (initialCenter) map.setView(initialCenter, 13); // Reset to a default view
        }
    }

    // Function to update map center
    function updateMapView(centerCoordinates) {
        if (centerCoordinates && Array.isArray(centerCoordinates) && centerCoordinates.length === 2) {
            map.setView(centerCoordinates, map.getZoom()); // Keep current zoom or set a default
        } else if (geoJsonLayer && geoJsonLayer.getLayers().length === 0) {
            // If no features and no specific center, set to default
            map.setView(initialCenter, 10); // Zoom out a bit more
        }
    }

    // Initial rendering of features
    renderFeatures(initialGeoJson);
    if (initialCenter && (!initialGeoJson || !initialGeoJson.features || initialGeoJson.features.length === 0)) {
         // If no features initially, ensure map is centered
        map.setView(initialCenter, 13);
    }


    // --- Event Listeners for Livewire dispatched events ---

    // Listen for GeoJSON updates
    window.addEventListener('geoJsonUpdated', event => {
        console.log('Livewire event: geoJsonUpdated received', event.detail.geoJson);
        if (event.detail && event.detail.geoJson) {
            renderFeatures(event.detail.geoJson);
        }
    });

    // Listen for Map Center updates
    window.addEventListener('mapCenterUpdated', event => {
        console.log('Livewire event: mapCenterUpdated received', event.detail.center);
        if (event.detail && event.detail.center) {
            updateMapView(event.detail.center);
        }
    });

    // Handle map resize to ensure it displays correctly if container size changes
    // This is useful if the map is in a dynamic layout.
    const resizeObserver = new ResizeObserver(() => {
        map.invalidateSize();
    });
    resizeObserver.observe(mapElement);

});
