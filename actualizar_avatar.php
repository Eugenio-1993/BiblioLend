<?php
/**
 * actualizar_avatar.php
 * Guarda el avatar elegido por el usuario autenticado.
 * Responde siempre en JSON.
 */

require_once __DIR__ . '/autenticacion.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

/**
 * Envía una respuesta JSON y detiene la ejecución.
 */
function responderJson(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(405, [
        'ok' => false,
        'error' => 'Método no permitido.',
    ]);
}

if (!isset($_SESSION['usuario_id'], $_SESSION['rol'])) {
    responderJson(401, [
        'ok' => false,
        'error' => 'Tu sesión expiró. Inicia sesión de nuevo.',
    ]);
}

if (!tokenCsrfValido($_POST['csrf'] ?? null)) {
    responderJson(403, [
        'ok' => false,
        'error' => 'La sesión expiró. Recarga la página e inténtalo de nuevo.',
    ]);
}

$idAvatar = trim((string) ($_POST['avatar'] ?? ''));
$avatares = avataresDisponibles();

if ($idAvatar === '' || !isset($avatares[$idAvatar])) {
    responderJson(422, [
        'ok' => false,
        'error' => 'El avatar seleccionado no es válido.',
    ]);
}

$rutaAvatar = 'Imagenes/Avatares/' . $idAvatar . '.png';

try {
    require_once __DIR__ . '/conexion.php';

    $stmt = $pdo->prepare(
        'UPDATE usuario SET foto = :foto WHERE id_usuario = :id_usuario LIMIT 1'
    );
    $stmt->execute([
        'foto' => $rutaAvatar,
        'id_usuario' => (int) $_SESSION['usuario_id'],
    ]);

    $_SESSION['foto'] = $rutaAvatar;

    responderJson(200, [
        'ok' => true,
        'avatar' => $idAvatar,
        'nombre' => $avatares[$idAvatar],
        'src' => '../' . $rutaAvatar,
    ]);
} catch (PDOException $e) {
    error_log('Error al actualizar el avatar: ' . $e->getMessage());

    responderJson(500, [
        'ok' => false,
        'error' => 'No se pudo guardar el avatar. Inténtalo de nuevo.',
    ]);
}
