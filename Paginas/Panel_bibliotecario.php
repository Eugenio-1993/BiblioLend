<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';
require_once __DIR__ . '/../funciones_biblioteca.php';
requerirRol('bibliotecario');

$librosDisponibles = contarLibrosDisponibles($pdo);
$prestamosActivos = contarPrestamosActivos($pdo);
$ticketsPorValidar = contarTicketsPorValidar($pdo);
$retrasos = contarRetrasos($pdo);
$tickets = listarTicketsPorValidar($pdo);
$prestamos = listarPrestamosActivos($pdo);
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel del Bibliotecario - BiblioLend</title>

    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Tipografías (mismas que la landing) -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../Css/BiblioLend.css" />
    <!-- Pie de página compartido -->
    
  </head>
  <body class="pagina-panel-bibliotecario">
    <header class="admin-header">
      <div class="admin-header__brand">
        <img
          src="../Imagenes/Logo_bibliolend.png"
          alt="Logo BiblioLend"
          class="admin-header__logo"
        />

        <div class="admin-header__info">
          <h1 class="admin-header__title">Panel de Bibliotecario</h1>
          <p class="admin-header__subtitle">
            Gestión de libros de la plataforma
          </p>
        </div>
        <a href="../logout.php" class="btn-panel btn-cerrar">Cerrar sesión</a>
      </div>
    </header>

    <main class="panel-bibliotecario">
      <section class="bloque bienvenida">
        <div>
          <h2>Panel del Bibliotecario</h2>
          <p>
            Administra préstamos, devoluciones, catálogo y actividad reciente de
            la biblioteca.
          </p>
        </div>
        <a href="Catalogo.php" class="btn btn-success">Mostrar Catálogo</a>
      </section>

      <?php mostrarFlash(); ?>

      <section class="stats-grid" aria-label="Resumen de la biblioteca">
        <article class="stat-card">
          <h3>Libros disponibles</h3>
          <p><?= $librosDisponibles ?></p>
        </article>
        <article class="stat-card">
          <h3>Préstamos activos</h3>
          <p><?= $prestamosActivos ?></p>
        </article>
        <article class="stat-card">
          <h3>Pendientes por validar</h3>
          <p><?= $ticketsPorValidar ?></p>
        </article>
        <article class="stat-card alerta">
          <h3>Retrasos</h3>
          <p><?= $retrasos ?></p>
        </article>
      </section>

      <section class="bloque acciones-rapidas">
        <h2>Acciones rápidas</h2>
        <div class="acciones-grid">
          <a href="#validar-ticket" class="accion-card">Validar ticket</a>
          <a href="#tickets-pendientes" class="accion-card">Entregas pendientes</a>
          <a href="#prestamos-activos" class="accion-card">Préstamos activos</a>
          <a href="Catalogo.php" class="accion-card">Ver catálogo</a>
        </div>
      </section>

      <section class="bloque" id="validar-ticket">
        <h2>Validar ticket</h2>
        <p class="text-muted">
          Escribe el código del ticket para registrar la entrega del libro o su devolución.
        </p>

        <form action="../acciones.php" method="post" class="row g-2 align-items-end">
          <?= csrfCampo() ?>
          <div class="col-md-6">
            <label for="codigo-ticket" class="form-label">Código del ticket</label>
            <input type="text" class="form-control" id="codigo-ticket" name="codigo_ticket"
              placeholder="Ejemplo: BL-3FA9C1" autocomplete="off" required>
          </div>
          <div class="col-md-6">
            <input type="hidden" name="volver" value="Panel_bibliotecario.php">
            <button type="submit" name="accion" value="validar_entrega" class="btn btn-success">
              Entregar libro
            </button>
            <button type="submit" name="accion" value="validar_devolucion" class="btn btn-outline-primary">
              Recibir devolución
            </button>
          </div>
        </form>
        <p class="small text-muted mt-2 mb-0">
          Al entregar se descuenta una copia y el préstamo vence en <?= DIAS_PRESTAMO ?> días.
        </p>
      </section>

      <section class="bloque" id="tickets-pendientes">
        <h2>Tickets pendientes</h2>
        <p class="text-muted">
          Solicitudes de entrega y devoluciones que esperan tu validación.
        </p>

        <?php if ($tickets === []): ?>
          <p class="text-muted mb-0">No hay tickets pendientes por validar.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered align-middle tabla-apilada">
              <caption class="visually-hidden">Tickets pendientes de validación</caption>
              <thead>
                <tr>
                  <th scope="col">Ticket</th>
                  <th scope="col">Estudiante</th>
                  <th scope="col">Libro</th>
                  <th scope="col">Fecha solicitud</th>
                  <th scope="col">Estado</th>
                  <th scope="col">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tickets as $ticket): ?>
                  <tr>
                    <td data-label="Ticket"><strong><?= htmlspecialchars($ticket['codigo_ticket'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td data-label="Estudiante">
                      <?= htmlspecialchars($ticket['estudiante'], ENT_QUOTES, 'UTF-8') ?><br>
                      <small class="text-muted"><?= htmlspecialchars(trim(($ticket['grado'] ?? '') . ' ' . ($ticket['grupo'] ?? '')), ENT_QUOTES, 'UTF-8') ?></small>
                    </td>
                    <td data-label="Libro"><?= htmlspecialchars($ticket['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Fecha solicitud"><?= formatearFecha(substr((string) $ticket['fecha_solicitud'], 0, 10)) ?></td>
                    <td data-label="Estado">
                      <span class="estado <?= htmlspecialchars($ticket['clase'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($ticket['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
                      </span>
                    </td>
                    <td data-label="Acciones">
                      <form action="../acciones.php" method="post" class="d-inline">
                        <?= csrfCampo() ?>
                        <input type="hidden" name="codigo_ticket" value="<?= htmlspecialchars($ticket['codigo_ticket'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="volver" value="Panel_bibliotecario.php">
                        <?php if ($ticket['estado'] === 'solicitado'): ?>
                          <button type="submit" name="accion" value="validar_entrega" class="btn btn-sm btn-success">Entregar libro</button>
                        <?php else: ?>
                          <button type="submit" name="accion" value="validar_devolucion" class="btn btn-sm btn-primary">Recibir devolución</button>
                        <?php endif; ?>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

      <section class="bloque" id="prestamos-activos">
        <h2>Préstamos activos</h2>
        <p class="text-muted">Libros que están actualmente en manos de los estudiantes.</p>

        <?php if ($prestamos === []): ?>
          <p class="text-muted mb-0">No hay préstamos activos en este momento.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered align-middle tabla-apilada">
              <caption class="visually-hidden">Préstamos activos</caption>
              <thead>
                <tr>
                  <th scope="col">Ticket</th>
                  <th scope="col">Estudiante</th>
                  <th scope="col">Libro</th>
                  <th scope="col">Fecha préstamo</th>
                  <th scope="col">Fecha devolución</th>
                  <th scope="col">Estado</th>
                  <th scope="col">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($prestamos as $prestamo): ?>
                  <tr>
                    <td data-label="Ticket"><strong><?= htmlspecialchars($prestamo['codigo_ticket'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td data-label="Estudiante">
                      <?= htmlspecialchars($prestamo['estudiante'], ENT_QUOTES, 'UTF-8') ?><br>
                      <small class="text-muted"><?= htmlspecialchars(trim(($prestamo['grado'] ?? '') . ' ' . ($prestamo['grupo'] ?? '')), ENT_QUOTES, 'UTF-8') ?></small>
                    </td>
                    <td data-label="Libro"><?= htmlspecialchars($prestamo['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Fecha préstamo"><?= formatearFecha($prestamo['fecha_prestamo']) ?></td>
                    <td data-label="Fecha devolución"><?= formatearFecha($prestamo['fecha_vencimiento']) ?></td>
                    <td data-label="Estado">
                      <span class="estado <?= htmlspecialchars($prestamo['clase'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($prestamo['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
                      </span>
                    </td>
                    <td data-label="Acciones">
                      <form action="../acciones.php" method="post" class="d-inline">
                        <?= csrfCampo() ?>
                        <input type="hidden" name="accion" value="validar_devolucion">
                        <input type="hidden" name="codigo_ticket" value="<?= htmlspecialchars($prestamo['codigo_ticket'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="volver" value="Panel_bibliotecario.php">
                        <button type="submit" class="btn btn-sm btn-primary">Registrar devolución</button>
                      </form>
                    </td>
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
                  <li><a href="Panel_bibliotecario.php">Inicio</a></li>
                  <li><a href="Catalogo.php">Catálogo</a></li>
                  <li><a href="Resenas.php">Reseñas</a></li>
                  <li><a href="Contactanos.php">Nuestro equipo</a></li>
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
