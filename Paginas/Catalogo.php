<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';
requerirRol(['estudiante', 'bibliotecario', 'administrador']);

// Libros desde la base de datos (tabla libro)
$filas = $pdo->query(
    "SELECT id_libro, titulo, autor, categoria, descripcion, portada, disponibles
     FROM libro
     ORDER BY titulo"
)->fetchAll();

// Se adapta cada fila al formato que espera la plantilla de abajo
$libros = [];
foreach ($filas as $fila) {
    $libros[] = [
        'id'          => (string) $fila['id_libro'],
        'titulo'      => $fila['titulo'],
        'autor'       => $fila['autor'],
        'categoria'   => $fila['categoria'],
        'descripcion' => $fila['descripcion'] ?? '',
        // La base guarda "Imagenes/Libros/archivo.jpg" y la plantilla ya agrega "../Imagenes/Libros/"
        'portada'     => basename((string) $fila['portada']),
        'disponibles' => (int) $fila['disponibles'],
    ];
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Catálogo - BiblioLend</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <!-- Tipografías (mismas que la landing) -->
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../Css/BiblioLend.css" />
  <!-- Pie de página compartido -->
  
</head>

<body class="pagina-catalogo" data-usuario-email="<?= htmlspecialchars($_SESSION['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-sobre">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="Panel_principal.php">
          <img class="logoBibliolend" alt="Logo BiblioLend" src="../Imagenes/Logo_bibliolend.png" width="80"
            height="80" />
          <span class="titulo-logo">BiblioLend</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav"
          aria-controls="menuNav" aria-expanded="false" aria-label="Abrir menú">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="Panel_principal.php">Inicio</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Sobre_nosotros.php">Sobre Nosotros</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Contactanos.html">Contáctanos</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="Resenas.php">Reseñas</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="Catalogo.php" aria-current="page">Catálogo</a>
            </li>
            <li class="nav-item">
              <a href="Cuenta_usuarios.php"><img src="../<?= htmlspecialchars(rutaAvatarUsuario(), ENT_QUOTES, 'UTF-8') ?>" alt="Imagen de Perfil"
                  class="navItem--perfil" /></a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <main class="catalogo-page">
    <section class="catalogo-bloque" aria-labelledby="titulo-catalogo">
      <h1 id="titulo-catalogo">Catálogo de Libros</h1>
      <p>Explora los libros disponibles en la biblioteca escolar.</p>

      <div class="filtros">
        <label class="visually-hidden" for="buscar-libro">Buscar por título o autor</label>
        <input id="buscar-libro" type="search" class="form-control" placeholder="Buscar por título o autor" />

        <div class="filtro-categoria" data-filtro-categoria>
          <span class="visually-hidden" id="filtro-categoria-etiqueta">Filtrar por categoría</span>
          <button type="button" class="filtro-categoria__trigger" id="filtro-categoria"
            aria-haspopup="listbox" aria-expanded="false"
            aria-labelledby="filtro-categoria-etiqueta filtro-categoria-valor"
            aria-controls="filtro-categoria-lista" data-valor="Todas las categorías">
            <span class="filtro-categoria__valor" id="filtro-categoria-valor">Todas las categorías</span>
          </button>

          <ul class="filtro-categoria__lista" id="filtro-categoria-lista" role="listbox"
            aria-labelledby="filtro-categoria-etiqueta" tabindex="-1" hidden>
            <li class="filtro-categoria__opcion" role="option" aria-selected="true" tabindex="-1">
              Todas las categorías
            </li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">Novela</li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">
              Literatura infantil
            </li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">Aventura</li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">
              Ciencia ficción
            </li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">Historia</li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">
              Ciencias naturales
            </li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">Filosofía</li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">Geografía</li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">Poesía</li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">Terror</li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">Romance</li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">Biografía</li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">Consulta</li>
            <li class="filtro-categoria__opcion" role="option" aria-selected="false" tabindex="-1">
              Fábulas y leyendas
            </li>
          </ul>

          <input type="hidden" name="categoria" value="Todas las categorías" data-filtro-categoria-campo />
        </div>
      </div>
    </section>

    <section class="catalogo-bloque" aria-labelledby="subtitulo-catalogo">
      <h2 class="subtitulo-catalogo" id="subtitulo-catalogo">Todo lo que hay</h2>
      <p class="text-muted" id="catalogo-contador" role="status" aria-live="polite">
        <?= count($libros) ?> libros disponibles
      </p>
      <div class="libros-grid">
        <?php if ($libros === []): ?>
          <p class="sin-resultados">No hay libros disponibles en este momento.</p>
        <?php endif; ?>

        <?php foreach ($libros as $libro): ?>
          <?php
            $tituloLibro = (string) ($libro['titulo'] ?? '');
            $autorLibro = (string) ($libro['autor'] ?? 'Autor por confirmar');
            $categoriaLibro = (string) ($libro['categoria'] ?? 'Sin categoría');
            $descripcionLibro = (string) ($libro['descripcion'] ?? 'Sin descripción disponible.');
            $portadaLibro = (string) ($libro['portada'] ?? '');
            $idLibro = (string) ($libro['id'] ?? '');
            $anioLibro = (string) ($libro['anio'] ?? '');
            $paginasLibro = (string) ($libro['paginas'] ?? '');
            $isbnLibro = (string) ($libro['isbn'] ?? '');
            $ubicacionLibro = (string) ($libro['ubicacion'] ?? '');
            $disponiblesLibro = (int) ($libro['disponibles'] ?? 0);
            $estaDisponible = $disponiblesLibro > 0;
          ?>
          <article class="libro-card"
            data-libro-id="<?= htmlspecialchars($idLibro, ENT_QUOTES, 'UTF-8') ?>"
            data-categoria="<?= htmlspecialchars($categoriaLibro, ENT_QUOTES, 'UTF-8') ?>"
            data-descripcion="<?= htmlspecialchars($descripcionLibro, ENT_QUOTES, 'UTF-8') ?>"
            <?php if ($anioLibro !== ''): ?>data-anio="<?= htmlspecialchars($anioLibro, ENT_QUOTES, 'UTF-8') ?>"<?php endif; ?>
            <?php if ($paginasLibro !== ''): ?>data-paginas="<?= htmlspecialchars($paginasLibro, ENT_QUOTES, 'UTF-8') ?>"<?php endif; ?>
            <?php if ($isbnLibro !== ''): ?>data-isbn="<?= htmlspecialchars($isbnLibro, ENT_QUOTES, 'UTF-8') ?>"<?php endif; ?>
            <?php if ($ubicacionLibro !== ''): ?>data-ubicacion="<?= htmlspecialchars($ubicacionLibro, ENT_QUOTES, 'UTF-8') ?>"<?php endif; ?>>
            <div class="libro-img">
              <?php if ($portadaLibro !== ''): ?>
                <img src="../Imagenes/Libros/<?= htmlspecialchars($portadaLibro, ENT_QUOTES, 'UTF-8') ?>"
                  alt="<?= htmlspecialchars($tituloLibro, ENT_QUOTES, 'UTF-8') ?>" loading="lazy" />
              <?php endif; ?>
            </div>
            <h3><?= htmlspecialchars($tituloLibro, ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="autor"><?= htmlspecialchars($autorLibro, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="categoria"><?= htmlspecialchars($categoriaLibro, ENT_QUOTES, 'UTF-8') ?></p>
            <span class="estado <?= $estaDisponible ? 'disponible' : 'prestado' ?>"><?= $estaDisponible ? 'Disponible' : 'Prestado' ?></span>
            <button type="button" class="btn btn-primary btn-sm mt-3 btn-ver-detalle">Ver detalle</button>
          </article>
        <?php endforeach; ?>
      </div>
      <p class="text-muted" id="catalogo-sin-resultados" hidden>
        No se encontraron libros con esos criterios.
      </p>
    </section>
  </main>
  <hr />
  <footer class="site-footer">
    <div class="site-footer__inner">
      <!-- Columna 1: Marca -->
      <section class="site-footer__brand">
        <p class="site-footer__brand-name">
          BiblioLend<sup>&trade;</sup>
        </p>
        <p class="site-footer__brand-description">
          Accede al catálogo escolar, gestiona tus préstamos y consulta recursos
          académicos desde un solo lugar.
        </p>
        <ul class="site-footer__social" role="list">
          <li>
            <a class="site-footer__social-link" href="https://instagram.com" aria-label="BiblioLend en Instagram">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path fill="currentColor" d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.3 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.3-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.3-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2zm0 2c-3.1 0-3.5 0-4.8.1-1.1.1-1.7.2-2.1.4-.5.2-.9.4-1.2.8-.4.3-.6.7-.8 1.2-.1.4-.3 1-.4 2.1-.1 1.3-.1 1.7-.1 4.8s0 3.5.1 4.8c.1 1.1.2 1.7.4 2.1.2.5.4.9.8 1.2.3.4.7.6 1.2.8.4.1 1 .3 2.1.4 1.3.1 1.7.1 4.8.1s3.5 0 4.8-.1c1.1-.1 1.7-.2 2.1-.4.5-.2.9-.4 1.2-.8.4-.3.6-.7.8-1.2.1-.4.3-1 .4-2.1.1-1.3.1-1.7.1-4.8s0-3.5-.1-4.8c-.1-1.1-.2-1.7-.4-2.1-.2-.5-.4-.9-.8-1.2-.3-.4-.7-.6-1.2-.8-.4-.1-1-.3-2.1-.4-1.3-.1-1.7-.1-4.8-.1zm0 3.4a4.4 4.4 0 1 1 0 8.8 4.4 4.4 0 0 1 0-8.8zm0 7.2a2.8 2.8 0 1 0 0-5.6 2.8 2.8 0 0 0 0 5.6zm5.6-7.4a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
              </svg>
            </a>
          </li>
          <li>
            <a class="site-footer__social-link" href="https://facebook.com" aria-label="BiblioLend en Facebook">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path fill="currentColor" d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.3-1.5 1.6-1.5h1.6V3.6c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.4H7.8V13h2.7v8h3z" />
              </svg>
            </a>
          </li>
          <li>
            <a class="site-footer__social-link" href="https://x.com" aria-label="BiblioLend en X">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path fill="currentColor" d="M17.5 3h3l-6.6 7.5L21.8 21h-6l-4.7-6.1L5.6 21h-3l7-8L2.5 3h6.1l4.2 5.6L17.5 3zm-1 16h1.7L7.6 4.9H5.8L16.5 19z" />
              </svg>
            </a>
          </li>
          <li>
            <a class="site-footer__social-link" href="https://youtube.com" aria-label="BiblioLend en YouTube">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path fill="currentColor" d="M21.6 7.2s-.2-1.4-.8-2c-.7-.8-1.6-.8-2-.9C16 4.2 12 4.2 12 4.2s-4 0-6.8.2c-.4.1-1.3.1-2 .9-.6.6-.8 2-.8 2S2.2 8.8 2.2 10.5v1.6c0 1.6.2 3.3.2 3.3s.2 1.4.8 2c.7.8 1.7.7 2.1.8 1.6.2 6.7.2 6.7.2s4 0 6.8-.3c.4 0 1.3-.1 2-.9.6-.6.8-2 .8-2s.2-1.6.2-3.3v-1.6c0-1.6-.2-3.3-.2-3.3zM10 14.2V8.6l5.2 2.8-5.2 2.8z" />
              </svg>
            </a>
          </li>
        </ul>
      </section>

      <!-- Columna 2: enlaces -->
      <nav class="site-footer__nav" aria-labelledby="footer-links-title">
        <h2 class="site-footer__title" id="footer-links-title">Enlaces rápidos</h2>
        <ul class="site-footer__list" role="list">
          <li><a href="Panel_principal.php">Inicio</a></li>
          <li><a href="Catalogo.php">Catálogo</a></li>
          <li><a href="Resenas.php">Reseñas</a></li>
          <li><a href="Contactanos.html">Nuestro equipo</a></li>
          <li><a href="Sobre_nosotros.php">Sobre nosotros</a></li>
        </ul>
      </nav>

      <!-- Columna 3: contacto -->
      <section class="site-footer__contact" aria-labelledby="footer-contact-title">
        <h2 class="site-footer__title" id="footer-contact-title">Contacto</h2>
        <address class="site-footer__address">
          <p>
              <a href="https://maps.google.com/?q=6.2907713,-75.5595566" target="_blank" rel="noopener">Cra. 50B #97a-30, Santa Cruz<br />
              Medellín, Antioquia 050001
              </a>
          </p>
          <p>
              <a href="mailto:contacto@bibliolend.edu.co">contacto@bibliolend.edu.co</a>
          </p>
          <p>
              <a href="tel:+573001234567">+57 300 123 4567</a>
          </p>
        </address>
      </section>
    </div>
  </footer>

  <!-- Ventana emergente con el detalle de cada libro -->
  <div class="modal fade" id="modalDetalleLibro" tabindex="-1" aria-labelledby="detalleTituloModal"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content detalle-libro">
        <div class="modal-header detalle-libro__header">
          <h2 class="modal-title" id="detalleTituloModal">Detalle del libro</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar ventana"></button>
        </div>

        <div class="modal-body">
          <div class="detalle-libro__contenido">
            <div class="detalle-libro__portada" id="detallePortada">
              <img id="detalleImagen" alt="" hidden />
              <span class="detalle-libro__iniciales" id="detalleIniciales" aria-hidden="true"></span>
            </div>

            <div class="detalle-libro__info">
              <span class="estado disponible" id="detalleEstado">Disponible</span>
              <h3 class="detalle-libro__titulo" id="detalleTitulo"></h3>
              <p class="detalle-libro__autor" id="detalleAutor"></p>
              <p class="detalle-libro__categoria" id="detalleCategoria"></p>

              <dl class="detalle-libro__datos">
                <div>
                  <dt>Año</dt>
                  <dd id="detalleAnio"></dd>
                </div>
                <div>
                  <dt>Páginas</dt>
                  <dd id="detallePaginas"></dd>
                </div>
                <div>
                  <dt>ISBN</dt>
                  <dd id="detalleIsbn"></dd>
                </div>
                <div>
                  <dt>Ubicación</dt>
                  <dd id="detalleUbicacion"></dd>
                </div>
              </dl>

              <h4 class="detalle-libro__subtitulo">Sinopsis</h4>
              <p class="detalle-libro__descripcion" id="detalleDescripcion"></p>
            </div>
          </div>

          <p class="detalle-libro__aviso" id="detalleAviso" role="status" aria-live="polite" hidden></p>
        </div>

        <div class="modal-footer detalle-libro__footer">
          <button type="button" class="btn detalle-libro__cerrar" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success detalle-libro__accion" id="detalleAccion">Pedir
            prestado</button>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../JavaScript/Catalogo.js"></script>
</body>

</html>
