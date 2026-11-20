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
$usuarios = listarUsuariosGestion($pdo);
$resenas = listarResenas($pdo);

$escapar = static fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel de Administración | BiblioLend</title>
    <meta name="csrf-token" content="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />
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

      <section class="management" id="gestion-usuarios" aria-labelledby="usuarios-title">
        <div class="management__header">
          <div>
            <h2 id="usuarios-title" class="management__title">Gestión de Usuarios</h2>
            <p class="management__subtitle">
              Consulta los datos de cada usuario y el detalle de sus préstamos.
            </p>
          </div>
          <span class="management__meta"><?= count($usuarios) ?> usuarios registrados</span>
        </div>

        <?php if ($usuarios === []): ?>
          <p class="text-muted mb-0">Todavía no hay usuarios registrados.</p>
        <?php else: ?>
          <div class="users-grid">
            <?php foreach ($usuarios as $usuario): ?>
              <?php
                $avatarUsuario = rutaAvatarDesdeFoto($usuario['foto'] ?? null);
                $rolUsuario = (string) $usuario['rol'];
                $roles = [
                    'estudiante'    => 'Estudiante',
                    'bibliotecario' => 'Bibliotecario',
                    'administrador' => 'Administrador',
                ];
                $etiquetaRol = $roles[$rolUsuario] ?? ucfirst($rolUsuario);
                $sinPrestamos = (int) $usuario['prestamos_total'] === 0;
              ?>
              <article class="user-card">
                <header class="user-card__header">
                  <img
                    class="user-card__avatar"
                    src="../<?= $escapar($avatarUsuario) ?>"
                    alt="Avatar de <?= $escapar($usuario['nombre']) ?>"
                    loading="lazy"
                  />

                  <div class="user-card__info">
                    <h3 class="user-card__name"><?= $escapar($usuario['nombre']) ?></h3>
                    <p class="user-card__email"><?= $escapar($usuario['correo']) ?></p>
                    <p class="user-card__meta">
                      <span class="user-card__role user-card__role--<?= $escapar($rolUsuario) ?>">
                        <?= $escapar($etiquetaRol) ?>
                      </span>
                      <?php if (($usuario['grado_grupo'] ?? '') !== ''): ?>
                        <span>Grado: <?= $escapar($usuario['grado_grupo']) ?></span>
                      <?php endif; ?>
                      <?php if (($usuario['documento'] ?? '') !== ''): ?>
                        <span>Doc. <?= $escapar($usuario['documento']) ?></span>
                      <?php endif; ?>
                    </p>
                  </div>

                  <div class="user-card__stats" aria-label="Resumen de préstamos">
                    <span class="user-card__stat-number"><?= (int) $usuario['prestamos_activos'] ?></span>
                    <span class="user-card__stat-label">activos</span>
                    <span class="user-card__stat-total">
                      <?= (int) $usuario['prestamos_total'] ?> prestamos en total
                    </span>
                  </div>
                </header>

                <?php if ($sinPrestamos): ?>
                  <p class="user-card__empty">
                    Este usuario todavía no ha solicitado ningún libro.
                  </p>
                <?php else: ?>
                  <details class="user-card__loans">
                    <summary class="user-card__summary">
                      Ver préstamos (<?= (int) $usuario['prestamos_total'] ?>)
                    </summary>

                    <div class="table-container">
                      <table class="table-books user-loans-table">
                        <caption class="visually-hidden">
                          Préstamos de <?= $escapar($usuario['nombre']) ?>
                        </caption>
                        <thead>
                          <tr>
                            <th scope="col">Ticket</th>
                            <th scope="col">Libro</th>
                            <th scope="col">Solicitud</th>
                            <th scope="col">Préstamo</th>
                            <th scope="col">Vence</th>
                            <th scope="col">Estado</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($usuario['prestamos'] as $prestamo): ?>
                            <tr>
                              <td data-label="Ticket">
                                <strong><?= $escapar($prestamo['codigo_ticket']) ?></strong>
                              </td>
                              <td data-label="Libro">
                                <?= $escapar($prestamo['titulo']) ?><br />
                                <small><?= $escapar($prestamo['autor']) ?></small>
                              </td>
                              <td data-label="Solicitud">
                                <?= formatearFecha(substr((string) $prestamo['fecha_solicitud'], 0, 10)) ?>
                              </td>
                              <td data-label="Préstamo">
                                <?= formatearFecha($prestamo['fecha_prestamo']) ?>
                              </td>
                              <td data-label="Vence">
                                <?= formatearFecha($prestamo['fecha_vencimiento']) ?>
                              </td>
                              <td data-label="Estado">
                                <span class="estado <?= $escapar($prestamo['clase']) ?>">
                                  <?= $escapar($prestamo['etiqueta']) ?>
                                </span>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </details>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

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
