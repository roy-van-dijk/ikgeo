id("hamburger").addEventListener("click", function() {
    this.classList.toggle("open");
    id("menu").classList.toggle("open");
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