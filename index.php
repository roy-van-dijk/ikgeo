<?php require_once("header.php") ?>

    <div id="map">
        
    </div>

    <script>
		
        mapboxgl.accessToken = 'pk.eyJ1Ijoicm95ZXZhbmRpamsiLCJhIjoiY2p0NXJnbTRpMDh0ZTN6cnVyd24xaTdlbCJ9.kCp52B18Bm8EonaSgytxPQ';
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
                        100,
                        "#f1f075",
                        750,
                        "#f28cb1"
                    ],
                    "circle-radius": [
                        "step",
                        ["get", "point_count"],
                        20,
                        100,
                        30,
                        750,
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
                    "text-field": "{point_count}",
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
                    "circle-color": "#11b4da",
                    "circle-radius": 4,
                    "circle-stroke-width": 1,
                    "circle-stroke-color": "#fff"
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

        map.on('mouseenter', 'clusters', function () {
            map.getCanvas().style.cursor = 'pointer';
        });
        map.on('mouseleave', 'clusters', function () {
            map.getCanvas().style.cursor = '';
        });

        function cleanupJSON(json) {
            for (let i = 0; i < json.features.length; i++) {
                let current = json.features[i].properties;
                current.FreeSpaceShort = parseInt(current.FreeSpaceShort) || 0;
                current.ShortCapacity = parseInt(current.ShortCapacity) || 0;
                current.FreeSpaceLong = parseInt(current.FreeSpaceLong) || 0;
                current.LongCapacity = parseInt(current.LongCapacity) || 0;
            }
            return json;
        }

    </script>

<?php require_once("footer.php"); ?>