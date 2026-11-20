<?php 
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';
require_once __DIR__ . '/../funciones_biblioteca.php';
requerirRol('estudiante');
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contáctanos - BiblioLend</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- Tipografías (mismas que el resto del sitio) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Nuestro CSS -->
    <link rel="stylesheet" href="../Css/BiblioLend.css" />
    <!-- Iconos de Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  </head>
  <body class="pagina-contactanos">
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
                <a class="nav-link" href="Sobre_nosotros.php">Sobre Nosotros</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" href="Contactanos.php">Contáctanos</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="Resenas.php">Reseñas</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="Catalogo.php">Catálogo</a>
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

    <main>
      <section class="bloque">
        <h2>Ponte en contacto con nosotros</h2>
        <p>
          Puedes contactarnos por medio de nuestros diferentes canales de
          comunicación disponibles. Te responderemos lo más rápido posible.
        </p>
      </section>

      <section class="bloque bloque-equipo" aria-labelledby="equipo-titulo">
        <div class="equipo-encabezado">
          <p class="equipo-eyebrow">CodeForge</p>
          <h2 id="equipo-titulo">Equipo de Desarrolladores</h2>
          <p class="equipo-intro">
            Las personas que diseñan, construyen y mantienen BiblioLend.
          </p>
        </div>

        <div class="row g-4">
          <div class="col-12 col-md-6 col-lg-3">
            <article class="contacto-card h-100" aria-labelledby="dev-eugenio">
              <div class="contacto-card__cabecera">
                <span class="contacto-card__numero" aria-hidden="true">01</span>
                <img
                  class="img_perfiles img_eugenio"
                  src="../Imagenes/Desarrolladores/Desarrollador_Eugenio-Morales.jpeg"
                  alt="Foto de Eugenio Morales"
                />
              </div>
              <div class="contacto-card__cuerpo">
                <h3 class="contacto-card__nombre" id="dev-eugenio">Eugenio Morales</h3>
                <p class="contacto-card__rol">Project Manager</p>
                <div class="contacto-card__contacto">
                  <p class="contacto-card__etiqueta">Contacto</p>
                  <ul class="contacto-redes" aria-label="Canales de contacto de Eugenio Morales">
                    <li class="contacto-redes--item">
                      <a href="mailto:moralescarrizoe@gmail.com" aria-label="Enviar correo a Eugenio Morales" title="Correo">
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li class="contacto-redes--item">
                      <a href="https://github.com/Eugenio-1993" target="_blank" rel="noopener" aria-label="GitHub de Eugenio Morales" title="GitHub">
                        <i class="fa-brands fa-github" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li class="contacto-redes--item">
                      <a href="https://wa.me/573227008063" target="_blank" rel="noopener" aria-label="WhatsApp de Eugenio Morales" title="WhatsApp">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </article>
          </div>

          <div class="col-12 col-md-6 col-lg-3">
            <article class="contacto-card h-100" aria-labelledby="dev-juan-camilo">
              <div class="contacto-card__cabecera">
                <span class="contacto-card__numero" aria-hidden="true">02</span>
                <img
                  class="img_perfiles"
                  src="../Imagenes/Desarrolladores/Desarrollador_Juan-Camilo.jpg"
                  alt="Foto de Juan Camilo Puerta"
                />
              </div>
              <div class="contacto-card__cuerpo">
                <h3 class="contacto-card__nombre" id="dev-juan-camilo">Juan Camilo Puerta S.</h3>
                <p class="contacto-card__rol">Coordinador FullStack y Analista de Datos</p>
                <div class="contacto-card__contacto">
                  <p class="contacto-card__etiqueta">Contacto</p>
                  <ul class="contacto-redes" aria-label="Canales de contacto de Juan Camilo Puerta">
                    <li class="contacto-redes--item">
                      <a href="mailto:juancamilo321312@gmail.com" target="_blank" rel="noopener" aria-label="Enviar correo a Juan Camilo Puerta" title="Correo">
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li class="contacto-redes--item">
                      <a href="https://github.com/JCamilo-CF" target="_blank" rel="noopener" aria-label="GitHub de Juan Camilo Puerta" title="GitHub">
                        <i class="fa-brands fa-github" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li class="contacto-redes--item">
                      <a href="https://wa.me/573145899535" target="_blank" rel="noopener" aria-label="WhatsApp de Juan Camilo Puerta" title="WhatsApp">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </article>
          </div>

          <div class="col-12 col-md-6 col-lg-3">
            <article class="contacto-card h-100" aria-labelledby="dev-hans">
              <div class="contacto-card__cabecera">
                <span class="contacto-card__numero" aria-hidden="true">03</span>
                <img
                  class="img_perfiles"
                  src="../Imagenes/Desarrolladores/Desarrollador_Hans-Renteria.jpeg"
                  alt="Foto de Hans Rentería"
                />
              </div>
              <div class="contacto-card__cuerpo">
                <h3 class="contacto-card__nombre" id="dev-hans">Hans Rentería A.</h3>
                <p class="contacto-card__rol">Especialista en Base de Datos</p>
                <div class="contacto-card__contacto">
                  <p class="contacto-card__etiqueta">Contacto</p>
                  <ul class="contacto-redes" aria-label="Canales de contacto de Hans Rentería">
                    <li class="contacto-redes--item">
                      <a href="mailto:hansalbornozz@gmail.com" target="_blank" rel="noopener" aria-label="Enviar correo a Hans Rentería" title="Correo">
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li class="contacto-redes--item">
                      <a href="https://github.com/HANS7310" target="_blank" rel="noopener" aria-label="GitHub de Hans Rentería" title="GitHub">
                        <i class="fa-brands fa-github" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li class="contacto-redes--item">
                      <a href="https://wa.me/573028311860" target="_blank" rel="noopener" aria-label="WhatsApp de Hans Rentería" title="WhatsApp">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </article>
          </div>

          <div class="col-12 col-md-6 col-lg-3">
            <article class="contacto-card h-100" aria-labelledby="dev-santiago">
              <div class="contacto-card__cabecera">
                <span class="contacto-card__numero" aria-hidden="true">04</span>
                <img
                  class="img_perfiles"
                  src="../Imagenes/Desarrolladores/Desarrollador_Santiago-Palomino.png"
                  alt="Foto de Santiago Palomino"
                />
              </div>
              <div class="contacto-card__cuerpo">
                <h3 class="contacto-card__nombre" id="dev-santiago">Santiago Palomino M.</h3>
                <p class="contacto-card__rol">Desarrollador destacado en Frontend</p>
                <div class="contacto-card__contacto">
                  <p class="contacto-card__etiqueta">Contacto</p>
                  <ul class="contacto-redes" aria-label="Canales de contacto de Santiago Palomino">
                    <li class="contacto-redes--item">
                      <a href="mailto:santiagopalomino345@gmail.com" target="_blank" rel="noopener" aria-label="Enviar correo a Santiago Palomino" title="Correo">
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li class="contacto-redes--item">
                      <a href="https://github.com/santiagopalomino345-rgb" target="_blank" rel="noopener" aria-label="GitHub de Santiago Palomino" title="GitHub">
                        <i class="fa-brands fa-github" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li class="contacto-redes--item">
                      <a href="https://wa.me/573145346164" target="_blank" rel="noopener" aria-label="WhatsApp de Santiago Palomino" title="WhatsApp">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </article>
          </div>
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
            <li><a href="Contactanos.php">Nuestro equipo</a></li>
            <li><a href="Sobre_nosotros.php">Sobre nosotros</a></li>
          </ul>
        </nav>

        <!-- Columna 3: contacto -->
        <section class="site-footer__contact" aria-labelledby="footer-contact-title">
          <h2 class="site-footer__title" id="footer-contact-title">Contacto</h2>
          <address class="site-footer__address">
            <p>
              <a
                href="https://maps.google.com/?q=6.2907713,-75.5595566"
                target="_blank"
                rel="noopener"
              >
                Cra. 50B #97a-30, Santa Cruz<br />
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
