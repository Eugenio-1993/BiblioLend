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
