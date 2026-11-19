<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';
require_once __DIR__ . '/../funciones_biblioteca.php';
requerirRol('administrador');

$usuariosRegistrados = contarUsuariosRegistrados($pdo);
$librosDisponibles = contarLibrosDisponibles($pdo);
$prestamosActivos = contarPrestamosActivos($pdo);
$totalResenas = contarResenas($pdo);
$ticketsPorValidar = contarTicketsPorValidar($pdo);
$retrasos = contarRetrasos($pdo);
$libros = $pdo->query(
    "SELECT id_libro, titulo, autor, copias, disponibles
     FROM libro
     ORDER BY titulo"
)->fetchAll(PDO::FETCH_ASSOC);
$resenas = listarResenas($pdo);
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel de Administración | BiblioLend</title>
    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    />
    <!-- Tipografías (mismas que la landing) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../Css/BiblioLend.css" />
    <!-- Pie de página compartido -->
    
  </head>

  <body class="pagina-panel-admin">
    <header class="admin-header">
      <div class="admin-header__brand">
        <img
          src="../Imagenes/Logo_bibliolend.png"
          alt="Logo BiblioLend"
          class="admin-header__logo"
        />

        <div class="admin-header__info">
          <h1 class="admin-header__title">Panel de Administración</h1>
          <p class="admin-header__subtitle">Gestión general de la plataforma</p>
        </div>
      <a href="../logout.php" class="btn-panel btn-cerrar">Cerrar sesión</a>

      </div>
    </header>

    <main class="admin-main">
      <?php mostrarFlash(); ?>
      <section class="dashboard" aria-labelledby="dashboard-title">
        <h2 id="dashboard-title" class="dashboard__title">Resumen General</h2>

        <div class="dashboard__cards">
          <article class="card-stat">
            <h3 class="card-stat__title">Usuarios registrados</h3>
            <p class="card-stat__value"><?= $usuariosRegistrados ?></p>
          </article>

          <article class="card-stat">
            <h3 class="card-stat__title">Copias disponibles</h3>
            <p class="card-stat__value"><?= $librosDisponibles ?></p>
          </article>

          <article class="card-stat">
            <h3 class="card-stat__title">Préstamos activos</h3>
            <p class="card-stat__value"><?= $prestamosActivos ?></p>
          </article>

          <article class="card-stat">
            <h3 class="card-stat__title">Tickets por validar</h3>
            <p class="card-stat__value"><?= $ticketsPorValidar ?></p>
          </article>

          <article class="card-stat">
            <h3 class="card-stat__title">Retrasos</h3>
            <p class="card-stat__value"><?= $retrasos ?></p>
          </article>

          <article class="card-stat">
            <h3 class="card-stat__title">Reseñas activas</h3>
            <p class="card-stat__value"><?= $totalResenas ?></p>
          </article>
        </div>
      </section>

      <section class="management" aria-labelledby="management-title">
        <div class="management__header">
          <h2 id="management-title" class="management__title">
            Gestión de Libros
          </h2>

          <button type="submit" class="management__button" id="btnAgregarLibro">Agregar libro</button>
        </div>

        <div class="table-container">
          <table class="table-books tabla-apilada">
            <caption class="visually-hidden">Libros registrados en la biblioteca</caption>
            <thead class="table-books__head">
              <tr>
                <th scope="col">Título</th>
                <th scope="col">Autor</th>
                <th scope="col">Estado</th>
                <th scope="col">Acciones</th>
              </tr>
            </thead>

            <tbody class="table-books__body">
              <?php if ($libros === []): ?>
                <tr>
                  <td colspan="4">No hay libros registrados.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($libros as $libro): ?>
                  <?php $disponible = (int) $libro['disponibles'] > 0; ?>
                  <tr>
                    <td data-label="Título"><?= htmlspecialchars($libro['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Autor"><?= htmlspecialchars($libro['autor'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Estado">
                      <span class="table-books__status <?= $disponible ? 'table-books__status--available' : 'table-books__status--borrowed' ?>">
                        <?= $disponible ? 'Disponible' : 'Prestado' ?>
                      </span><br>
                      <small><?= (int) $libro['disponibles'] ?> de <?= (int) $libro['copias'] ?> copias</small>
                    </td>
                    <td data-label="Acciones">
                      <button type="button" class="table-books__action" disabled>Editar</button>
                      <button type="button" class="table-books__action table-books__action--danger" disabled>Eliminar</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
<br>
      <section class="management" aria-labelledby="resenas-title">
        <div class="management__header">
          <h2 class="management__title" id="resenas-title">Reseñas</h2>
        </div>

        <?php if ($resenas === []): ?>
          <p class="text-muted mb-0">Todavía no hay reseñas enviadas por los estudiantes.</p>
        <?php else: ?>
          <div class="table-container">
            <table class="table-books tabla-apilada">
              <caption class="visually-hidden">Reseñas de los estudiantes</caption>
              <thead class="table-books__head">
                <tr>
                  <th scope="col">Estudiante</th>
                  <th scope="col">Correo</th>
                  <th scope="col">Reseña</th>
                  <th scope="col">Fecha</th>
                </tr>
              </thead>
              <tbody class="table-books__body">
                <?php foreach ($resenas as $resena): ?>
                  <tr>
                    <td data-label="Estudiante"><?= htmlspecialchars($resena['estudiante'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Correo"><?= htmlspecialchars($resena['correo'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Reseña"><?= nl2br(htmlspecialchars($resena['cuerpo'], ENT_QUOTES, 'UTF-8')) ?></td>
                    <td data-label="Fecha"><?= formatearFecha(substr((string) $resena['fecha'], 0, 10)) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    </main>

    <br />
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
                  <li><a href="Panel_admin.php">Inicio</a></li>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
