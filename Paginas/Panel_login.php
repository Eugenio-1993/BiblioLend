<?php
session_start();
require_once __DIR__ . '/../conexion.php';

$error = '';
$correo = '';
$rolSeleccionado = 'estudiante';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Los nombres deben coincidir con los campos del formulario (name="email", "password", "rol").
    $correo = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // No se recorta: los espacios pueden ser parte de la contraseña.
    $rolSeleccionado = trim($_POST['rol'] ?? '');

    $rolesValidos = ['estudiante', 'bibliotecario', 'administrador'];
    $paneles = [
        'estudiante' => 'Panel_principal.html',
        'bibliotecario' => 'Panel_bibliotecario.html',
        'administrador' => 'Panel_admin.html',
    ];

    if ($correo === '' || $password === '' || $rolSeleccionado === '') {
        $error = "Debes completar todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Ingresa un correo institucional válido.";
    } elseif (!in_array($rolSeleccionado, $rolesValidos, true)) {
        $error = "Selecciona un tipo de usuario válido.";
    } else {
        try {
            // La tabla `login` guarda las credenciales y se une con `usuario` por el documento.
            $stmt = $pdo->prepare(
                "SELECT l.correo, l.contrasena, l.rol, l.Numero_de_documento,
                        u.Primer_nombre, u.Segundo_nombre,
                        u.Primer_apellido, u.Segundo_apellido
                 FROM login AS l
                 LEFT JOIN usuario AS u
                        ON u.Numero_de_documento = l.Numero_de_documento
                 WHERE l.correo = :correo
                 LIMIT 1"
            );
            $stmt->execute(['correo' => $correo]);
            $user = $stmt->fetch();

            if (
                $user &&
                $user['rol'] === $rolSeleccionado &&
                password_verify($password, $user['contrasena'])
            ) {
                // 🛡️ Prevenir ataques de fijación de sesión
                session_regenerate_id(true);

                $nombre = trim(implode(' ', array_filter([
                    $user['Primer_nombre'] ?? '',
                    $user['Segundo_nombre'] ?? '',
                    $user['Primer_apellido'] ?? '',
                    $user['Segundo_apellido'] ?? '',
                ])));

                $_SESSION['usuario_id'] = $user['Numero_de_documento'] ?? $user['correo'];
                $_SESSION['nombre'] = $nombre !== '' ? $nombre : $user['correo'];
                $_SESSION['correo'] = $user['correo'];
                $_SESSION['rol'] = $user['rol'];

                // Redirección al panel que corresponde al rol.
                header("Location: " . $paneles[$user['rol']]);
                exit();
            }

            $error = "Correo, contraseña o tipo de usuario incorrectos.";
        } catch (PDOException $e) {
            error_log("Error en la autenticación: " . $e->getMessage());
            $error = "No se pudo iniciar sesión. Inténtalo de nuevo más tarde.";
        }
    }
}
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bienvenido a BiblioLend - Iniciar Sesión</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- Fuente de Google -->
    <!-- Tipografías (mismas que la landing) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../Css/BiblioLend.css" />
  </head>
  <body class="pagina-panel-login">
    <main class="login-wrapper">
      <!-- Panel izquierdo -->
      <aside class="panel-left" aria-label="Presentación de BiblioLend">
        <div class="panel-left__deco"></div>
        <div class="panel-left__content">
          <img
            src="../Imagenes/Logo_bibliolend.png"
            alt="Logo BiblioLend"
            class="logo-img"
            width="90"
            height="90"
          />
          <p class="brand-title">BiblioLend</p>
          <p class="brand-tagline">
            Accede al catálogo escolar, libros y recursos académicos de tu
            institución educativa, todo en un solo lugar.
          </p>
        </div>
        <div class="panel-left__footer">
          <p>&copy; 2026 BiblioLend · CodeForge</p>
        </div>
      </aside>

      <!-- Panel derecho -->
      <section class="panel-right" aria-labelledby="titulo-login">
        <div class="form-wrapper">
          <p class="form-brand">Inicia Sesión con tu cuenta de BiblioLend</p>
          <h1 class="form-title" id="titulo-login">¡Bienvenido!</h1>

          <?php if ($error !== ''): ?>
            <p class="field-error" role="alert">
              <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
          <?php endif; ?>

          <form action="Panel_login.php" method="post" id="iniciarSesion">
            <div class="field-group">
              <label for="rol">Tipo de Usuario</label>
              <select class="field-select" id="rol" name="rol" required>
                <option value="" disabled>Selecciona tu rol</option>
                <option value="estudiante" <?= $rolSeleccionado === 'estudiante' ? 'selected' : '' ?>>Estudiante</option>
                <option value="bibliotecario" <?= $rolSeleccionado === 'bibliotecario' ? 'selected' : '' ?>>Bibliotecario</option>
                <option value="administrador" <?= $rolSeleccionado === 'administrador' ? 'selected' : '' ?>>Administrador</option>
              </select>
            </div>

            <div class="field-group">
              <label for="email">Correo Institucional</label>
              <input
                class="field-input"
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>"
                autocomplete="username"
                inputmode="email"
                required
                placeholder="usuario@ierepublicadehonduras.edu.co"
              />
            </div>

            <div class="field-group">
              <label for="password">Contraseña</label>
              <input
                type="password"
                id="password"
                name="password"
                class="field-input"
                autocomplete="current-password"
                placeholder="Ingrese su contraseña"
                required
              />
            </div>

            <button type="submit" class="btn btn-login">
              Entrar al Sistema
            </button>
          </form>
          
          <p class="forgot">
            <a href="Olvide_mi_contrasena.html">¿Olvidaste tu contraseña? Haz clic aquí</a>
          </p>
        </div>
      </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
