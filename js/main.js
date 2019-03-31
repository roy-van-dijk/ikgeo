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


function id(id) {
    return document.getElementById(id);
}
function el(el) {
    return document.querySelector(el)[0];
}
function els(els) {
    return document.querySelectorAll(els);
}