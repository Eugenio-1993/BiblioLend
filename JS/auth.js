// ============================================================
// auth.js — Sistema de Autenticación y Protección por Roles
// BiblioLend · CodeForge — 2026
// ============================================================

// --- BASE DE USUARIOS (simulada) ---
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
    "cuenta_usuario.html",
    "panel_bibliotecario.html",
  ],
  administrador: [
    "panel_principal.html",
    "sobre_nosotros.html",
    "contactanos.html",
    "resenas.html",
    "catalogo.html",
    "cuenta_usuario.html",
    "panel_admin.html",
  ],
};

// --- PÁGINA DE DESTINO SEGÚN ROL ---
const paginaInicial = {
  estudiante: "./paginas/panel_principal.html",
  bibliotecario: "./paginas/panel_principal.html",
  administrador: "./paginas/panel_principal.html",
};

// ============================================================
// LÓGICA DEL FORMULARIO DE LOGIN
// ============================================================
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

// ============================================================
// MOSTRAR USUARIO EN EL SALUDO
// ============================================================
// Busca el primer elemento con [data-saludo] y reemplaza su texto.
// También oculta elementos que requieran un rol diferente al actual.

function mostrarUsuario() {
  const email = localStorage.getItem("sesion_email");
  const rol = localStorage.getItem("sesion_rol");

  // Actualizar el saludo dinámico
  const saludo = document.querySelector("[data-saludo]");
  if (saludo && email) {
    saludo.textContent = `Bienvenido de vuelta, ${email}`;
  }

  // Actualizar datos del perfil en cuenta_usuario.html
  const userEmail = document.querySelector("[data-user-email]");
  if (userEmail && email) {
    userEmail.textContent = email;
  }

  const userRole = document.querySelector("[data-user-role]");
  if (userRole && rol) {
    // Capitalizar primera letra del rol
    userRole.textContent =
      rol.charAt(0).toUpperCase() + rol.slice(1);
  }

  // Mostrar/ocultar elementos según el rol
  // Ejemplo: <button data-rol-requerido="administrador">Admin</button>
  document.querySelectorAll("[data-rol-requerido]").forEach((elemento) => {
    const rolRequerido = elemento.getAttribute("data-rol-requerido");
    if (rol !== rolRequerido) {
      elemento.style.display = "none";
    }
  });
}
