<?php require_once("header.php") ?>

    <div id="map">
        
    </div>

    <div id="popup">
        <h2 class="name"></h2>
        <div class="free-space-short"></div>
        <button class="directions" id="directions">Directions</button>
        <div id="close-popup">&#10005;</div>
    </div>

    <script>
        navigator.geolocation.getCurrentPosition(function(location) {
            localStorage.lat = location.coords.latitude;
            localStorage.long = location.coords.longitude;
        });
        
		const token = 'pk.eyJ1Ijoicm95ZXZhbmRpamsiLCJhIjoiY2p0NXJnbTRpMDh0ZTN6cnVyd24xaTdlbCJ9.kCp52B18Bm8EonaSgytxPQ';
        mapboxgl.accessToken = token;
        var map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/light-v9',
            center: [4.9036, 52.3680],
            zoom: 10
        });

        map.on('load', function () {

            map.addSource("parking_spots", {
                type: "geojson",
                data: cleanupJSON(JSON.parse(<?php echo getJSON("http://opd.it-t.nl/data/amsterdam/ParkingLocation.json") ?>)),
                cluster: true,
                clusterMaxZoom: 20,
                clusterRadius: 25 // Radius of each cluster when clustering points (defaults to 50)
            });
            
            var start = [localStorage.long, localStorage.lat];

            // make an initial directions request that
            // starts and ends at the same location
            getRoute(start);

            // Add starting point to the map
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
                    'circle-color': '#e50011'
                }
            });

            map.addLayer({
                id: "clusters",
                type: "circle",
                source: "parking_spots",
                filter: ["has", "point_count"],
                paint: {
                    // Use step expressions (https://docs.mapbox.com/mapbox-gl-js/style-spec/#expressions-step)
                    // with three steps to implement three types of circles:
                    //   * Blue, 20px circles when point count is less than 100
                    //   * Yellow, 30px circles when point count is between 100 and 750
                    //   * Pink, 40px circles when point count is greater than or equal to 750
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
                
            map.addLayer({
                id: "cluster-count",
                type: "symbol",
                source: "parking_spots",
                filter: ["has", "point_count"],
                layout: {
                    "text-field": "{point_count_abbreviated}",
                    "text-font": ["Arial Unicode MS Bold"],
                    "text-size": 16,
                    "text-offset": [0, 0]
                    // "text-allow-overlap" : true
                }
            });
                
            map.addLayer({
                id: "unclustered-point",
                type: "circle",
                source: "parking_spots",
                filter: ["!", ["has", "point_count"]],
                paint: {
                    "circle-color": "#f3f3f3",
                    "circle-radius": 6,
                    "circle-stroke-width": 1,
                    "circle-stroke-color": "#e50011"
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

        function cleanupJSON(json) {
            for (let i = 0; i < json.features.length; i++) {
                let current = json.features[i].properties;
                current.FreeSpaceShort = parseInt(current.FreeSpaceShort) || 0;
                current.ShortCapacity = parseInt(current.ShortCapacity) || 0;
                current.FreeSpaceLong = parseInt(current.FreeSpaceLong) || 0;
                current.LongCapacity = parseInt(current.LongCapacity) || 0;
                current.Name = current.Name.match(/[^\d]*$/)[0];
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
            } else { // otherwise, make a new request
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


        function id(id) {
            return document.getElementById(id);
        }

        function el(el) {
            return document.querySelector(el);
        }

    </script>

<?php require_once("footer.php"); ?>