/**
 * Olvide_mi_contrasena.js
 * Muestra la confirmación del formulario de recuperación de contraseña.
 *
 * Nota: el envío real del correo lo debe hacer el servidor cuando exista
 * el endpoint de recuperación. Aquí solo se valida el correo y se cambia
 * al estado de confirmación que ya trae la página.
 */
(() => {
  'use strict';

  const formulario = document.getElementById('recuperarContrasena');
  const correo = document.getElementById('email');
  const error = document.getElementById('email-error');
  const confirmacion = document.getElementById('confirmacion');
  const correoEnviado = document.getElementById('correoEnviado');
  const btnOtroCorreo = document.getElementById('btnOtroCorreo');
  const tituloConfirmacion = document.getElementById('titulo-confirmacion');

  if (!formulario || !correo || !confirmacion) {
    return;
  }

  function mostrarError(mensaje) {
    if (!error) {
      return;
    }

    error.textContent = mensaje;
    error.hidden = false;
    correo.setAttribute('aria-invalid', 'true');
  }

  function limpiarError() {
    if (!error) {
      return;
    }

    error.textContent = '';
    error.hidden = true;
    correo.removeAttribute('aria-invalid');
  }

  correo.addEventListener('input', limpiarError);

  formulario.addEventListener('submit', (evento) => {
    evento.preventDefault();
    limpiarError();

    const valor = correo.value.trim();
    if (valor === '' || !correo.checkValidity()) {
      mostrarError('Escribe un correo electrónico válido.');
      correo.focus();
      return;
    }

    if (correoEnviado) {
      correoEnviado.textContent = valor;
    }

    formulario.hidden = true;
    confirmacion.hidden = false;
    tituloConfirmacion?.focus();
  });

  btnOtroCorreo?.addEventListener('click', () => {
    confirmacion.hidden = true;
    formulario.hidden = false;
    formulario.reset();
    limpiarError();
    correo.focus();
  });
})();
