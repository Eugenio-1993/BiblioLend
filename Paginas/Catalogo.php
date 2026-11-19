<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';
requerirRol(['estudiante', 'bibliotecario', 'administrador']);

// Catálogo centralizado en datos/libros_catalogo.json.
$rutaLibros = __DIR__ . '/../datos/libros_catalogo.json';
$libros = [];

if (is_readable($rutaLibros)) {
    $contenidoLibros = file_get_contents($rutaLibros);
    $librosDecodificados = json_decode($contenidoLibros, true);

    if (is_array($librosDecodificados)) {
        $libros = $librosDecodificados;
    }
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
              <a href="Cuenta_usuarios.php"><img src="../Imagenes/Imagen_de_perfil.jpg" alt="Imagen de Perfil"
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
