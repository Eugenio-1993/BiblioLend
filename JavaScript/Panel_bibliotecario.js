// ============================================================
// Panel_bibliotecario.js — Alta y edición de libros
// BiblioLend · 2026
// ============================================================

(function () {
  "use strict";

  const modalElement = document.getElementById("modalLibro");

  if (!modalElement || typeof bootstrap === "undefined") {
    return;
  }

  const formulario = document.getElementById("formLibro");
  const campoAccion = document.getElementById("libroAccion");
  const campoId = document.getElementById("libroId");
  const tituloModal = document.getElementById("tituloModalLibro");
  const botonGuardar = modalElement.querySelector("[data-libro-submit]");
  const campoTitulo = document.getElementById("libroTitulo");
  const campoAutor = document.getElementById("libroAutor");
  const campoCategoria = document.getElementById("libroCategoria");
  const campoCopias = document.getElementById("libroCopias");
  const campoDescripcion = document.getElementById("libroDescripcion");
  const campoPortada = document.getElementById("libroPortada");
  const pistaCopias = modalElement.querySelector("[data-libro-copias-hint]");
  const contenedorPortada = modalElement.querySelector(
    "[data-libro-portada-preview]"
  );
  const imagenPortada = modalElement.querySelector(
    "[data-libro-portada-imagen]"
  );
  const textoPortadaVacia = modalElement.querySelector(
    "[data-libro-portada-vacia]"
  );

  if (
    !formulario ||
    !campoAccion ||
    !campoId ||
    !tituloModal ||
    !botonGuardar ||
    !campoTitulo ||
    !campoAutor ||
    !campoCategoria ||
    !campoCopias ||
    !campoDescripcion
  ) {
    return;
  }

  function mostrarPortada(ruta) {
    if (!contenedorPortada || !imagenPortada || !textoPortadaVacia) {
      return;
    }

    if (ruta) {
      imagenPortada.src = "../" + ruta;
      contenedorPortada.hidden = false;
      textoPortadaVacia.hidden = true;
      return;
    }

    imagenPortada.removeAttribute("src");
    contenedorPortada.hidden = true;
    textoPortadaVacia.hidden = false;
  }

  modalElement.addEventListener("show.bs.modal", function (evento) {
    const disparador = evento.relatedTarget;
    const modo =
      disparador && disparador.dataset.modo === "editar" ? "editar" : "crear";

    formulario.reset();

    if (campoPortada) {
      campoPortada.value = "";
    }

    if (modo === "editar") {
      const datos = disparador.dataset;
      const copias = Number(datos.copias || 0);
      const disponibles = Number(datos.disponibles || 0);
      const prestadas = Math.max(0, copias - disponibles);

      campoAccion.value = "editar_libro";
      campoId.value = datos.id || "";
      campoTitulo.value = datos.titulo || "";
      campoAutor.value = datos.autor || "";
      campoCategoria.value = datos.categoria || "";
      campoCopias.value = datos.copias || "1";
      campoDescripcion.value = datos.descripcion || "";
      tituloModal.textContent = "Editar libro";
      botonGuardar.textContent = "Guardar cambios";

      mostrarPortada(datos.portada || "");

      if (pistaCopias) {
        pistaCopias.textContent =
          "Actualmente: " +
          copias +
          " copia(s), " +
          prestadas +
          " prestada(s). No puedes bajar el total por debajo de las prestadas.";
      }

      return;
    }

    campoAccion.value = "crear_libro";
    campoId.value = "";
    tituloModal.textContent = "Agregar libro";
    botonGuardar.textContent = "Agregar libro";
    campoCopias.value = "1";
    mostrarPortada("");

    if (pistaCopias) {
      pistaCopias.textContent = "Se registrarán como copias disponibles.";
    }
  });
})();
