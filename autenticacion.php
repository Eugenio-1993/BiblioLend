<?php // autenticacion.php - Incluir en la primera línea de cualquier página protegida

function verificarSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['error'] = "Debes iniciar sesión para acceder.";
        header("Location: Panel_login.php");
        exit();
    }
}

/**
 * Avatares disponibles para la cuenta de usuario.
 * La clave es el identificador que se guarda en la columna usuario.foto.
 */
function avataresDisponibles(): array
{
    return [
        'the-striver'        => 'The Striver',
        'focused-tiger'      => 'Focused Tiger',
        'driven-lion'        => 'Driven Lion',
        'the-powerhouse'     => 'The Powerhouse',
        'relentless-cheetah' => 'Relentless Cheetah',
        'the-contender'      => 'The Contender',
        'fierce-panther'     => 'Fierce Panther',
        'grinding-bear'      => 'Grinding Bear',
        'iron-rhino'         => 'Iron Rhino',
        'elite-eagle'        => 'Elite Eagle',
    ];
}

/**
 * Avatar que se muestra mientras el usuario no ha elegido uno.
 */
function avatarPredeterminado(): string
{
    return 'Imagenes/Imagen_de_perfil.jpg';
}

/**
 * Devuelve el identificador del avatar guardado en la sesión, o '' si
 * todavía no tiene uno válido.
 */
function avatarActualId(): string
{
    $foto = (string) ($_SESSION['foto'] ?? '');

    if (preg_match('#^Imagenes/Avatares/([a-z0-9-]+)\.png$#', $foto, $coincidencia)) {
        $idAvatar = $coincidencia[1];

        if (isset(avataresDisponibles()[$idAvatar])) {
            return $idAvatar;
        }
    }

    return '';
}

/**
 * Convierte el valor guardado en usuario.foto en una ruta segura.
 * Si la foto no corresponde a uno de los avatares disponibles, usa la imagen
 * predeterminada. Sirve también para mostrar el avatar de otros usuarios.
 */
function rutaAvatarDesdeFoto(?string $foto): string
{
    $foto = (string) $foto;

    if (preg_match('#^Imagenes/Avatares/([a-z0-9-]+)\.png$#', $foto, $coincidencia)) {
        $idAvatar = $coincidencia[1];

        if (isset(avataresDisponibles()[$idAvatar])) {
            return 'Imagenes/Avatares/' . $idAvatar . '.png';
        }
    }

    return avatarPredeterminado();
}

/**
 * Ruta relativa del avatar del usuario desde la raíz del proyecto.
 */
function rutaAvatarUsuario(): string
{
    return rutaAvatarDesdeFoto($_SESSION['foto'] ?? '');
}

/**
 * Token simple para proteger formularios enviados por AJAX.
 */
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

/**
 * Comprueba que el token recibido coincida con el de la sesión.
 */
function tokenCsrfValido($token): bool
{
    return is_string($token)
        && $token !== ''
        && isset($_SESSION['csrf_token'])
        && hash_equals((string) $_SESSION['csrf_token'], $token);
}

// Acepta un rol ('estudiante') o varios (['estudiante', 'bibliotecario'])
function requerirRol($rolesPermitidos) {
    verificarSesion();

    $rolesPermitidos = (array) $rolesPermitidos;

    // Si el rol en sesión no está entre los permitidos para esta página
    if (!in_array($_SESSION['rol'], $rolesPermitidos, true)) {
        $_SESSION['error'] = "No tienes permisos para acceder a esta sección.";

        switch ($_SESSION['rol']) {
            case 'administrador':
                header("Location: Panel_admin.php");
                break;
            case 'estudiante':
                header("Location: Panel_principal.php");
                break;
            case 'bibliotecario':
                header("Location: Panel_bibliotecario.php");
                break;
            default:
                header("Location: Panel_login.php");
                break;
        }
        exit();
    }
}

// ------------------------------------------------------------
// Seguridad de formularios (CSRF) y mensajes entre páginas
// ------------------------------------------------------------

function csrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfCampo(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrfValido(?string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return is_string($token)
        && $token !== ''
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function guardarFlash(string $tipo, string $mensaje): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['flash'] = [
        'tipo'    => $tipo,
        'mensaje' => $mensaje,
    ];
}

function tomarFlash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

// Imprime el mensaje que dejó la acción anterior (si existe).
function mostrarFlash(): void
{
    $flash = tomarFlash();

    if ($flash === null) {
        return;
    }

    $clase = ($flash['tipo'] ?? '') === 'exito' ? 'alert-success' : 'alert-danger';
    $texto = htmlspecialchars((string) ($flash['mensaje'] ?? ''), ENT_QUOTES, 'UTF-8');

    echo '<div class="alert ' . $clase . ' mb-4" role="alert">' . $texto . '</div>';
}
?>
