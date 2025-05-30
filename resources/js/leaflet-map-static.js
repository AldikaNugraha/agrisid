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
    let initialLat = -6.595038; // Example: Bogor
    let initialLng = 106.816635;
    let initialZoom = 10;

    var map = L.map('map').setView([initialLat, initialLng], initialZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // var lc = L.control.locate().addTo(map);
    // lc.start();

    // Check if there is GeoJSON data to display
    if (fieldsGeoJsonData && fieldsGeoJsonData.features && fieldsGeoJsonData.features.length > 0) {
        var geoJsonLayer = L.geoJSON(fieldsGeoJsonData, {
            style: function (feature) {
                // Define a style for your polygons
                return {
                    color: "#007bff",       // Border color (e.g., blue)
                    weight: 2,              // Border weight
                    opacity: 0.8,           // Border opacity
                    fillColor: "#007bff",    // Fill color
                    fillOpacity: 0.2        // Fill opacity
                };
            },
            onEachFeature: function (feature, layer) {
                // Bind a popup to each feature
                if (feature.properties) {
                    let popupContent = '';
                    if (feature.properties.field_name) {
                        popupContent += `<strong>Nama Lahan:</strong> ${feature.properties.field_name}<br>`;
                    }
                    if (feature.properties.field_luas) {
                        popupContent += `<strong>Luas:</strong> ${feature.properties.field_luas} Ha`;
                    }
                    // Add more properties as needed
                    if (popupContent) {
                        layer.bindPopup(popupContent);
                    }
                }
            }
        }).addTo(map);

        // Fit the map bounds to the GeoJSON layer
        map.fitBounds(geoJsonLayer.getBounds());
    } else {
        console.log('No field boundaries found for this village or data is empty.');
        // You could add a message to the user on the map here if no data
        // L.marker([initialLat, initialLng]).addTo(map)
        //  .bindPopup(`No field boundaries found for village ID: ${village_id}. Showing default location.`)
        //  .openPopup();
    }
});
