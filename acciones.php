<?php
/**
 * acciones.php
 * Punto único para ejecutar las acciones de préstamos y reseñas.
 *
 * Responde en JSON cuando la petición viene de JavaScript (Catálogo)
 * y redirige con un mensaje cuando viene de un formulario normal.
 */

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/autenticacion.php';
require_once __DIR__ . '/funciones_biblioteca.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$esAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
    || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');

// Páginas a las que se puede volver después de una acción con formulario.
const PAGINAS_RETORNO = [
    'Panel_principal.php',
    'Panel_bibliotecario.php',
    'Panel_admin.php',
    'Catalogo.php',
    'Cuenta_usuarios.php',
    'Resenas.php',
];

function responderAccion(array $resultado, bool $esAjax, string $destino): void
{
    if ($esAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        exit();
    }

    guardarFlash(
        !empty($resultado['ok']) ? 'exito' : 'error',
        (string) ($resultado['mensaje'] ?? 'No se pudo completar la acción.')
    );

    header('Location: ' . $destino);
    exit();
}

function paginaPorDefecto(string $rol): string
{
    switch ($rol) {
        case 'estudiante':
            return 'Panel_principal.php';
        case 'bibliotecario':
            return 'Panel_bibliotecario.php';
        case 'administrador':
            return 'Panel_admin.php';
        default:
            return 'Panel_login.php';
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responderAccion(resultado(false, 'Método no permitido.'), $esAjax, 'Paginas/Panel_login.php');
}

$rol = (string) ($_SESSION['rol'] ?? '');
$idUsuario = (int) ($_SESSION['usuario_id'] ?? 0);

if ($idUsuario <= 0) {
    if ($esAjax) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(resultado(false, 'Tu sesión expiró. Vuelve a iniciar sesión.'), JSON_UNESCAPED_UNICODE);
        exit();
    }

    $_SESSION['error'] = 'Debes iniciar sesión para continuar.';
    header('Location: Paginas/Panel_login.php');
    exit();
}

// Página de retorno (solo se aceptan páginas conocidas).
$volver = basename((string) ($_POST['volver'] ?? ''));
if (!in_array($volver, PAGINAS_RETORNO, true)) {
    $volver = paginaPorDefecto($rol);
}
$destino = 'Paginas/' . $volver;

// Validación CSRF para todas las acciones.
if (!csrfValido($_POST['csrf_token'] ?? null)) {
    if ($esAjax) {
        http_response_code(419);
    }

    responderAccion(
        resultado(false, 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.'),
        $esAjax,
        $destino
    );
}

$accion = (string) ($_POST['accion'] ?? '');
$resultado = resultado(false, 'Acción no reconocida.');

switch ($accion) {
    case 'solicitar_prestamo':
        if ($rol !== 'estudiante') {
            $resultado = resultado(false, 'Solo los estudiantes pueden solicitar préstamos.');
            break;
        }

        $idLibro = filter_var($_POST['id_libro'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($idLibro === false) {
            $resultado = resultado(false, 'El libro indicado no es válido.');
            break;
        }

        $resultado = solicitarPrestamo($pdo, $idUsuario, (int) $idLibro);
        break;

    case 'cancelar_solicitud':
        if ($rol !== 'estudiante') {
            $resultado = resultado(false, 'Solo los estudiantes pueden cancelar sus solicitudes.');
            break;
        }

        $idLibro = filter_var($_POST['id_libro'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($idLibro === false) {
            $resultado = resultado(false, 'El libro indicado no es válido.');
            break;
        }

        $resultado = cancelarSolicitud($pdo, $idUsuario, (int) $idLibro);
        break;

    case 'solicitar_devolucion':
        if ($rol !== 'estudiante') {
            $resultado = resultado(false, 'Solo los estudiantes pueden solicitar devoluciones.');
            break;
        }

        $idPrestamo = filter_var($_POST['id_prestamo'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($idPrestamo === false) {
            $resultado = resultado(false, 'El préstamo indicado no es válido.');
            break;
        }

        $resultado = solicitarDevolucion($pdo, $idUsuario, (int) $idPrestamo);
        break;

    case 'validar_entrega':
        if (!in_array($rol, ['bibliotecario', 'administrador'], true)) {
            $resultado = resultado(false, 'No tienes permisos para validar entregas.');
            break;
        }

        $codigo = trim((string) ($_POST['codigo_ticket'] ?? ''));
        if ($codigo === '') {
            $resultado = resultado(false, 'Escribe el código del ticket.');
            break;
        }

        $resultado = validarEntrega($pdo, $codigo);
        break;

    case 'validar_devolucion':
        if (!in_array($rol, ['bibliotecario', 'administrador'], true)) {
            $resultado = resultado(false, 'No tienes permisos para validar devoluciones.');
            break;
        }

        $codigo = trim((string) ($_POST['codigo_ticket'] ?? ''));
        if ($codigo === '') {
            $resultado = resultado(false, 'Escribe el código del ticket.');
            break;
        }

        $resultado = validarDevolucion($pdo, $codigo);
        break;

    case 'guardar_resena':
        $texto = (string) ($_POST['resena'] ?? '');
        $resultado = guardarResena($pdo, $idUsuario, $texto);
        break;
}

responderAccion($resultado, $esAjax, $destino);
