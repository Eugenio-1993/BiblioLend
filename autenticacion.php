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
