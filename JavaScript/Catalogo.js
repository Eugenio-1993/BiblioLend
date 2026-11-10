// ============================================================
// Catalogo.js — Detalle de libros y solicitudes de préstamo
// BiblioLend · 2026
// ============================================================

(function () {
  "use strict";

  const modalElement = document.getElementById("modalDetalleLibro");

  if (!modalElement || typeof bootstrap === "undefined") {
    return;
  }

  const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

  // Las solicitudes se guardan en el navegador para que se conserven
  // al cerrar la ventana o recargar la página. Si hay una sesión activa,
  // cada usuario mantiene sus propias solicitudes.
  function obtenerClaveSolicitudes() {
    try {
      const usuario = localStorage.getItem("sesion_email");
      return usuario
        ? "bibliolend_solicitudes:" + usuario
        : "bibliolend_solicitudes";
    } catch (error) {
      return "bibliolend_solicitudes";
    }
  }

  const STORAGE_KEY = obtenerClaveSolicitudes();
  let solicitudesMemoria = null;

  const elementos = {
    portada: document.getElementById("detallePortada"),
    imagen: document.getElementById("detalleImagen"),
    iniciales: document.getElementById("detalleIniciales"),
    titulo: document.getElementById("detalleTitulo"),
    autor: document.getElementById("detalleAutor"),
    categoria: document.getElementById("detalleCategoria"),
    estado: document.getElementById("detalleEstado"),
    anio: document.getElementById("detalleAnio"),
    paginas: document.getElementById("detallePaginas"),
    isbn: document.getElementById("detalleIsbn"),
    ubicacion: document.getElementById("detalleUbicacion"),
    descripcion: document.getElementById("detalleDescripcion"),
    aviso: document.getElementById("detalleAviso"),
    accion: document.getElementById("detalleAccion"),
  };

  let libroActual = null;

  function leerSolicitudes() {
    if (solicitudesMemoria !== null) {
      return solicitudesMemoria;
    }

    try {
      const solicitudes = JSON.parse(localStorage.getItem(STORAGE_KEY));
      return Array.isArray(solicitudes) ? solicitudes : [];
    } catch (error) {
      return [];
    }
  }

  function guardarSolicitudes(solicitudes) {
    // La copia en memoria permite que el botón funcione durante la sesión
    // actual aunque el navegador bloquee localStorage.
    solicitudesMemoria = solicitudes.slice();

    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(solicitudes));
    } catch (error) {
      // Se conserva únicamente la copia en memoria.
    }
  }

  function tieneSolicitud(id) {
    return leerSolicitudes().includes(id);
  }

  function generarId(titulo) {
    return titulo
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/(^-|-$)/g, "");
  }

  function obtenerIniciales(titulo) {
    const omitidas = new Set(["de", "del", "la", "las", "el", "los", "y", "con"]);
    const palabras = titulo
      .split(/\s+/)
      .filter(function (palabra) {
        return palabra && !omitidas.has(palabra.toLowerCase());
      });

    return palabras
      .slice(0, 2)
      .map(function (palabra) {
        return palabra.charAt(0);
      })
      .join("")
      .toUpperCase();
  }

  function obtenerDatos(card) {
    const estadoElemento = card.querySelector(".estado");
    const imagen = card.querySelector(".libro-img img");
    const portadaElemento = card.querySelector(".libro-img");
    const tituloElemento = card.querySelector("h3");
    const titulo = tituloElemento ? tituloElemento.textContent.trim() : "Libro";

    const portadaClase = portadaElemento
      ? Array.from(portadaElemento.classList).find(function (clase) {
          return clase.indexOf("portada-") === 0;
        })
      : "";

    return {
      id: card.dataset.libroId || generarId(titulo),
      titulo: titulo,
      autor: card.querySelector(".autor")
        ? card.querySelector(".autor").textContent.trim()
        : "Autor desconocido",
      categoria: card.querySelector(".categoria")
        ? card.querySelector(".categoria").textContent.trim()
        : "Sin categoría",
      disponible: estadoElemento
        ? estadoElemento.classList.contains("disponible")
        : true,
      imagen: imagen ? imagen.getAttribute("src") : "",
      imagenAlt: imagen ? imagen.getAttribute("alt") : "",
      portadaClase: portadaClase,
      descripcion: card.dataset.descripcion || "Sin descripción disponible.",
      anio: card.dataset.anio || "—",
      paginas: card.dataset.paginas || "—",
      isbn: card.dataset.isbn || "—",
      ubicacion: card.dataset.ubicacion || "—",
    };
  }

  function mostrarAviso(texto, tipo) {
    elementos.aviso.classList.remove(
      "detalle-libro__aviso--ok",
      "detalle-libro__aviso--info"
    );

    if (!texto) {
      elementos.aviso.textContent = "";
      elementos.aviso.hidden = true;
      return;
    }

    elementos.aviso.textContent = texto;
    elementos.aviso.classList.add("detalle-libro__aviso--" + tipo);
    elementos.aviso.hidden = false;
  }

  function actualizarEstado() {
    const solicitado = tieneSolicitud(libroActual.id);

    elementos.estado.classList.remove(
      "disponible",
      "prestado",
      "pendiente",
      "retraso"
    );

    if (libroActual.disponible && solicitado) {
      elementos.estado.classList.add("pendiente");
      elementos.estado.textContent = "Solicitud enviada";
    } else if (libroActual.disponible) {
      elementos.estado.classList.add("disponible");
      elementos.estado.textContent = "Disponible";
    } else {
      elementos.estado.classList.add("prestado");
      elementos.estado.textContent = "Prestado";
    }
  }

  function actualizarAccion() {
    const solicitado = tieneSolicitud(libroActual.id);

    elementos.accion.className = "btn detalle-libro__accion";
    elementos.accion.disabled = false;
    elementos.accion.removeAttribute("title");

    if (solicitado) {
      elementos.accion.classList.add("detalle-libro__accion--cancelar");
      elementos.accion.textContent = "Cancelar solicitud";
      elementos.accion.setAttribute(
        "aria-label",
        "Cancelar la solicitud de préstamo de " + libroActual.titulo
      );
      return;
    }

    if (libroActual.disponible) {
      elementos.accion.classList.add("btn-success");
      elementos.accion.textContent = "Pedir prestado";
      elementos.accion.setAttribute(
        "aria-label",
        "Pedir prestado " + libroActual.titulo
      );
      return;
    }

    elementos.accion.classList.add("detalle-libro__accion--agotado");
    elementos.accion.textContent = "No disponible";
    elementos.accion.disabled = true;
    elementos.accion.setAttribute(
      "title",
      "Este libro está prestado actualmente."
    );
  }

  function pintarPortada(libro) {
    elementos.portada.className = "detalle-libro__portada";
    elementos.iniciales.textContent = "";

    if (libro.imagen) {
      elementos.imagen.src = libro.imagen;
      elementos.imagen.alt = libro.imagenAlt || "Portada de " + libro.titulo;
      elementos.imagen.hidden = false;
      return;
    }

    elementos.imagen.removeAttribute("src");
    elementos.imagen.alt = "";
    elementos.imagen.hidden = true;
    elementos.portada.classList.add("detalle-libro__portada--vacia");

    if (libro.portadaClase) {
      elementos.portada.classList.add(libro.portadaClase);
    }

    elementos.iniciales.textContent = obtenerIniciales(libro.titulo);
  }

  function mostrarLibro(libro) {
    elementos.titulo.textContent = libro.titulo;
    elementos.autor.textContent = libro.autor;
    elementos.categoria.textContent = libro.categoria;
    elementos.anio.textContent = libro.anio;
    elementos.paginas.textContent = libro.paginas;
    elementos.isbn.textContent = libro.isbn;
    elementos.ubicacion.textContent = libro.ubicacion;
    elementos.descripcion.textContent = libro.descripcion;

    pintarPortada(libro);
    actualizarEstado();
    actualizarAccion();

    if (tieneSolicitud(libro.id)) {
      mostrarAviso(
        "Solicitud registrada. Puedes cancelarla cuando quieras desde este mismo botón.",
        "info"
      );
    } else if (!libro.disponible) {
      mostrarAviso(
        "Este libro está prestado actualmente. Vuelve a consultar su disponibilidad más adelante.",
        "info"
      );
    } else {
      mostrarAviso("", "");
    }
  }

  function abrirDetalle(card) {
    libroActual = obtenerDatos(card);
    mostrarLibro(libroActual);
    modal.show();
  }

  document.querySelectorAll(".btn-ver-detalle").forEach(function (boton) {
    boton.addEventListener("click", function () {
      const card = boton.closest(".libro-card");

      if (card) {
        abrirDetalle(card);
      }
    });
  });

  elementos.accion.addEventListener("click", function () {
    if (!libroActual) {
      return;
    }

    const solicitado = tieneSolicitud(libroActual.id);
    let solicitudes = leerSolicitudes();

    if (solicitado) {
      solicitudes = solicitudes.filter(function (id) {
        return id !== libroActual.id;
      });
      guardarSolicitudes(solicitudes);
      actualizarEstado();
      actualizarAccion();
      mostrarAviso(
        "Solicitud cancelada. El libro vuelve a estar disponible.",
        "info"
      );
      return;
    }

    if (!libroActual.disponible) {
      return;
    }

    if (!solicitudes.includes(libroActual.id)) {
      solicitudes.push(libroActual.id);
      guardarSolicitudes(solicitudes);
    }

    actualizarEstado();
    actualizarAccion();
    mostrarAviso(
      "Solicitud registrada. Acércate a la biblioteca para confirmar el préstamo.",
      "ok"
    );
  });

  modalElement.addEventListener("hidden.bs.modal", function () {
    libroActual = null;
  });
})();

