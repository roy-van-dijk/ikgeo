id("hamburger").addEventListener("click", function() {
    this.classList.toggle("open");
    id("menu").classList.toggle("open");
});

id("directions").addEventListener("click", function() {
    navigator.geolocation.getCurrentPosition(function(location) {
        localStorage.lat = location.coords.latitude;
        localStorage.long = location.coords.longitude;
    });
    if (localStorage.getItem("lat") == null) {
        alert("Could not get location. Please ensure your browser has permission to access your location.")
    } else {
        direct();
    }
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