<?php require_once("header.php") ?>

    <div id="map">
        
    </div>

    <script>
		
        let accessToken = 'pk.eyJ1Ijoicm95ZXZhbmRpamsiLCJhIjoiY2p0NXJnbTRpMDh0ZTN6cnVyd24xaTdlbCJ9.kCp52B18Bm8EonaSgytxPQ';

        var mymap = L.map('map').setView([52.3680, 4.9036], 12);

        let data = cleanupJSON(JSON.parse(<?php echo getJSON("http://opd.it-t.nl/data/amsterdam/ParkingLocation.json") ?>));

        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
            attribution: '',
            maxZoom: 18,
            id: 'mapbox.streets',
            accessToken: 'pk.eyJ1Ijoicm95ZXZhbmRpamsiLCJhIjoiY2p0NXJnbTRpMDh0ZTN6cnVyd24xaTdlbCJ9.kCp52B18Bm8EonaSgytxPQ'
        }).addTo(mymap);

        L.geoJSON(data).addTo(mymap);
        var markers = L.markerClusterGroup();

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