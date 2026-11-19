<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';
requerirRol(['estudiante', 'bibliotecario', 'administrador']);
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sobre Nosotros - BiblioLend</title>
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
  <body class="pagina-sobre-nosotros">
    <header id="headerSobre">
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
            data-bs-target="#menuSobre"
            aria-controls="menuSobre"
            aria-expanded="false"
            aria-label="Abrir menú"
          >
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="menuSobre">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <a class="nav-link" href="Panel_principal.php">Inicio</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" href="Sobre_nosotros.php">Sobre Nosotros</a>
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
              <li class="nav-item">
                <a href="Cuenta_usuarios.php">
                  <img
                    src="../<?= htmlspecialchars(rutaAvatarUsuario(), ENT_QUOTES, 'UTF-8') ?>"
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

    <main>
      <section class="bloque" id="section_sobre_nosotros">
        <h2>Sobre Nosotros: CodeForge</h2>
        <p>
          Somos <strong>CodeForge</strong>, un equipo de estudiantes de la
          <strong>Institución Educativa República de Honduras</strong>
          apasionados por la tecnología. Nos unimos con el objetivo de crear
          <strong>BiblioLend</strong>, una solución digital para mejorar la
          gestión bibliotecaria de nuestra institución.
        </p>
      </section>

      <section class="bloque" id="section_mision">
        <h2>Nuestra Misión</h2>
        <p>
          facilitar el acceso a los recursos bibliográficos mediante tecnología
          simple, accesible y fácil de usar.
        </p>
      </section>

      <section class="bloque" id="section_equipo">
        <h2>Nuestro Equipo</h2>

        <div class="accordion accordion-equipo" id="acordeonEquipo">
          <div class="accordion-item equipo-item">
            <h2 class="accordion-header">
              <button class="accordion-button equipo-boton" type="button" data-bs-toggle="collapse" data-bs-target="#miembro1">
                Eugenio Morales
              </button>
            </h2>
            <div id="miembro1" class="accordion-collapse collapse show" data-bs-parent="#acordeonEquipo">
              <div class="accordion-body equipo-body">
                <p><strong>Rol:</strong> Especializado FullStack</p>
                <p><strong>Responsabilidad:</strong> Colaboración y ejecución del proyecto</p>
              </div>
            </div>
          </div>

          <div class="accordion-item equipo-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed equipo-boton" type="button" data-bs-toggle="collapse" data-bs-target="#miembro2">
                Santiago Palomino
              </button>
            </h2>
            <div id="miembro2" class="accordion-collapse collapse" data-bs-parent="#acordeonEquipo">
              <div class="accordion-body equipo-body">
                <p><strong>Rol:</strong> Desarrollador destacado en Frontend</p>
                <p><strong>Responsabilidad:</strong> Diseño y estructura de las páginas</p>
              </div>
            </div>
          </div>

          <div class="accordion-item equipo-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed equipo-boton" type="button" data-bs-toggle="collapse" data-bs-target="#miembro3">
                Hans Rentería
              </button>
            </h2>
            <div id="miembro3" class="accordion-collapse collapse" data-bs-parent="#acordeonEquipo">
              <div class="accordion-body equipo-body">
                <p><strong>Rol:</strong> Desarrollador especialista en Base de Datos</p>
                <p><strong>Responsabilidad:</strong> Lógica del sistema y base de datos</p>
              </div>
            </div>
          </div>

          <div class="accordion-item equipo-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed equipo-boton" type="button" data-bs-toggle="collapse" data-bs-target="#miembro4">
                Juan Camilo Puerta
              </button>
            </h2>
            <div id="miembro4" class="accordion-collapse collapse" data-bs-parent="#acordeonEquipo">
              <div class="accordion-body equipo-body">
                <p><strong>Rol:</strong> Analista de código, Coordinación y FullStack</p>
                <p><strong>Responsabilidad:</strong> Diseñador, planificación y pruebas del sistema</p>
              </div>
            </div>
          </div>
        </div>
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
  </body>
</html>
