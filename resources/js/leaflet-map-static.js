import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

document.addEventListener('DOMContentLoaded', function () {
    let initialLat = -6.595038; // Example: Bogor
    let initialLng = 106.816635;
    let initialZoom = 10;

    var map = L.map('map').setView([initialLat, initialLng], initialZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);


    // 3. Define the API endpoint for the GeoJSON feature
    const collectionId = 'fields-postgis'; // Your collection ID
    const featureId = fieldId;
    const pygeoapiBaseUrl = 'http://localhost:8050'; // Your pygeoapi base URL
    const geoJsonFeatureUrl = `${pygeoapiBaseUrl}/collections/${collectionId}/items/${featureId}?f=json`;

    const cog_name = cogName;
    const mywms = L.tileLayer.wms("http://localhost:8090/geoserver/WMS/wms", {
        layers: `WMS:${cog_name}`,
        format: 'image/png',
        transparent: true,
        version: '1.1.0',
        maxZoom: 30,
    });
    mywms.addTo(map);

    fetch(geoJsonFeatureUrl
        // If your server requires an Accept header for non-explicit ?f=json:
        // { headers: { 'Accept': 'application/geo+json' } }
    )
    .then(async response => {
        if (!response.ok) {
            // If the response is not ok, throw an error to be caught by .catch()
            const text = await response.text();
            throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
        }
        return response.json(); // Parse the response body as JSON
    })
    .then(geoJsonFeatureData => {
        // 5. Create a Leaflet GeoJSON layer
        // geoJsonFeatureData should be a single GeoJSON Feature object
        const featureLayer = L.geoJSON(geoJsonFeatureData, {
            onEachFeature: function (feature, layer) {
                // You can customize what happens for each feature, e.g., add a popup
                let popupContent = '<h4>Feature Details</h4>';
                if (feature.properties) {
                    popupContent += '<ul>';
                    for (const key in feature.properties) {
                        if (feature.properties.hasOwnProperty(key)) {
                            popupContent += `<li><strong>${key}:</strong> ${feature.properties[key]}</li>`;
                        }
                    }
                    popupContent += '</ul>';
                }
                // pygeoapi often includes the feature ID in the 'id' field or within properties
                const id = feature.id || (feature.properties ? feature.properties.id : 'N/A');
                layer.bindPopup(`<b>Feature ID:</b> ${id}<br>${popupContent}`);
            },
            style: function (feature) {
                // Optionally, style your feature
                return {
                    color: "#ff7800",
                    weight: 5,
                    opacity: 0.65
                };
            }
        });

        // 6. Add the layer to the map
        featureLayer.addTo(map);

        // 7. Zoom the map to the bounds of the feature
        if (featureLayer.getBounds().isValid()) {
            map.fitBounds(featureLayer.getBounds());
        } else {
            // Fallback for point geometries or if bounds are not valid
            if (geoJsonFeatureData.geometry && geoJsonFeatureData.geometry.type === 'Point' &&
                geoJsonFeatureData.geometry.coordinates) {
                map.setView([geoJsonFeatureData.geometry.coordinates[1], geoJsonFeatureData.geometry.coordinates[0]], 15); // Zoom to point
            } else {
                console.warn("Could not determine valid bounds to fit the map to the feature.");
            }
        }
    })
    .catch(error => {
        console.error('Error fetching or displaying GeoJSON feature:', error);
        // Display an error message to the user on the page or in a modal
        const mapDiv = document.getElementById('map');
        mapDiv.innerHTML = `<p style="color: red; text-align: center; padding: 20px;">Could not load feature: ${error.message}</p>`;
    });
});