// ============================================================
// Filtro de categorías — desplegable personalizado
// ============================================================

(function () {
  "use strict";

  const contenedor = document.querySelector("[data-filtro-categoria]");

  if (!contenedor) {
    return;
  }

  const boton = contenedor.querySelector(".filtro-categoria__trigger");
  const lista = contenedor.querySelector(".filtro-categoria__lista");
  const valor = contenedor.querySelector(".filtro-categoria__valor");
  const campo = contenedor.querySelector("[data-filtro-categoria-campo]");
  const opciones = lista
    ? Array.from(lista.querySelectorAll('[role="option"]'))
    : [];

  if (!boton || !lista || !valor || !opciones.length) {
    return;
  }

  let indiceSeleccionado = opciones.findIndex(function (opcion) {
    return opcion.getAttribute("aria-selected") === "true";
  });

  if (indiceSeleccionado < 0) {
    indiceSeleccionado = 0;
  }

  let indiceActivo = indiceSeleccionado;

  function marcarActivo(indice) {
    indiceActivo = (indice + opciones.length) % opciones.length;

    opciones.forEach(function (opcion, posicion) {
      opcion.classList.toggle("is-active", posicion === indiceActivo);
    });
  }

  function enfocarOpcion(indice) {
    marcarActivo(indice);
    opciones[indiceActivo].focus({ preventScroll: true });
    opciones[indiceActivo].scrollIntoView({ block: "nearest" });
  }

  function ajustarAlturaLista() {
    const margenInferior = 16;
    const separacion = 7;
    const alturaMinima = 200;

    function calcularEspacio() {
      return (
        window.innerHeight -
        boton.getBoundingClientRect().bottom -
        separacion -
        margenInferior
      );
    }

    let espacioDisponible = calcularEspacio();

    // Si el botón queda cerca del borde inferior, se desplaza la página
    // lo justo para que la lista quepa completa debajo de él.
    if (espacioDisponible < alturaMinima) {
      window.scrollBy({
        top: alturaMinima - espacioDisponible,
        left: 0,
        behavior: "instant",
      });
      espacioDisponible = calcularEspacio();
    }

    // La lista siempre se abre hacia abajo y nunca supera el espacio
    // visible, ni siquiera en ventanas muy bajas.
    const altura = Math.min(420, Math.max(0, espacioDisponible));

    lista.style.maxHeight = altura + "px";
  }

  function abrir() {
    ajustarAlturaLista();
    lista.hidden = false;
    boton.setAttribute("aria-expanded", "true");
    contenedor.classList.add("is-open");
    enfocarOpcion(indiceSeleccionado);
  }

  function cerrar(devolverFoco) {
    lista.hidden = true;
    boton.setAttribute("aria-expanded", "false");
    contenedor.classList.remove("is-open");

    if (devolverFoco) {
      boton.focus();
    }
  }

  function seleccionar(indice) {
    const opcion = opciones[indice];
    const texto = opcion.textContent.trim();

    indiceSeleccionado = indice;

    opciones.forEach(function (elemento, posicion) {
      elemento.setAttribute(
        "aria-selected",
        String(posicion === indiceSeleccionado)
      );
    });

    valor.textContent = texto;
    boton.dataset.valor = texto;

    if (campo) {
      campo.value = texto;
    }

    cerrar(true);
  }

  boton.addEventListener("click", function () {
    if (lista.hidden) {
      abrir();
    } else {
      cerrar(true);
    }
  });

  boton.addEventListener("keydown", function (event) {
    if (event.key === "ArrowDown" || event.key === "ArrowUp") {
      event.preventDefault();
      abrir();
    }
  });

  opciones.forEach(function (opcion, indice) {
    // Se usa mousemove en lugar de mouseenter para que reaparecer bajo el
    // cursor no altere la opción activa durante la navegación con teclado.
    opcion.addEventListener("mousemove", function () {
      marcarActivo(indice);
    });

    opcion.addEventListener("click", function () {
      seleccionar(indice);
    });
  });

  lista.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      event.preventDefault();
      cerrar(true);
      return;
    }

    if (event.key === "ArrowDown") {
      event.preventDefault();
      enfocarOpcion(indiceActivo + 1);
      return;
    }

    if (event.key === "ArrowUp") {
      event.preventDefault();
      enfocarOpcion(indiceActivo - 1);
      return;
    }

    if (event.key === "Home") {
      event.preventDefault();
      enfocarOpcion(0);
      return;
    }

    if (event.key === "End") {
      event.preventDefault();
      enfocarOpcion(opciones.length - 1);
      return;
    }

    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();
      seleccionar(indiceActivo);
      return;
    }

    if (event.key === "Tab") {
      cerrar(false);
      boton.focus();
    }
  });

  document.addEventListener("click", function (event) {
    if (!contenedor.contains(event.target)) {
      cerrar(false);
    }
  });

  window.addEventListener("resize", function () {
    if (!lista.hidden) {
      ajustarAlturaLista();
    }
  });
})();
