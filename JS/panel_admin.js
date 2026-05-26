document.addEventListener("DOMContentLoaded",function(){
sessionStorage.getItem("user");
})
if (user !== "admin") {
    window.location.href = "../cuenta_usuario.html"; //mandar al inicio de sesion
} 