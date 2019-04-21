<?php require_once("header.php") ?>

    <div id="map">
        
    </div>

    <div class="legend">
        <table>
            <tr>
                <td><div class="legend-location"></div></td>
                <td>Current location</td>
            </tr>
            <tr>
                <td><div class="legend-route"></div></td>
                <td>Route</td>
            </tr>
            <tr>
                <td><div class="legend-10"></div></td>
                <td>10+ parking garages</td>
            </tr>
            <tr>
                <td><div class="legend-5"></div></td>
                <td>5+ parking garages</td>
            </tr>
            <tr>
                <td><div class="legend-2"></div></td>
                <td>2+ parking garages</td>
            </tr>
            <tr>
                <td><div class="legend-parking-garage"></div></td>
                <td>Parking garage</td>
            </tr>
        </table>
    </div>

    <button id="legend-button">Show legend</button>

    <div id="popup">
        <h2 class="name"></h2>
        <div class="free-space-short"></div>
        <button class="directions" id="directions">Directions</button>
        <div id="close-popup">&#10005;</div>
    </div>

    <script>
        const token = 'pk.eyJ1Ijoicm95ZXZhbmRpamsiLCJhIjoiY2p0NXJnbTRpMDh0ZTN6cnVyd24xaTdlbCJ9.kCp52B18Bm8EonaSgytxPQ';
        mapboxgl.accessToken = token;
        var scale = new mapboxgl.ScaleControl();
        var hidden = 10000;
        var clusterRadius = 40;
        var clusterMaxZoom = 20;
        var clustering = true;
        var mapStyle = 'light-v9';
        var start = [0, 0];
        var overlap = false;
        const data = cleanupJSON(JSON.parse(<?php echo getJSON("http://opd.it-t.nl/data/amsterdam/ParkingLocation.json") ?>));
        var map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/' + mapStyle,
            center: [4.9036, 52.3680],
            zoom: 7,
        });

        navigator.geolocation.getCurrentPosition(function(location) {
            localStorage.lat = location.coords.latitude;
            localStorage.long = location.coords.longitude;
            start = [localStorage.long, localStorage.lat];
        });
        
        map.on('load', function () {
            loadMap();
        });

        function loadMap() {

            map.flyTo({
                center: [4.9036, 52.3680],
                zoom: 10
            });

            map.addSource("parking_spots", {
                type: "geojson",
                data: data,
                cluster: clustering,
                clusterMaxZoom: clusterMaxZoom,
                clusterRadius: clusterRadius,
                clusterProperties: {
                    sum: ["+", ["get", "FreeSpaceShort"], ["get", "FreeSpaceShort"]]
                }
            });

            map.loadImage('assets/pin.png', function(error, image) {
                if (error) throw error;
                map.addImage('pin-active', image);
            });
            
            getRoute(start);

            // Current location point
            map.addLayer({
                id: 'point',
                type: 'circle',
                source: {
                    type: 'geojson',
                    data: {
                        type: 'FeatureCollection',
                        features: [{
                            type: 'Feature',
                            properties: {},
                            geometry: {
                                type: 'Point',
                                coordinates: start
                            }
                        }]
                    }
                },
                paint: {
                    'circle-radius': 5,
                    'circle-color': '#f3f3f3',
                    'circle-stroke-color': '#e50011',
                    'circle-stroke-width': 3
                }
            });

            // Cluster layer
            map.addLayer({
                id: "clusters",
                type: "circle",
                source: "parking_spots",
                filter: ["has", "point_count"],
                paint: {
                    "circle-opacity": 0.7,
                    "circle-color": [
                        "step",
                        ["get", "point_count"],
                        "#51bbd6",
                        5,
                        "#f1f075",
                        10,
                        "#f28cb1"
                    ],
                    "circle-radius": [
                        "step",
                        ["get", "point_count"],
                        20,
                        5,
                        30,
                        10,
                        40
                    ]
                }
            });
                
            // Cluster count layer
            map.addLayer({
                id: "cluster-count",
                type: "symbol",
                source: "parking_spots",
                filter: ["has", "point_count"],
                layout: {
                    "text-field": ["get", "sum"],
                    "text-font": ["Arial Unicode MS Bold"],
                    "text-size": 16,
                    "text-offset": [0, 0]
                    // "text-allow-overlap" : true
                }
            });
                
            // Unclustered layer
            map.addLayer({
                id: "unclustered-point",
                type: "symbol",
                source: "parking_spots",
                filter: ["!", ["has", "point_count"]],
                layout: {
                    "icon-image": "pin-active",
                    "icon-size": 0.12,
                    "icon-offset": [0, -250],
                    "text-field": "{FreeSpaceShort}",
                    "text-font": ["Roboto Black"],
                    "text-size": 14,
                    "text-offset": [0, -2.6],
                    "text-optional": true,
                    "text-allow-overlap" : overlap,
                    "icon-allow-overlap" : true
                },
                paint: {
                    "text-color": "#221915"
                }
            }); 

            map.on('click', 'clusters', function (e) {
                var features = map.queryRenderedFeatures(e.point, { layers: ['clusters'] });
                var clusterId = features[0].properties.cluster_id;
                map.getSource('parking_spots').getClusterExpansionZoom(clusterId, function (err, zoom) {
                    if (err) return;
                    
                    map.easeTo({
                        center: features[0].geometry.coordinates,
                        zoom: zoom
                    });
                });
            });

            map.on('click', 'unclustered-point', function (e) {
                let popup = id("popup");
                var coordinates = e.features[0].geometry.coordinates.slice();
                var name = e.features[0].properties.Name;
                var freeSpaceShort = e.features[0].properties.FreeSpaceShort;
                var spaceSelector = popup.querySelector(".free-space-short");
                localStorage.destLat = coordinates[1];
                localStorage.destLong = coordinates[0];
                
                popup.classList.add("open");
                popup.querySelector(".name").innerText = name;
                
                spaceSelector.classList.remove("free");
                spaceSelector.classList.remove("full");
                spaceSelector.innerText = "";

                if (freeSpaceShort === 0) {
                    spaceSelector.classList.add("full");
                    spaceSelector.innerText = "Full -\xa0";
                } else {
                    spaceSelector.classList.add("free");
                    spaceSelector.innerText = "Free -\xa0";
                }

                spaceSelector.innerText += freeSpaceShort + "\xa0spaces";
            });

            map.on('mouseenter', 'clusters', function () { map.getCanvas().style.cursor = 'pointer'; });
            map.on('mouseleave', 'clusters', function () { map.getCanvas().style.cursor = ''; });
            map.on('mouseenter', 'unclustered-point', function () { map.getCanvas().style.cursor = 'pointer'; });
            map.on('mouseleave', 'unclustered-point', function () { map.getCanvas().style.cursor = ''; });

            map.addControl(scale, 'bottom-right');
        }

        function cleanupJSON(json) {
            for (let i = 0; i < json.features.length; i++) {
                let current = json.features[i].properties;
                current.FreeSpaceShort = parseInt(current.FreeSpaceShort) || 0;
                current.ShortCapacity = parseInt(current.ShortCapacity) || 0;
                current.FreeSpaceLong = parseInt(current.FreeSpaceLong) || 0;
                current.LongCapacity = parseInt(current.LongCapacity) || 0;
                current.Name = current.Name.match(/[^\d]*$/)[0];

                if (current.Name.toLowerCase().includes("nognietoperationeel")) {
                    json.features[i].geometry.coordinates[0] = 4.887118;
                    json.features[i].geometry.coordinates[1] = 52.355649;
                }
                // current.Name = current.Name.replace(/([A-Z])/g, ' $1').trim();
            }
            return json;
        }

        function getRoute(end) {
            var lat = localStorage.lat;
            var long = localStorage.long;
            var url = 'https://api.mapbox.com/directions/v5/mapbox/driving/'+long+','+lat+';'+end[0]+','+end[1]+'?overview=full&geometries=geojson&access_token='+token;
            var req = new XMLHttpRequest();
            req.responseType = 'json';
            req.open('GET', url, true);
            req.onload = function() {
                var data = req.response.routes[0];
                var route = data.geometry.coordinates;
                var geojson = {
                    type: 'Feature',
                    properties: {},
                    geometry: {
                        type: 'LineString',
                        coordinates: route
                    }
                };

                if (map.getSource('route')) {
                    map.getSource('route').setData(geojson);
                } else {
                    map.addLayer({
                        id: 'route',
                        type: 'line',
                        source: {
                            type: 'geojson',
                            data: {
                                type: 'Feature',
                                properties: {},
                                geometry: {
                                    type: 'LineString',
                                    coordinates: geojson
                                }
                            }
                        },
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': '#e50011',
                            'line-width': 5,
                            'line-opacity': 0.75
                        }
                    });
                }
            };
            req.send();
        }

        function resetMap() {
            applyFilters();
            clearMap();
            loadMap();
        }

        function clearMap() {
            map.removeControl(scale);
            map.removeLayer("clusters");
            map.removeLayer("cluster-count");
            map.removeLayer("unclustered-point");
            map.removeLayer("point");
            map.removeSource("point");
            map.removeImage("pin-active");
            map.removeSource("parking_spots");
        }

        function applyFilters() {
            let filterValue = parseInt(id("spot-filter").value);

            if (filterValue == 10000) {
                map.setFilter('unclustered-point', ["!", ["has", "point_count"]]);
                map.getSource('parking_spots').setData(data);
            } else {
                map.setFilter('unclustered-point', [">", "FreeSpaceShort", filterValue]);
                map.getSource('parking_spots').setData(getFilteredData(filterValue));
            }
        }

        function getFilteredData(value) {
            let newData = JSON.parse(JSON.stringify(data));
            
            for (let i = 0; i < newData.features.length; i++) {
                if (newData.features[i].properties.FreeSpaceShort < value) {
                    newData.features.splice(i, 1);
                }
            }
            return newData;
        }

        function id(id) {
            return document.getElementById(id);
        }

        function el(el) {
            return document.querySelector(el);
        }

    </script>

<?php require_once("footer.php"); ?>