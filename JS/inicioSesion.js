const user = {
    email: "estudiante@institucion.edu.co",
    password: "123est",
    user: "estudiante",
};

const formulario = document.getElementById("iniciarSesion");

formulario.addEventListener("submit", function (event) {
  event.preventDefault();

  let email = document.getElementById("email").value;
  let password = document.getElementById("password").value;

  if (user.email === email && user.password === password) {
    sessionStorage.setItem("user", user.user);
    window.location.href = "../paginas/panel_principal.html";
    return
  } else {
    alert("Usuario NO encontrado");
  }
});
