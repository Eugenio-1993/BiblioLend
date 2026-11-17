// ============================================================
// Olvide_mi_contrasena.js — Validación y confirmación visual
// BiblioLend · 2026
// ============================================================

(function () {
  "use strict";

  const formulario = document.getElementById("recuperarContrasena");

  if (!formulario) {
    return;
  }

  const email = document.getElementById("email");
  const error = document.getElementById("email-error");
  const confirmacion = document.getElementById("confirmacion");
  const correoEnviado = document.getElementById("correoEnviado");
  const btnOtroCorreo = document.getElementById("btnOtroCorreo");
  const tituloConfirmacion = document.getElementById("titulo-confirmacion");

  function mostrarError(mensaje) {
    if (!error) {
      return;
    }

    error.textContent = mensaje;
    error.hidden = false;

    if (email) {
      email.setAttribute("aria-invalid", "true");
    }
  }

  function limpiarError() {
    if (!error) {
      return;
    }

    error.textContent = "";
    error.hidden = true;

    if (email) {
      email.removeAttribute("aria-invalid");
    }
  }

  if (email) {
    email.addEventListener("input", limpiarError);
  }

  formulario.addEventListener("submit", function (event) {
    event.preventDefault();

    const valor = email ? email.value.trim() : "";

    if (valor === "") {
      mostrarError("Escribe tu correo institucional.");
      if (email) {
        email.focus();
      }
      return;
    }

    if (email && !email.checkValidity()) {
      mostrarError("Ingresa un correo institucional válido.");
      email.focus();
      return;
    }

    limpiarError();

    if (correoEnviado) {
      correoEnviado.textContent = valor;
    }

    formulario.hidden = true;

    if (confirmacion) {
      confirmacion.hidden = false;
    }

    if (tituloConfirmacion) {
      tituloConfirmacion.focus();
    }
  });

  if (btnOtroCorreo) {
    btnOtroCorreo.addEventListener("click", function () {
      if (confirmacion) {
        confirmacion.hidden = true;
      }

      formulario.hidden = false;
      limpiarError();

      if (email) {
        email.value = "";
        email.focus();
      }
    });
  }
})();
