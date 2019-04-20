id("hamburger").addEventListener("click", function() {
    this.classList.toggle("open");
    id("menu").classList.toggle("open");
});

id("directions").addEventListener("click", function() {
    getRoute([localStorage.destLong, localStorage.destLat]);
});

id("close-popup").addEventListener("click", function() {
    id("popup").classList.remove("open");
});

id("map-style").addEventListener("change", function() {
    mapStyle = this.value;
    clearMap();
    map = null;
    map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/' + mapStyle,
        center: [4.9036, 52.3680],
        zoom: 7,
    });
    map.on('load', function () {
        loadMap();
        applyFilters();
    });
});

id("spot-filter").addEventListener("change", function() {
    applyFilters();
});

id("clustering").addEventListener("change", function() {
    if (this.value === "true") {
        enableClustering();
        
    } else {
        disableClustering();
    }
    resetFilterOptions();
});

id("overlap").addEventListener("change", function() {
    if (this.value === "true") {
        overlap = true;
        resetMap();
        
    } else {
        overlap = false;
        resetMap();
    }
    resetFilterOptions();
});

id("clear-directions").addEventListener("click", function() {
    start = [localStorage.long, localStorage.lat];
    getRoute(start);
});

id("legend-button").addEventListener("click", function() {
    el(".legend").classList.toggle("open");
    this.classList.toggle("open");
    if (this.innerText === "Show legend") {
        this.innerText = "Hide legend";
    } else {
        this.innerText = "Show legend";
    }
});

function resetFilterOptions() {
    var select = id("spot-filter");
    var options = select.options;
    for (var option, j = 0; option = options[j]; j++) {
        if (option.value == 10000) {
            select.selectedIndex = j;
            break;
        }
    }
}
function enableClustering() {
    clusterRadius = 40;
    clustering = true;
    resetMap();
    map.setLayoutProperty("clusters", 'visibility', "visible");
    map.setLayoutProperty("cluster-count", 'visibility', "visible");
}
function disableClustering() {
    clusterRadius = 10;
    clustering = false;
    resetMap();
    map.setLayoutProperty("clusters", 'visibility', "none");
    map.setLayoutProperty("cluster-count", 'visibility', "none");
}

function id(id) {
    return document.getElementById(id);
}
function el(el) {
    return document.querySelector(el);
}
function els(els) {
    return document.querySelectorAll(els);
}