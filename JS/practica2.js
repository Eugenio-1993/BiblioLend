console.log("hola");

const user = {
    "email": "admin@admin.com",
    "password": "admin123"
};

const formulario =document.getElementById("iniciarsesion");


formulario.addEventListener("submit", function (event) {
    event.preventDefault();
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;
    if (user.email === email && user.password) {
        window.location.href="./anadirplato.html"
        return
    }
    formulario.reset();
    return alert("usuario no encontrado")
})