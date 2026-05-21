document.addEventListener("DOMContentLoaded", function () {
    sessionStorage.getItem("user");
    if (user !== "administrador") {
        window.location.href = "../index.html"
    }
})