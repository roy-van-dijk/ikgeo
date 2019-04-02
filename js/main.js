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

id("clustering").addEventListener("change", function() {
    console.log("e");
    map.getSource("parking_spots").cluster = false;
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

function id(id) {
    return document.getElementById(id);
}
function el(el) {
    return document.querySelector(el);
}
function els(els) {
    return document.querySelectorAll(els);
}