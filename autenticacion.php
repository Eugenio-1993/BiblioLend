<?php
/**
 * autenticacion.php
 * Funciones compartidas para proteger las páginas por sesión y rol.
 * Se incluye desde las páginas ubicadas en /Paginas.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Paneles principales disponibles para cada rol.
 */
function panelesPorRol(): array
{
    return [
        'estudiante'    => 'Panel_principal.php',
        'bibliotecario' => 'Panel_bibliotecario.php',
        'administrador' => 'Panel_admin.php',
    ];
}

/**
 * Construye una URL absoluta hacia un archivo de /Paginas.
 * Funciona tanto si el proyecto está en la raíz del servidor como en una subcarpeta.
 */
function urlPagina(string $archivo): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $directorio = rtrim(dirname($script), '/');

    // Las páginas protegidas viven en /Paginas. Este respaldo permite
    // usar las mismas funciones desde archivos ubicados en la raíz.
    if (basename($directorio) !== 'Paginas') {
        $directorio .= '/Paginas';
    }

    return $directorio . '/' . ltrim($archivo, '/');
}

/**
 * Redirige y detiene la ejecución.
 */
function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit();
}

/**
 * Comprueba que exista una sesión iniciada.
 * Si no existe, guarda el mensaje y envía al login.
 */
function verificarSesion(): void
{
    if (!isset($_SESSION['usuario_id'], $_SESSION['rol'])) {
        $_SESSION['error'] = 'Debes iniciar sesión para acceder.';
        redirigir(urlPagina('Panel_login.php'));
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
 * Ruta relativa del avatar del usuario desde la raíz del proyecto.
 */
function rutaAvatarUsuario(): string
{
    $idAvatar = avatarActualId();

    if ($idAvatar === '') {
        return avatarPredeterminado();
    }

    return 'Imagenes/Avatares/' . $idAvatar . '.png';
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

/**
 * Exige que el usuario tenga uno de los roles permitidos.
 * Acepta un rol ('estudiante') o varios (['estudiante', 'bibliotecario']).
 */
function requerirRol($rolesPermitidos): void
{
    verificarSesion();

    $rolesPermitidos = (array) $rolesPermitidos;
    $paneles = panelesPorRol();
    $rolActual = (string) $_SESSION['rol'];

    // Si el rol guardado no corresponde a ningún panel conocido,
    // se invalida la sesión para evitar estados inconsistentes.
    if (!isset($paneles[$rolActual])) {
        $_SESSION = [];
        $_SESSION['error'] = 'Tu sesión no es válida. Inicia sesión de nuevo.';
        redirigir(urlPagina('Panel_login.php'));
    }

    // Si el rol no tiene permiso, vuelve a su propio panel.
    if (!in_array($rolActual, $rolesPermitidos, true)) {
        redirigir(urlPagina($paneles[$rolActual]));
    }
}
