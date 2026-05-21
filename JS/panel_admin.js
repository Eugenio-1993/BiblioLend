document.addEventListener("DOMContentLoaded"), function () {
    let user = sessionStorage.getItem("user");
    if (user !== "admin") {
        window.location.href="../index.html"
    }
}