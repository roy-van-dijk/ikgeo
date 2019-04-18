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

id("spot-filter").addEventListener("change", function() {
    hidden = this.value;
    if (hidden == 10000) {
        resetMap();
    } else {
        map.setFilter('unclustered-point', [">", "FreeSpaceShort", parseInt(this.value)]); 
    }
});

id("clustering").addEventListener("change", function() {
    if (this.value === "true") {
        enableClustering();
        
    } else {
        disableClustering();
    }
    
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