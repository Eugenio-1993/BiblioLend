<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';requerirRol('estudiante');
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
                    src="../Imagenes/Imagen_de_perfil.jpg"
                    alt="Imagen de Perfil"
                    class="navItem--perfil"
                  />
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <main class="panel-usuario">
      <section class="panel-layout" id="pantalla">
        <aside class="sidebar" id="datos_usuario">
          <div class="avatar"></div>
          <h2 class="user-name" data-user-name><?= htmlspecialchars($_SESSION['nombre']) ?></h2>
          <p class="user-role" data-user-role><?= htmlspecialchars(ucfirst($_SESSION['rol'] ?? 'Estudiante'), ENT_QUOTES, 'UTF-8') ?></p>

          <div class="user-data">
            <p><strong>Correo:</strong> <span data-user-email><?= htmlspecialchars($_SESSION['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></p>
            <p><strong>Matricula:</strong> 190094</p>
          </div>

          <a href="#" class="btn-panel activo">Editar Perfil</a>
          <a href="../logout.php" class="btn-panel btn-cerrar">Cerrar sesión</a>
        </aside>

        <section class="content">
          <div class="header-box">
            <h3>Bienvenido a tu cuenta</h3>
            <p>
              Desde aquí puedes consultar tu actividad, revisar tus préstamos y
              mantener tu información al día.
            </p>
          </div>

          <div class="stats-grid stats-grid--two">
            <div class="stat-card">
              <h4>Préstamos activos</h4>
              <span data-stat="prestamos-activos">2</span>
            </div>
            <div class="stat-card">
              <h4>Libros Vencidos por devolver</h4>
              <span data-stat="libros-vencidos">0</span>
            </div>
          </div>

          <div class="card-panel">
            <h4>Estado de mis libros</h4>
            <p class="prestamos-hint">
              Haz clic en un libro para consultar las fechas de tu préstamo.
            </p>
            <div class="recommend-grid">
              <div class="recommend-item" data-inicio="2026-11-02" data-devolucion="2026-11-17">
                <button type="button" class="recommend-item__toggle" aria-expanded="false"
                  aria-controls="detalle-oliver-twist" aria-label="Ver fechas del préstamo de Oliver Twist">
                  <span class="thumb thumb--oliver" aria-hidden="true">
                    <span class="thumb__iniciales">OT</span>
                  </span>
                  <span class="recommend-item__info">
                    <span class="book-title">Oliver Twist</span>
                    <span class="book-meta">Drama</span>
                  </span>
                  <span class="dias-restantes" data-dias-restantes>—</span>
                </button>

                <div class="recommend-detail" id="detalle-oliver-twist" hidden>
                  <dl class="recommend-detail__datos">
                    <div>
                      <dt>Fecha de inicio</dt>
                      <dd data-fecha-inicio>—</dd>
                    </div>
                    <div>
                      <dt>Fecha de devolución</dt>
                      <dd data-fecha-devolucion>—</dd>
                    </div>
                    <div>
                      <dt>Días para entregar</dt>
                      <dd data-dias-detalle>—</dd>
                    </div>
                  </dl>
                </div>
              </div>

              <div class="recommend-item" data-inicio="2026-10-29" data-devolucion="2026-11-12">
                <button type="button" class="recommend-item__toggle" aria-expanded="false"
                  aria-controls="detalle-meditaciones" aria-label="Ver fechas del préstamo de Meditaciones">
                  <span class="thumb" aria-hidden="true">
                    <img src="../Imagenes/Libros/Meditaciones_de_Marco-Aurelio.png" alt="" />
                  </span>
                  <span class="recommend-item__info">
                    <span class="book-title">Meditaciones</span>
                    <span class="book-meta">Historia</span>
                  </span>
                  <span class="dias-restantes" data-dias-restantes>—</span>
                </button>

                <div class="recommend-detail" id="detalle-meditaciones" hidden>
                  <dl class="recommend-detail__datos">
                    <div>
                      <dt>Fecha de inicio</dt>
                      <dd data-fecha-inicio>—</dd>
                    </div>
                    <div>
                      <dt>Fecha de devolución</dt>
                      <dd data-fecha-devolucion>—</dd>
                    </div>
                    <div>
                      <dt>Días para entregar</dt>
                      <dd data-dias-detalle>—</dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>
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
    <script src="../JavaScript/Cuenta_usuarios.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
