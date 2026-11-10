// ============================================================
// Cuenta_usuarios.js — Fechas y días restantes de los préstamos
// BiblioLend · 2026
// ============================================================

(function () {
  "use strict";

  const prestamos = Array.from(
    document.querySelectorAll(
      ".recommend-item[data-inicio][data-devolucion]"
    )
  );

  if (!prestamos.length) {
    return;
  }

  const formateadorFecha = new Intl.DateTimeFormat("es-CO", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });

  const MILISEGUNDOS_POR_DIA = 24 * 60 * 60 * 1000;

  function crearFecha(valor) {
    if (!valor) {
      return null;
    }

    const partes = /^(\d{4})-(\d{2})-(\d{2})$/.exec(valor.trim());

    if (!partes) {
      return null;
    }

    const anio = Number(partes[1]);
    const mes = Number(partes[2]) - 1;
    const dia = Number(partes[3]);
    const fecha = new Date(anio, mes, dia);

    if (
      Number.isNaN(fecha.getTime()) ||
      fecha.getFullYear() !== anio ||
      fecha.getMonth() !== mes ||
      fecha.getDate() !== dia
    ) {
      return null;
    }

    return fecha;
  }

  // Se comparan fechas de calendario en UTC para evitar desfases
  // por zonas horarias o cambios de horario.
  function fechaEnUTC(fecha) {
    return Date.UTC(
      fecha.getFullYear(),
      fecha.getMonth(),
      fecha.getDate()
    );
  }

  function diasDeDiferencia(desde, hasta) {
    return Math.round(
      (fechaEnUTC(hasta) - fechaEnUTC(desde)) / MILISEGUNDOS_POR_DIA
    );
  }

  function etiquetaDias(dias) {
    if (dias > 1) {
      return dias + " días";
    }

    if (dias === 1) {
      return "1 día";
    }

    if (dias === 0) {
      return "Vence hoy";
    }

    if (dias === -1) {
      return "Vencido hace 1 día";
    }

    return "Vencido hace " + Math.abs(dias) + " días";
  }

  function detalleDias(dias) {
    if (dias > 1) {
      return "Te quedan " + dias + " días";
    }

    if (dias === 1) {
      return "Te queda 1 día";
    }

    if (dias === 0) {
      return "Debes entregarlo hoy";
    }

    if (dias === -1) {
      return "Vencido hace 1 día";
    }

    return "Vencido hace " + Math.abs(dias) + " días";
  }

  function aplicarEstado(elemento, dias) {
    if (!elemento) {
      return;
    }

    elemento.classList.remove(
      "dias-restantes--urgente",
      "dias-restantes--vencido"
    );

    if (dias < 0) {
      elemento.classList.add("dias-restantes--vencido");
    } else if (dias <= 2) {
      elemento.classList.add("dias-restantes--urgente");
    }
  }

  function actualizarPrestamo(item, hoy) {
    const inicio = crearFecha(item.dataset.inicio);
    const devolucion = crearFecha(item.dataset.devolucion);
    const badge = item.querySelector("[data-dias-restantes]");
    const fechaInicio = item.querySelector("[data-fecha-inicio]");
    const fechaDevolucion = item.querySelector("[data-fecha-devolucion]");
    const diasDetalle = item.querySelector("[data-dias-detalle]");

    if (!inicio || !devolucion) {
      if (badge) {
        badge.textContent = "Sin fecha";
        badge.title = "El préstamo no tiene fechas registradas.";
      }
      return null;
    }

    const dias = diasDeDiferencia(hoy, devolucion);

    if (badge) {
      badge.textContent = etiquetaDias(dias);
      badge.title =
        dias < 0
          ? "El préstamo está vencido"
          : "Días restantes para devolver el libro";
      aplicarEstado(badge, dias);
    }

    if (fechaInicio) {
      fechaInicio.textContent = formateadorFecha.format(inicio);
    }

    if (fechaDevolucion) {
      fechaDevolucion.textContent = formateadorFecha.format(devolucion);
    }

    if (diasDetalle) {
      diasDetalle.textContent = detalleDias(dias);
      aplicarEstado(diasDetalle, dias);
    }

    return dias;
  }

  function activarDesplegable(item) {
    const boton = item.querySelector(".recommend-item__toggle");
    const detalle = item.querySelector(".recommend-detail");

    if (!boton || !detalle) {
      return;
    }

    boton.addEventListener("click", function () {
      const abierto = boton.getAttribute("aria-expanded") === "true";

      boton.setAttribute("aria-expanded", String(!abierto));
      detalle.hidden = abierto;
      item.classList.toggle("is-open", !abierto);
    });
  }

  const hoy = new Date();
  let vencidos = 0;

  prestamos.forEach(function (item) {
    const dias = actualizarPrestamo(item, hoy);

    if (dias !== null && dias < 0) {
      vencidos += 1;
    }

    activarDesplegable(item);
  });

  const statActivos = document.querySelector(
    '[data-stat="prestamos-activos"]'
  );
  const statVencidos = document.querySelector(
    '[data-stat="libros-vencidos"]'
  );

  if (statActivos) {
    statActivos.textContent = String(prestamos.length);
  }

  if (statVencidos) {
    statVencidos.textContent = String(vencidos);
  }
})();
