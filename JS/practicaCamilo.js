console.log('hola');
const user = 
[
{
    "email": "admin@admin.com",
    "password": "admin123",
    "user": "administrador" 
},
 {
    "email": "admin@estu.com",
    "password": "estu123",
    "user": "estudiante" 
}
,
 {
    "email": "admin@biblio.com",
    "password": "biblio123",
    "user": "bibliotecario" 
}

]

    

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
//clase 3
//  opcion 1
// function iniciarSesion(email, password) { 
//     for (let index = 0; index < user.length; index++) {
//         const element = user[index];
//         if (element.email === email && element.passwors === password) {
//             alert("usuario encontrado");
//             sessionStorage.setItem("user", element.user);
//             break;
//         }
//         alert("usuario no encontrado");

//         console.log("index: ",index);
//         console.log("elemento: ",element.email);
//         console.log("elemento: ",element.passwors);
//         console.log("elemento: ",element.user);


//     }}


}); 




