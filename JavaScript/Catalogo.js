/**
 * Catalogo.js
 * Filtros del catálogo y ventana de detalle con solicitud/cancelación de préstamos.
 */
(() => {
  'use strict';

  const modalEl = document.getElementById('modalDetalleLibro');
  if (!modalEl) {
    return;
  }

  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  const tarjetas = Array.from(document.querySelectorAll('.libro-card'));
  const buscador = document.getElementById('buscar-libro');
  const contador = document.getElementById('catalogo-contador');
  const sinResultados = document.getElementById('catalogo-sin-resultados');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  // ------------------------------------------------------------
  // Filtros (texto y categoría)
  // ------------------------------------------------------------
  const filtroWrap = document.querySelector('[data-filtro-categoria]');
  const filtroTrigger = document.getElementById('filtro-categoria');
  const filtroLista = document.getElementById('filtro-categoria-lista');
  const filtroValor = document.getElementById('filtro-categoria-valor');
  const filtroCampo = document.querySelector('[data-filtro-categoria-campo]');
  let categoriaActual = 'Todas las categorías';

  const normalizar = (texto) => String(texto || '')
    .toLocaleLowerCase('es')
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '');

  function aplicarFiltros() {
    const texto = normalizar(buscador?.value.trim() || '');
    let visibles = 0;

    tarjetas.forEach((tarjeta) => {
      const coincideTexto = texto === ''
        || normalizar(tarjeta.dataset.titulo).includes(texto)
        || normalizar(tarjeta.dataset.autor).includes(texto)
        || normalizar(tarjeta.dataset.categoria).includes(texto);

      const coincideCategoria = categoriaActual === 'Todas las categorías'
        || tarjeta.dataset.categoria === categoriaActual;

      const visible = coincideTexto && coincideCategoria;
      tarjeta.hidden = !visible;

      if (visible) {
        visibles++;
      }
    });

    if (contador) {
      contador.textContent = visibles === 1
        ? '1 libro encontrado'
        : `${visibles} libros encontrados`;
    }

    if (sinResultados) {
      sinResultados.hidden = visibles !== 0 || tarjetas.length === 0;
    }
  }

  function abrirFiltro(abrir) {
    if (!filtroLista || !filtroTrigger) {
      return;
    }

    filtroLista.hidden = !abrir;
    filtroTrigger.setAttribute('aria-expanded', abrir ? 'true' : 'false');
  }

  buscador?.addEventListener('input', aplicarFiltros);

  filtroTrigger?.addEventListener('click', () => {
    abrirFiltro(filtroLista.hidden);
  });

  filtroLista?.addEventListener('click', (evento) => {
    const opcion = evento.target.closest('.filtro-categoria__opcion');
    if (!opcion) {
      return;
    }

    categoriaActual = opcion.textContent.trim();
    filtroValor.textContent = categoriaActual;
    filtroTrigger.dataset.valor = categoriaActual;

    filtroLista.querySelectorAll('.filtro-categoria__opcion').forEach((item) => {
      item.setAttribute('aria-selected', item === opcion ? 'true' : 'false');
    });

    if (filtroCampo) {
      filtroCampo.value = categoriaActual;
    }

    abrirFiltro(false);
    aplicarFiltros();
  });

  document.addEventListener('click', (evento) => {
    if (filtroWrap && !filtroWrap.contains(evento.target)) {
      abrirFiltro(false);
    }
  });

  document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape') {
      abrirFiltro(false);
    }
  });

  // ------------------------------------------------------------
  // Ventana de detalle
  // ------------------------------------------------------------
  const detalle = {
    portada: document.getElementById('detallePortada'),
    imagen: document.getElementById('detalleImagen'),
    iniciales: document.getElementById('detalleIniciales'),
    estado: document.getElementById('detalleEstado'),
    titulo: document.getElementById('detalleTitulo'),
    autor: document.getElementById('detalleAutor'),
    categoria: document.getElementById('detalleCategoria'),
    descripcion: document.getElementById('detalleDescripcion'),
    aviso: document.getElementById('detalleAviso'),
    accion: document.getElementById('detalleAccion'),
  };

  let libroActual = null;

  function texto(valor, vacio = '—') {
    const limpio = String(valor || '').trim();
    return limpio === '' ? vacio : limpio;
  }

  function iniciales(titulo) {
    return String(titulo || '?')
      .trim()
      .split(/\s+/)
      .slice(0, 2)
      .map((palabra) => palabra.charAt(0).toUpperCase())
      .join('');
  }

  function mostrarAviso(mensaje, tipo = 'ok') {
    if (!detalle.aviso) {
      return;
    }

    detalle.aviso.textContent = mensaje;
    detalle.aviso.className = 'detalle-libro__aviso '
      + (tipo === 'error' ? 'detalle-libro__aviso--error' : 'detalle-libro__aviso--ok');
    detalle.aviso.hidden = false;
  }

  function ocultarAviso() {
    if (detalle.aviso) {
      detalle.aviso.hidden = true;
      detalle.aviso.textContent = '';
    }
  }

  function etiquetaVisible(tarjeta) {
    const estado = tarjeta.dataset.miEstado || '';
    const disponibles = Number(tarjeta.dataset.disponibles || 0);

    if (estado === 'solicitado') {
      return { texto: 'Solicitud enviada', clase: 'pendiente' };
    }
    if (estado === 'activo') {
      return { texto: 'En tu préstamo', clase: 'activo' };
    }
    if (estado === 'devolucion_solicitada') {
      return { texto: 'Devolución solicitada', clase: 'pendiente' };
    }
    if (disponibles > 0) {
      return { texto: 'Disponible', clase: 'disponible' };
    }

    return { texto: 'Prestado', clase: 'prestado' };
  }

  function actualizarTarjeta(tarjeta) {
    const etiqueta = etiquetaVisible(tarjeta);
    const badge = tarjeta.querySelector('[data-estado-libro]');

    if (badge) {
      badge.className = 'estado ' + etiqueta.clase;
      badge.textContent = etiqueta.texto;
    }
  }

  function configurarBotonAccion() {
    const boton = detalle.accion;
    if (!boton || !libroActual) {
      return;
    }

    const estado = libroActual.dataset.miEstado || '';
    const disponibles = Number(libroActual.dataset.disponibles || 0);
    const puedeSolicitar = libroActual.dataset.puedeSolicitar === '1';

    boton.disabled = false;
    boton.dataset.accion = '';
    boton.className = 'btn btn-success detalle-libro__accion';

    if (estado === 'solicitado') {
      boton.textContent = 'Cancelar solicitud';
      boton.dataset.accion = 'cancelar';
      boton.className = 'btn detalle-libro__accion detalle-libro__accion--cancelar';
      return;
    }

    if (estado === 'activo') {
      boton.textContent = 'Ya está en tu préstamo';
      boton.disabled = true;
      boton.className = 'btn detalle-libro__accion detalle-libro__accion--agotado';
      return;
    }

    if (estado === 'devolucion_solicitada') {
      boton.textContent = 'Devolución en revisión';
      boton.disabled = true;
      boton.className = 'btn detalle-libro__accion detalle-libro__accion--agotado';
      return;
    }

    if (!puedeSolicitar) {
      boton.textContent = 'Solo estudiantes';
      boton.disabled = true;
      boton.className = 'btn detalle-libro__accion detalle-libro__accion--agotado';
      return;
    }

    if (disponibles <= 0) {
      boton.textContent = 'Sin copias disponibles';
      boton.disabled = true;
      boton.className = 'btn detalle-libro__accion detalle-libro__accion--agotado';
      return;
    }

    boton.textContent = 'Pedir prestado';
    boton.dataset.accion = 'solicitar';
  }

  function abrirDetalle(tarjeta) {
    libroActual = tarjeta;
    ocultarAviso();

    detalle.titulo.textContent = texto(tarjeta.dataset.titulo, 'Sin título');
    detalle.autor.textContent = texto(tarjeta.dataset.autor, 'Autor por confirmar');
    detalle.categoria.textContent = texto(tarjeta.dataset.categoria, 'Sin categoría');
    detalle.descripcion.textContent = texto(tarjeta.dataset.descripcion, 'Sin descripción disponible.');

    const etiqueta = etiquetaVisible(tarjeta);
    detalle.estado.className = 'estado ' + etiqueta.clase;
    detalle.estado.textContent = etiqueta.texto;

    const imagenTarjeta = tarjeta.querySelector('.libro-img img');
    if (imagenTarjeta) {
      detalle.imagen.src = imagenTarjeta.src;
      detalle.imagen.alt = imagenTarjeta.alt;
      detalle.imagen.hidden = false;
      detalle.iniciales.textContent = '';
      detalle.portada?.classList.remove('detalle-libro__portada--vacia');
    } else {
      detalle.imagen.hidden = true;
      detalle.imagen.removeAttribute('src');
      detalle.imagen.alt = '';
      detalle.iniciales.textContent = iniciales(tarjeta.dataset.titulo);
      detalle.portada?.classList.add('detalle-libro__portada--vacia');
    }

    configurarBotonAccion();
    modal.show();
  }

  tarjetas.forEach((tarjeta) => {
    tarjeta.querySelector('.btn-ver-detalle')?.addEventListener('click', () => abrirDetalle(tarjeta));
    actualizarTarjeta(tarjeta);
  });

  detalle.accion?.addEventListener('click', async () => {
    if (!libroActual || !detalle.accion.dataset.accion) {
      return;
    }

    const accion = detalle.accion.dataset.accion;

    detalle.accion.disabled = true;
    detalle.accion.textContent = 'Procesando...';
    ocultarAviso();

    try {
      const cuerpo = new URLSearchParams({
        accion: accion === 'solicitar' ? 'solicitar_prestamo' : 'cancelar_solicitud',
        id_libro: libroActual.dataset.libroId,
        csrf_token: csrf,
        volver: 'Catalogo.php',
      });

      const respuesta = await fetch('../acciones.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        body: cuerpo,
      });

      const datos = await respuesta.json();

      if (!datos.ok) {
        mostrarAviso(datos.mensaje || 'No se pudo completar la acción.', 'error');
        return;
      }

      libroActual.dataset.miEstado = accion === 'solicitar' ? 'solicitado' : '';
      actualizarTarjeta(libroActual);

      const etiqueta = etiquetaVisible(libroActual);
      detalle.estado.className = 'estado ' + etiqueta.clase;
      detalle.estado.textContent = etiqueta.texto;

      mostrarAviso(datos.mensaje || 'Acción realizada.');
      configurarBotonAccion();
    } catch (error) {
      mostrarAviso('No se pudo conectar con el servidor. Inténtalo de nuevo.', 'error');
    } finally {
      detalle.accion.disabled = false;
      configurarBotonAccion();
    }
  });

  aplicarFiltros();
})();
