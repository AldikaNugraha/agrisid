import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Handle Leaflet's default icon image paths for bundling
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
  iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
  shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.error('Map element with ID "map" not found!');
        return;
    }

    // --- pygeoapi Configuration ---
    const pygeoapiBaseUrl = 'http://localhost:8050'; // Your pygeoapi base URL
    const collectionId = 'fields-postgis';          // Your collection ID
    const itemsUrl = `${pygeoapiBaseUrl}/collections/${collectionId}/items`;

    // This should match the actual ID property name in your GeoJSON features' properties
    // as returned by pygeoapi, and used in the CQL2 filter.
    const idPropertyNameInFeatures = mapElement.dataset.idPropertyName || 'id'; // Default to 'id', make configurable if needed

    // --- End pygeoapi Configuration ---

    let initialMapCenter;
    try {
        initialMapCenter = JSON.parse(mapElement.dataset.initialCenter);
    } catch (e) {
        initialMapCenter = [-6.595038, 106.816635]; // Default center
    }

    const map = L.map('map').setView(initialMapCenter, 13);
    let geoJsonLayer = null; // To store the current GeoJSON layer

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);


    // Function to clear existing features from the map
    function clearMapFeatures() {
        if (geoJsonLayer) {
            map.removeLayer(geoJsonLayer);
            geoJsonLayer = null;
        }
    }

    // Function to fetch and render features from pygeoapi based on IDs
    async function fetchAndRenderFields(featureIdsToFetch) {
        clearMapFeatures();

        if (!featureIdsToFetch || featureIdsToFetch.length === 0) {
            console.log("No field IDs provided to fetch. Map cleared.");
            // Optionally set a default view if map is empty
            map.setView(initialMapCenter, 10); // Zoom out if no features
            return;
        }

        // Show a simple loading indicator (optional)
        mapElement.style.cursor = 'wait';
        // You could add a more sophisticated loading overlay here

        const cql2JsonFilter = {
            "op": "in",
            "args": [
                { "property": idPropertyNameInFeatures }, // Use the configured ID property name for the filter
                featureIdsToFetch // These are the IDs from Livewire
            ]
        };

        const queryParams = 'f=json&filter-lang=cql-json';
        console.log("Requesting features for IDs:", featureIdsToFetch);
        console.log("Sending CQL2-JSON filter:", JSON.stringify(cql2JsonFilter, null, 2));

        try {
            const response = await fetch(`${itemsUrl}?${queryParams}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/query-cql-json',
                    'Accept': 'application/geo+json'
                },
                body: JSON.stringify(cql2JsonFilter)
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! Status: ${response.status}, Message: ${errorText}`);
            }

            const featureCollection = await response.json();

            if (!featureCollection || featureCollection.type !== 'FeatureCollection') {
                console.warn("Response is not a valid GeoJSON FeatureCollection:", featureCollection);
                alert("Did not receive a valid FeatureCollection from the server.");
                return;
            }

            if (!featureCollection.features || featureCollection.features.length === 0) {
                console.warn("No features returned in the FeatureCollection.");
                // alert(`No features found for the selected IDs using property '${idPropertyNameInFeatures}'.`);
                // No alert here, as an empty valid collection is possible if selected IDs have no geometry
                // or don't match any features in pygeoapi.
            } else {
                console.log(`Received ${featureCollection.features.length} features.`);
            }


            // Add the new features to the map
            geoJsonLayer = L.geoJSON(featureCollection, {
                onEachFeature: function (feature, layer) {
                    let popupContent = `<h4>Feature Details</h4>`;
                    if (feature.properties) {
                        popupContent += '<ul>';
                        for (const key in feature.properties) {
                            if (Object.prototype.hasOwnProperty.call(feature.properties, key)) {
                                popupContent += `<li><strong>${key}:</strong> ${feature.properties[key]}</li>`;
                            }
                        }
                        popupContent += '</ul>';
                    } else {
                        popupContent += "<p>No properties found for this feature.</p>";
                    }
                    layer.bindPopup(popupContent);
                },
                style: function (feature) { // Optional: default styling
                    return { color: "#3388ff", weight: 3, opacity: 0.7 };
                }
            }).addTo(map);

            // Zoom the map to the bounds of all fetched features
            if (featureCollection.features && featureCollection.features.length > 0 && geoJsonLayer.getBounds().isValid()) {
                map.fitBounds(geoJsonLayer.getBounds());
            } else if (featureCollection.features && featureCollection.features.length > 0) {
                // If bounds are not valid (e.g. single point), try to set view based on first feature.
                // This is a simplified fallback; more robust centering might be needed.
                const firstFeatCoords = featureCollection.features[0]?.geometry?.coordinates;
                if (firstFeatCoords) {
                    if (featureCollection.features[0].geometry.type === 'Point') {
                         map.setView([firstFeatCoords[1], firstFeatCoords[0]], 15); // Lat, Lng for Point
                    } else {
                        // For Polygons/MultiPolygons, this is more complex. fitBounds is preferred.
                        // As a last resort, set to initial center or a wider view.
                        map.setView(initialMapCenter, 13);
                    }
                }
            } else {
                 map.setView(initialMapCenter, 10); // No features, zoom out
            }

        } catch (error) {
            console.error('Error fetching or displaying GeoJSON features from pygeoapi:', error);
            alert('Could not load features on the map: ' + error.message);
            // Optionally display error on map div
            // mapElement.innerHTML = `<p style="color: red; text-align: center; padding: 20px;">Failed to load features: ${error.message}</p>`;
        } finally {
            mapElement.style.cursor = ''; // Reset cursor
            // Hide loading overlay here
        }
    }

    // Listen for the Livewire event dispatching selected field IDs
    window.addEventListener('selectedFieldIdsUpdated', event => {
        if (event.detail && Array.isArray(event.detail.selectedIds)) {
            console.log('Livewire event: selectedFieldIdsUpdated received', event.detail.selectedIds);
            fetchAndRenderFields(event.detail.selectedIds);
        } else {
            console.warn('selectedFieldIdsUpdated event received, but "selectedIds" array is missing or invalid in event.detail.');
            fetchAndRenderFields([]); // Clear map or show default state
        }
    });

    // Handle map resize
    const resizeObserver = new ResizeObserver(() => {
        map.invalidateSize();
    });
    if (mapElement) {
        resizeObserver.observe(mapElement);
    }

    // Initial data load (if any IDs are dispatched from mount())
    // The 'selectedFieldIdsUpdated' event dispatched from mount() will trigger the initial load.
    // If you have initialGeoJson in data attributes and want to load that *before* any event:
    // const initialGeoJsonData = mapElement.dataset.initialGeojson ? JSON.parse(mapElement.dataset.initialGeojson) : null;
    // if (initialGeoJsonData && initialGeoJsonData.features && initialGeoJsonData.features.length > 0) {
    //    renderFeatures(initialGeoJsonData); // A simpler renderFeatures without fetching might be needed for this.
    // } else if (mapElement.dataset.initialSelectedIds) { // Or pass initial IDs this way
    //    fetchAndRenderFields(JSON.parse(mapElement.dataset.initialSelectedIds));
    // }
});
