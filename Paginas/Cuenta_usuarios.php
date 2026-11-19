<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';
require_once __DIR__ . '/../funciones_biblioteca.php';
requerirRol('estudiante');

$idUsuario = (int) $_SESSION['usuario_id'];

$stmt = $pdo->prepare(
    "SELECT primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
            correo, grado, grupo
     FROM usuario
     WHERE id_usuario = ?
     LIMIT 1"
);
$stmt->execute([$idUsuario]);
$datosUsuario = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$nombreCompleto = trim(implode(' ', array_filter([
    $datosUsuario['primer_nombre'] ?? '',
    $datosUsuario['segundo_nombre'] ?? '',
    $datosUsuario['primer_apellido'] ?? '',
    $datosUsuario['segundo_apellido'] ?? '',
])));
if ($nombreCompleto === '') {
    $nombreCompleto = (string) ($_SESSION['nombre'] ?? 'Estudiante');
}

$gradoGrupo = trim(($datosUsuario['grado'] ?? '') . ' ' . ($datosUsuario['grupo'] ?? ''));
$misLibros = listarMisLibros($pdo, $idUsuario);
$prestamosActivos = contarPrestamosActivos($pdo, $idUsuario);
$retrasos = contarRetrasos($pdo, $idUsuario);
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cuenta de Usuario - BiblioLend</title>

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
  <body class="pagina-cuenta-usuarios">
    <header>
      <nav class="navbar navbar-expand-lg navbar-dark navbar-sobre">
        <div class="container">
          <a
            class="navbar-brand d-flex align-items-center gap-2"
            href="Panel_principal.php"
          >
            <img
              class="logoBibliolend"
              alt="Logo BiblioLend"
              src="../Imagenes/Logo_bibliolend.png"
              width="80"
              height="80"
            />
            <span class="titulo-logo">BiblioLend</span>
          </a>

          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menuNav"
            aria-controls="menuNav"
            aria-expanded="false"
            aria-label="Abrir menú"
          >
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="menuNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <a class="nav-link" href="Panel_principal.php">Inicio</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="Sobre_nosotros.php"
                  >Sobre Nosotros</a
                >
              </li>
              <li class="nav-item">
                <a class="nav-link" href="Contactanos.html">Contáctanos</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="Resenas.php">Reseñas</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="Catalogo.php">Catálogo</a>
              </li>
              <li>
                <a href="Cuenta_usuarios.php">
                  <img
                    src="../<?= htmlspecialchars($avatarActual, ENT_QUOTES, 'UTF-8') ?>"
                    alt="Imagen de Perfil"
                    class="navItem--perfil"
                    data-avatar-nav
                  />
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <main class="panel-usuario">
      <?php mostrarFlash(); ?>
      <section class="panel-layout" id="pantalla">
        <aside class="sidebar" id="datos_usuario">
          <div class="avatar"></div>
          <h2 class="user-name" data-user-name><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="user-role" data-user-role><?= htmlspecialchars(ucfirst($_SESSION['rol'] ?? 'Estudiante'), ENT_QUOTES, 'UTF-8') ?></p>

          <div class="user-data">
            <p><strong>Correo:</strong> <span data-user-email><?= htmlspecialchars($datosUsuario['correo'] ?? ($_SESSION['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></p>
            <p><strong>Grado:</strong> <?= htmlspecialchars($gradoGrupo !== '' ? $gradoGrupo : 'Sin registrar', ENT_QUOTES, 'UTF-8') ?></p>
          </div>

          <a href="#" class="btn-panel activo">Editar Perfil</a>
          <a href="../logout.php" class="btn-panel btn-cerrar">Cerrar sesión</a>
        </aside>

        <section class="content">
          <div class="header-box">
            <h3>Bienvenid@ a tu cuenta, <?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></h3>
            <p>
              Desde aquí puedes consultar tu actividad, revisar tus préstamos y
              mantener tu información al día.
            </p>
          </div>

          <div class="stats-grid stats-grid--two">
            <div class="stat-card">
              <h4>Préstamos activos</h4>
              <span data-stat="prestamos-activos"><?= $prestamosActivos ?></span>
            </div>
            <div class="stat-card">
              <h4>Libros vencidos por devolver</h4>
              <span data-stat="libros-vencidos"><?= $retrasos ?></span>
            </div>
          </div>

          <div class="card-panel">
            <h4>Estado de mis libros</h4>
            <p class="prestamos-hint">
              Aquí ves tus solicitudes, préstamos activos y devoluciones en proceso.
            </p>

            <?php if ($misLibros === []): ?>
              <p class="mb-0">
                No tienes libros solicitados ni prestados.
                <a href="Catalogo.php">Explora el catálogo</a>.
              </p>
            <?php else: ?>
              <div class="recommend-grid">
                <?php foreach ($misLibros as $prestamo): ?>
                  <?php
                    $dias = diasParaVencimiento($prestamo['fecha_vencimiento']);
                    $claseDias = '';
                    $textoDias = '—';

                    if ($prestamo['estado'] === 'solicitado') {
                        $textoDias = 'Por entregar';
                    } elseif ($prestamo['estado'] === 'devolucion_solicitada') {
                        $textoDias = 'En devolución';
                    } elseif ($dias !== null) {
                        if ($dias < 0) {
                            $textoDias = abs($dias) . ' días de retraso';
                            $claseDias = 'dias-restantes--vencido';
                        } elseif ($dias === 0) {
                            $textoDias = 'Vence hoy';
                            $claseDias = 'dias-restantes--urgente';
                        } else {
                            $textoDias = $dias . ' días';
                            $claseDias = $dias <= 3 ? 'dias-restantes--urgente' : '';
                        }
                    }
                  ?>
                  <div class="recommend-item is-open">
                    <div class="recommend-item__toggle recommend-item__toggle--static">
                      <span class="thumb" aria-hidden="true">
                        <?php if (!empty($prestamo['portada'])): ?>
                          <img src="../Imagenes/Libros/<?= htmlspecialchars(basename((string) $prestamo['portada']), ENT_QUOTES, 'UTF-8') ?>" alt="" />
                        <?php else: ?>
                          <span class="thumb__iniciales"><?= htmlspecialchars(inicialesTitulo((string) $prestamo['titulo']), ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                      </span>
                      <span class="recommend-item__info">
                        <span class="book-title"><?= htmlspecialchars($prestamo['titulo'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="book-meta"><?= htmlspecialchars($prestamo['categoria'], ENT_QUOTES, 'UTF-8') ?></span>
                      </span>
                      <span class="dias-restantes <?= $claseDias ?>"><?= htmlspecialchars($textoDias, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>

                    <div class="recommend-detail">
                      <dl class="recommend-detail__datos">
                        <div>
                          <dt>Fecha de solicitud</dt>
                          <dd><?= formatearFecha(substr((string) $prestamo['fecha_solicitud'], 0, 10)) ?></dd>
                        </div>
                        <div>
                          <dt>Fecha de devolución</dt>
                          <dd><?= formatearFecha($prestamo['fecha_vencimiento']) ?></dd>
                        </div>
                        <div>
                          <dt>Estado</dt>
                          <dd>
                            <span class="estado <?= htmlspecialchars($prestamo['clase'], ENT_QUOTES, 'UTF-8') ?>">
                              <?= htmlspecialchars($prestamo['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                          </dd>
                        </div>
                      </dl>

                      <p class="mt-3 mb-2 small text-muted">
                        Ticket: <strong><?= htmlspecialchars($prestamo['codigo_ticket'], ENT_QUOTES, 'UTF-8') ?></strong>
                      </p>

                      <?php if ($prestamo['estado'] === 'solicitado'): ?>
                        <form action="../acciones.php" method="post">
                          <?= csrfCampo() ?>
                          <input type="hidden" name="accion" value="cancelar_solicitud">
                          <input type="hidden" name="id_libro" value="<?= (int) $prestamo['id_libro'] ?>">
                          <input type="hidden" name="volver" value="Cuenta_usuarios.php">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar solicitud</button>
                        </form>
                      <?php elseif ($prestamo['estado'] === 'activo'): ?>
                        <form action="../acciones.php" method="post">
                          <?= csrfCampo() ?>
                          <input type="hidden" name="accion" value="solicitar_devolucion">
                          <input type="hidden" name="id_prestamo" value="<?= (int) $prestamo['id_prestamo'] ?>">
                          <input type="hidden" name="volver" value="Cuenta_usuarios.php">
                          <button type="submit" class="btn btn-sm btn-success">Solicitar devolución</button>
                        </form>
                      <?php elseif ($prestamo['estado'] === 'devolucion_solicitada'): ?>
                        <p class="mb-0 small text-muted">Entrega el libro en la biblioteca; el bibliotecario validará la devolución.</p>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </section>
  </section>
      <br />
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../JavaScript/Cuenta_usuarios.js"></script>
  </body>
</html>
