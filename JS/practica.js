// ============================================================
// practica.js — Login con roles y protección de sesión
// ============================================================

// --- BASE DE USUARIOS ---
const usuarios = [
  { email: "estudiante@institucion.edu.co", password: "123est",   rol: "estudiante"    },
  { email: "biblio@institucion.edu.co",     password: "123bib",   rol: "bibliotecario" },
  { email: "admin@institucion.edu.co",      password: "123adm",   rol: "administrador" }
];

// --- PÁGINAS PERMITIDAS POR ROL ---
// panel_principal.html es el "hub" de todos los roles: debe estar en los tres.
// Cada rol hereda las páginas del anterior más las suyas exclusivas.
const paginasPermitidas = {
  estudiante: [
    "panel_principal.html",
    "catalogo.html",
    "resenas.html",
    "contactanos.html",
    "sobre_nosotros.html",
    "cuenta_usuario.html"
  ],
  bibliotecario: [
    "panel_principal.html",   // ← necesario para no quedar atrapado en el login
    "catalogo.html",
    "resenas.html",
    "contactanos.html",
    "sobre_nosotros.html",
    "cuenta_usuario.html",
    "panel_bibliotecario.html" // exclusiva del bibliotecario
  ],
  administrador: [
    "panel_principal.html",   // ← ídem
    "catalogo.html",
    "resenas.html",
    "contactanos.html",
    "sobre_nosotros.html",
    "cuenta_usuario.html",
    "panel_bibliotecario.html",
    "panel_admin.html"         // exclusiva del administrador
  ]
};

// --- PÁGINA DE DESTINO SEGÚN ROL ---
// Después del login, cada rol llega a su propio panel.
// Todos van a panel_principal.html por ahora; puedes cambiarlos cuando
// tengas los paneles de bibliotecario y admin listos.
const paginaInicial = {
  estudiante:    "./paginas/panel_principal.html",
  bibliotecario: "./paginas/panel_principal.html", // cambiar a panel_bibliotecario.html cuando exista
  administrador: "./paginas/panel_principal.html"  // cambiar a panel_admin.html cuando exista
};

// ============================================================
// LÓGICA DEL FORMULARIO DE LOGIN
// ============================================================
const formulario = document.getElementById("iniciarSesion");

if (formulario) {
  formulario.addEventListener("submit", function (event) {
    event.preventDefault();

    const emailIngresado    = document.getElementById("email").value.trim();
    const passwordIngresada = document.getElementById("password").value;
    const rolSeleccionado   = document.getElementById("rol").value;

    const usuarioEncontrado = usuarios.find(
      u => u.email    === emailIngresado &&
            u.password === passwordIngresada &&
            u.rol      === rolSeleccionado
    );

    if (usuarioEncontrado) {
      localStorage.setItem("sesion_email", usuarioEncontrado.email);
      localStorage.setItem("sesion_rol",   usuarioEncontrado.rol);

      // Redirigimos según el rol del usuario que inició sesión
      window.location.href = paginaInicial[usuarioEncontrado.rol];
    } else {
      alert("Correo, contraseña o rol incorrectos. Verifica tus datos.");
    }
  });
}

// ============================================================
// PROTEGER PÁGINA
// ============================================================
// Uso: protegerPagina("nombre-del-archivo.html")
// Coloca este llamado en un <script> al final de cada página privada.

function protegerPagina(nombrePagina) {
  const rol = localStorage.getItem("sesion_rol");

  // Sin sesión → al login
  if (!rol) {
    alert("Debes iniciar sesión para acceder a esta sección.");
    window.location.href = "../index.html";
    return;
  }

  // Con sesión pero sin permiso para ESTA página → al panel principal
  const permitidas = paginasPermitidas[rol] || [];
  if (!permitidas.includes(nombrePagina)) {
    alert(`Tu rol (${rol}) no tiene acceso a esta sección.`);
    window.location.href = "../paginas/panel_principal.html";
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

// ============================================================
// MOSTRAR USUARIO EN EL SALUDO
// ============================================================
// Busca el primer <h2> que contenga "[USUARIO]" y lo reemplaza.
// Así no rompe otros <h2> de la página.

function mostrarUsuario() {
  const email = localStorage.getItem("sesion_email");
  const rol   = localStorage.getItem("sesion_rol");

  // Buscamos el h2 específico del saludo, no cualquier h2 de la página
  const saludo = document.querySelector("h2[data-saludo]");

  if (saludo && email) {
    saludo.textContent = `Bienvenido de vuelta, ${email}`;
  }

  // Opcional: mostrar/ocultar elementos según el rol
  // Por ejemplo, un botón que solo ve el administrador
  document.querySelectorAll("[data-rol-requerido]").forEach(elemento => {
    const rolRequerido = elemento.getAttribute("data-rol-requerido");
    if (rol !== rolRequerido) {
      elemento.style.display = "none"; // Oculta el elemento si el rol no coincide
    }
  });
}