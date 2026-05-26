console.log('hola');
const user = {
    "email": "admin@admin.com",
    "password": "admin123",
    "user": "administrador"

    
}
const formulario = document.getElementById('iniciarSesion');

formulario.addEventListener("submit",function (event) {
    event.preventDefault();
    let email = document.getElementById('email').value;
    let password = document.getElementById('password').value;
    if (user.email === email && user.password === password) {
        sessionStorage.setItem("user", user.user);
        window.location.href = "./paginas/panel_principal.html";
        return alert("usuario encontrado")
        
    
    } 
    return alert("usuario no encontrado")
    localStorage.setItem("sesion_email", email);
    localStorage.setItem("sesion_password", password);
}); 




