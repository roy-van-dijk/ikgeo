<?php require_once("header.php") ?>

    <div id="map">
        
    </div>

    <script>
		
        mapboxgl.accessToken = 'pk.eyJ1Ijoicm95ZXZhbmRpamsiLCJhIjoiY2p0NXJnbTRpMDh0ZTN6cnVyd24xaTdlbCJ9.kCp52B18Bm8EonaSgytxPQ';
        var map = new mapboxgl.Map({
            container: 'map',
            zoom: 10,
            center: [4.9036, 52.3680],
            style: 'mapbox://styles/mapbox/light-v9',
        });

		var settings = {
            "id": "points",
            "type": "symbol",
            "source": {
                "type": "geojson",
                "data": JSON.parse(<?php echo getJSON("http://opd.it-t.nl/data/amsterdam/ParkingLocation.json") ?>)
            },
            "layout": {
                "icon-image": "{icon}-15",
                "text-field": "{FreeSpaceShort}",
                "text-font": [
                    "Open Sans Semibold",
                    "Arial Unicode MS Bold"
                ],
                "text-offset": [0, 0.6],
                "text-anchor": "top"
            } 
		};

        map.on('load', function () {
            map.addLayer(settings);
		});

    </script>

<?php require_once("footer.php"); ?>