<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';
requerirRol('estudiante');
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BiblioLend</title>
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
  <body class="pagina-panel-principal">
    <header>
      <nav class="navbar navbar-expand-lg navbar-sobre">
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
                <a class="nav-link active" href="Panel_principal.php">Inicio</a>
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
                <a class="nav-link" href="Catalogo.php">Catálogo</a>
              </li>
              <li class="nav-item">
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

    <main>
      <section class="bloque">
        <h2 data-saludo>Bienvenido de vuelta, <?= htmlspecialchars($_SESSION['nombre']) ?> </h2>
        <p>
          Presiona el botón y acceda al catálogo de libros escolares en cualquier lugar y en
          cualquier momento.
        </p>
        <a href="Catalogo.php" class="btn btn-success">
          Catálogo Completo
        </a>
      </section>

      <hr />

      <section class="bloque">
        <h2 class="visually-hidden">Galería</h2>
        <div
          id="carouselBiblioteca"
          class="carousel slide"
          data-bs-ride="carousel"
          data-bs-interval="3000"
        >
          <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselBiblioteca" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Imagen 1"></button>
            <button type="button" data-bs-target="#carouselBiblioteca" data-bs-slide-to="1" aria-label="Imagen 2"></button>
            <button type="button" data-bs-target="#carouselBiblioteca" data-bs-slide-to="2" aria-label="Imagen 3"></button>
            <button type="button" data-bs-target="#carouselBiblioteca" data-bs-slide-to="3" aria-label="Imagen 4"></button>
            <button type="button" data-bs-target="#carouselBiblioteca" data-bs-slide-to="4" aria-label="Imagen 5"></button>
          </div>

          <div class="carousel-inner rounded shadow">
            <div class="carousel-item active">
              <img src="../Imagenes/471793.jpg" class="d-block w-100 img-carousel" alt="Estanterías con libros en la biblioteca del colegio" />
              <div class="carousel-caption d-none d-md-block">
                <h5>Conoce nuestra biblioteca</h5>
                <p>Un espacio escolar lleno de historias y recursos para aprender.</p>
              </div>
            </div>
            <div class="carousel-item">
              <img src="../Imagenes/471794.jpg" class="d-block w-100 img-carousel" alt="Colección de libros de la biblioteca escolar" />
              <div class="carousel-caption d-none d-md-block">
                <h5>Libros para descubrir</h5>
                <p>Explora los títulos disponibles para nuestra comunidad educativa.</p>
              </div>
            </div>
            <div class="carousel-item">
              <img src="../Imagenes/471795.jpg" class="d-block w-100 img-carousel" alt="Estanterías de libros infantiles en la biblioteca del colegio" />
              <div class="carousel-caption d-none d-md-block">
                <h5>Leer abre nuevas ideas</h5>
                <p>Encuentra lecturas para aprender, imaginar y crecer.</p>
              </div>
            </div>
            <div class="carousel-item">
              <img src="../Imagenes/471796.jpg" class="d-block w-100 img-carousel" alt="Libros organizados en las estanterías de la biblioteca escolar" />
              <div class="carousel-caption d-none d-md-block">
                <h5>Un lugar para aprender</h5>
                <p>La biblioteca acompaña cada paso de tu aprendizaje.</p>
              </div>
            </div>
            <div class="carousel-item">
              <img src="../Imagenes/471797.jpg" class="d-block w-100 img-carousel" alt="Estanterías de recursos y libros de la biblioteca del colegio" />
              <div class="carousel-caption d-none d-md-block">
                <h5>Tu biblioteca escolar</h5>
                <p>Acércate a los libros y aprovecha todos sus recursos.</p>
              </div>
            </div>
          </div>

          <button
            class="carousel-control-prev"
            type="button"
            data-bs-target="#carouselBiblioteca"
            data-bs-slide="prev"
          >
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
          </button>

          <button
            class="carousel-control-next"
            type="button"
            data-bs-target="#carouselBiblioteca"
            data-bs-slide="next"
          >
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
          </button>
        </div>
      </section>

      <section class="bloque">
        <h2>Mis Préstamos Activos</h2>
        <div class="table-responsive">
          <table border="1" class="table table-bordered tabla-apilada">
            <caption class="visually-hidden">Préstamos activos del usuario</caption>
            <thead>
              <tr>
                <th scope="col">Libro</th>
                <th scope="col">Fecha de Préstamo</th>
                <th scope="col">Fecha de Devolución</th>
                <th scope="col">Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td data-label="Libro">100 Años de soledad</td>
                <td data-label="Fecha de préstamo">10 / Marzo / 2025</td>
                <td data-label="Fecha de devolución">17 / Marzo / 2025</td>
                <td data-label="Estado">Por devolver (3 días)</td>
              </tr>
              <tr>
                <td data-label="Libro">Inglés con Felipe</td>
                <td data-label="Fecha de préstamo">05 / Marzo / 2025</td>
                <td data-label="Fecha de devolución">19 / Marzo / 2025</td>
                <td data-label="Estado">Por devolver (12 días)</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      

      <section class="bloque">
        <h2>Horarios de Atención</h2>
        <div class="table-responsive">
          <table border="1" class="table table-bordered w-auto">
            <tr>
              <td>Lunes - Viernes</td>
              <td>6:00am - 10:00am</td>
            </tr>
            <tr>
              <td>Sábado y Domingo</td>
              <td>Cerrado</td>
            </tr>
          </table>
        </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
