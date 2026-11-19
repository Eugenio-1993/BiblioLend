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
