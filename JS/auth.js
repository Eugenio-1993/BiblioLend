// ============================================================
// auth.js — Sistema de Autenticación y Protección por Roles
// BiblioLend · CodeForge — 2026
// ============================================================

// --- BASE DE USUARIOS ---
const usuarios = [
  {
    email: "estudiante@ierepublicadehonduras.edu.co",
    password: "123est",
    rol: "estudiante",
  },
  {
    email: "biblio@ierepublicadehonduras.edu.co",
    password: "123bib",
    rol: "bibliotecario",
  },
  {
    email: "admin@ierepublicadehonduras.edu.co",
    password: "123adm",
    rol: "administrador",
  },
];

// --- PÁGINAS PERMITIDAS POR ROL ---
const paginasPermitidas = {
  estudiante: [
    "panel_principal.html",
    "sobre_nosotros.html",
    "contactanos.html",
    "resenas.html",
    "catalogo.html",
    "cuenta_usuario.html",
  ],
  bibliotecario: [
    "panel_principal.html",
    "sobre_nosotros.html",
    "contactanos.html",
    "resenas.html",
    "catalogo.html",
    "panel_bibliotecario.html",
  ],
  administrador: [
    "panel_principal.html",
    "sobre_nosotros.html",
    "contactanos.html",
    "resenas.html",
    "catalogo.html",
    "panel_admin.html",
  ],
};

// --- PÁGINA DE DESTINO SEGÚN ROL ---
const paginaInicial = {
  estudiante: "../paginas/panel_principal.html",
  bibliotecario: "../paginas/panel_bibliotecario.html",
  administrador: "../paginas/panel_admin.html",
};

// LOGIN
const formulario = document.getElementById("iniciarSesion");

if (formulario) {
  formulario.addEventListener("submit", function (event) {
    event.preventDefault();

    const emailIngresado = document.getElementById("email").value.trim();
    const passwordIngresada = document.getElementById("password").value;
    const rolSeleccionado = document.getElementById("rol").value;

    // Buscar coincidencia exacta de email + password + rol
    const usuarioEncontrado = usuarios.find(
      (u) =>
        u.email === emailIngresado &&
        u.password === passwordIngresada &&
        u.rol === rolSeleccionado
    );

    if (usuarioEncontrado) {
      // Guardar sesión en localStorage
      localStorage.setItem("sesion_email", usuarioEncontrado.email);
      localStorage.setItem("sesion_rol", usuarioEncontrado.rol);

      // Redirigir según el rol
      window.location.href = paginaInicial[usuarioEncontrado.rol];
    } else {
      alert("Correo, contraseña o rol incorrectos. Verifica tus datos.");
    }
  });
}

// PROTEGER PÁGINA
// Usen: protegerPagina("nombre-del-archivo.html")

function protegerPagina(nombrePagina) {
  const rol = localStorage.getItem("sesion_rol");

  // Sin sesión → al login
  if (!rol) {
    alert("Debes iniciar sesión para acceder a esta sección.");
    window.location.href = "../index.html";
    return;
  }

  // ...
  const permitidas = paginasPermitidas[rol] || [];
  if (!permitidas.includes(nombrePagina)) {
    alert(`Tu rol (${rol}) no tiene acceso a esta sección.`);
    window.location.href = "panel_principal.html";
  }
}

// ============================================================
// CERRAR SESIÓN
// ============================================================
function cerrarSesion() {
  localStorage.removeItem("sesion_email");
  localStorage.removeItem("sesion_rol");
  window.location.href = "../index.html";
}
