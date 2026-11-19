<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';
requerirRol('administrador');
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
        <a href="Panel_login.php" class="btn btn-outline-secondary">
     Volver al inicio
  </a>
      </div>
    </header>

    <main class="admin-main">
      <section class="dashboard" aria-labelledby="dashboard-title">
        <h2 id="dashboard-title" class="dashboard__title">Resumen General</h2>

        <div class="dashboard__cards">
          <article class="card-stat">
            <h3 class="card-stat__title">Usuarios registrados</h3>
            <p class="card-stat__value">1,248</p>
          </article>

          <article class="card-stat">
            <h3 class="card-stat__title">Total libros disponibles</h3>
            <p class="card-stat__value">532</p>
          </article>

          <article class="card-stat">
            <h3 class="card-stat__title">Préstamos activos</h3>
            <p class="card-stat__value">89</p>
          </article>

          <article class="card-stat">
            <h3 class="card-stat__title">Reseñas activas</h3>
            <p class="card-stat__value">34</p>
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
              <tr>
                <td data-label="Título">1984</td>
                <td data-label="Autor">George Orwell</td>
                <td data-label="Estado">
                  <span
                    class="table-books__status table-books__status--available"
                  >
                    Disponible
                  </span>
                </td>
                <td data-label="Acciones">
                  <button class="table-books__action">Editar</button>
                  <button
                    class="table-books__action table-books__action--danger"
                  >
                    Eliminar
                  </button>
                </td>
              </tr>

              <tr>
                <td data-label="Título">El Principito</td>
                <td data-label="Autor">Antoine de Saint-Exupéry</td>
                <td data-label="Estado">
                  <span
                    class="table-books__status table-books__status--borrowed"
                  >
                    Prestado
                  </span>
                </td>
                <td data-label="Acciones">
                  <button class="table-books__action">Editar</button>
                  <button
                    class="table-books__action table-books__action--danger"
                  >
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
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
