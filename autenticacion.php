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
?>