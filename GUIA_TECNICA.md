# Guía técnica de BiblioLend

Documento de referencia para entender **cómo está construido BiblioLend** y cuáles son las piezas que más importan al momento de mantenerlo o extenderlo.

No pretende explicar cada línea del proyecto. Se concentra en:

- Las conexiones entre PHP, sesiones, base de datos y endpoints.
- Los scripts de JavaScript y su contrato con el HTML.
- La arquitectura de estilos CSS y los patrones de HTML que la sostienen.

> Los fragmentos de código que aparecen aquí están tomados del código actual del repositorio y, en algunos casos, se abreviaron con `…` para resaltar lo importante.

---

## Contenido

1. [Panorama general](#1-panorama-general)
2. [Capa PHP](#2-capa-php)
   - [2.1 Conexión PDO](#21-conexión-pdo-conexionphp)
   - [2.2 Sesiones, login y roles](#22-sesiones-login-y-roles)
   - [2.3 CSRF y mensajes flash](#23-csrf-y-mensajes-flash)
   - [2.4 El controlador `acciones.php`](#24-el-controlador-accionesphp)
   - [2.5 El endpoint `actualizar_avatar.php`](#25-el-endpoint-actualizar_avatarphp)
   - [2.6 Lógica de préstamos](#26-lógica-de-préstamos)
3. [JavaScript](#3-javascript)
   - [3.1 `Catalogo.js`](#31-catalogojs)
   - [3.2 `Cuenta_usuarios.js`](#32-cuenta_usuariosjs)
   - [3.3 `Olvide_mi_contrasena.js`](#33-olvide_mi_contrasenajs)
   - [3.4 Contratos de integración](#34-contratos-de-integración)
4. [HTML y CSS](#4-html-y-css)
   - [4.1 Estructura HTML](#41-estructura-html)
   - [4.2 Tokens y arquitectura CSS](#42-tokens-y-arquitectura-css)
   - [4.3 Componentes y estados](#43-componentes-y-estados)
   - [4.4 Responsive y accesibilidad](#44-responsive-y-accesibilidad)
5. [Flujos de extremo a extremo](#5-flujos-de-extremo-a-extremo)
6. [Recetario para extender el proyecto](#6-recetario-para-extender-el-proyecto)
7. [Observaciones y deuda técnica](#7-observaciones-y-deuda-técnica)
8. [Glosario rápido](#8-glosario-rápido)

---

## 1. Panorama general

BiblioLend es una aplicación web escolar de gestión de biblioteca. No usa un framework PHP ni un empaquetador de JavaScript: cada página es un archivo PHP que mezcla controlador y vista, y el navegador carga Bootstrap 5.3 desde CDN más una única hoja de estilos propia.

| Pieza | Tecnología |
|---|---|
| Servidor | PHP 8+ |
| Base de datos | MySQL / MariaDB |
| Acceso a datos | PDO con consultas preparadas |
| Estilos | Bootstrap 5.3 + `Css/BiblioLend.css` |
| JavaScript | Vanilla JS, sin módulos ni build |
| Tipografías | DM Sans (texto) y Playfair Display (títulos) |

### Flujo de una petición

```text
Navegador
   │
   ├─ Página normal ──► Paginas/*.php
   │                      │
   │                      ├─ require conexion.php      (crea $pdo)
   │                      ├─ require autenticacion.php (sesión, roles, CSRF)
   │                      ├─ require funciones_biblioteca.php (consultas y acciones)
   │                      ├─ requerirRol(...)
   │                      └─ HTML + datos ya renderizados
   │
   └─ Acción ──────────► acciones.php ──► funciones_biblioteca.php ──► MySQL
                          │
                          ├─ AJAX  → JSON
                          └─ Form  → redirect + mensaje flash
```

### Archivos y responsabilidades

| Archivo | Responsabilidad |
|---|---|
| `conexion.php` | Crea la conexión PDO (`$pdo`). |
| `autenticacion.php` | Sesión, roles, avatares, CSRF y mensajes flash. |
| `funciones_biblioteca.php` | Consultas, contadores, reglas y acciones de préstamos. |
| `acciones.php` | Controlador único para acciones POST de préstamos y reseñas. |
| `actualizar_avatar.php` | Endpoint JSON para guardar el avatar. |
| `logout.php` | Cierra la sesión y redirige al login. |
| `hash.php` | Script de mantenimiento para convertir contraseñas viejas a hash. |
| `Paginas/*.php` | Páginas principales: paneles, catálogo, cuenta y reseñas. |
| `Paginas/*.html` | Páginas estáticas (contacto y recuperación de contraseña). |
| `JavaScript/*.js` | Interacciones de catálogo, cuenta y recuperación. |
| `Css/BiblioLend.css` | Toda la capa visual del sitio. |

### Convención de inclusión

Las páginas viven en `Paginas/`, así que siempre incluyen los archivos de la raíz con `__DIR__`:

```php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../autenticacion.php';
require_once __DIR__ . '/../funciones_biblioteca.php';
```

Esto evita depender del directorio de trabajo del proceso PHP. Es la primera regla que debe respetar cualquier página nueva.

---

## 2. Capa PHP

### 2.1 Conexión PDO (`conexion.php`)

El archivo completo es corto, pero cada opción cumple una función:

```php
$host = '127.0.0.1';
$db   = 'biblioteca';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $opciones);
} catch (PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    throw new PDOException("No se pudo conectar con la base de datos.");
}
```

Qué importa de esta configuración:

| Opción | Efecto |
|---|---|
| `utf8mb4` | Soporta tildes, emojis y cualquier carácter Unicode. |
| `ERRMODE_EXCEPTION` | Un error SQL lanza excepción en vez de devolver `false` silencioso. |
| `FETCH_ASSOC` | Las consultas devuelven arreglos asociativos (`$fila['titulo']`). |
| `EMULATE_PREPARES = false` | Fuerza consultas preparadas reales en el servidor. |
| `error_log(...)` | El detalle técnico queda en el log, no en la respuesta. |

Consecuencias prácticas:

- El resto del código puede usar `fetch()`, `fetchAll()` y `fetchColumn()` sin indicar el modo cada vez.
- Las funciones de acción de `funciones_biblioteca.php` capturan los errores con `try/catch` y devuelven un mensaje genérico; los contadores y listados no lo hacen, así que una excepción de conexión llega sin capturar al punto de entrada.
- La conexión se crea una sola vez por petición y queda disponible como `$pdo` en el ámbito global del archivo incluido.

> **Observación:** las credenciales están escritas directamente en `conexion.php` (`root` sin contraseña). Es aceptable para desarrollo local, pero en producción deberían venir de variables de entorno o de un archivo de configuración fuera del repositorio. Además, el comentario dice que las páginas muestran un mensaje amable, pero no hay un manejador global de excepciones: una caída de conexión produciría un error no capturado salvo que las páginas lo manejen.

### 2.2 Sesiones, login y roles

#### Inicio de sesión

`Paginas/Panel_login.php` es el punto de entrada. El flujo real es:

```text
POST email + password + rol
   │
   ├─ Validar campos vacíos y formato del correo
   ├─ Buscar usuario por correo (LIMIT 1)
   ├─ Comparar rol elegido con el rol de la base
   ├─ password_verify(password, hash_guardado)
   ├─ session_regenerate_id(true)      ← evita fijación de sesión
   ├─ Guardar datos en $_SESSION
   └─ Redirigir al panel según el rol
```

Fragmento clave:

```php
if (
    $user &&
    $user['rol'] === $rolSeleccionado &&
    password_verify($password, $user['contrasena'])
) {
    session_regenerate_id(true);

    $_SESSION['usuario_id'] = $user['id_usuario'];
    $_SESSION['nombre']     = $nombre !== '' ? $nombre : $user['correo'];
    $_SESSION['correo']     = $user['correo'];
    $_SESSION['rol']        = $user['rol'];
    $_SESSION['foto']       = $user['foto'] ?? '';

    header("Location: " . $paneles[$user['rol']]);
    exit();
}
```

Puntos importantes:

- La contraseña **no se recorta** (`trim`), porque los espacios pueden ser parte de ella.
- Se valida que el rol seleccionado coincida con el rol guardado en la base. No basta con saber la contraseña.
- `password_verify()` permite convivir con hashes generados por `password_hash()`; `hash.php` existe justamente para migrar contraseñas viejas en texto plano.
- `session_regenerate_id(true)` cambia el ID de sesión después del login. Sin esto, un atacante podría fijar una sesión antes del inicio y reutilizarla después.
- Los datos de sesión que usa el resto del sitio son: `usuario_id`, `nombre`, `correo`, `rol` y `foto`.

#### Protección de páginas

`autenticacion.php` ofrece dos niveles:

```php
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
```

```php
function requerirRol($rolesPermitidos) {
    verificarSesion();

    $rolesPermitidos = (array) $rolesPermitidos;

    if (!in_array($_SESSION['rol'], $rolesPermitidos, true)) {
        $_SESSION['error'] = "No tienes permisos para acceder a esta sección.";

        switch ($_SESSION['rol']) {
            case 'administrador':  header("Location: Panel_admin.php"); break;
            case 'estudiante':     header("Location: Panel_principal.php"); break;
            case 'bibliotecario':  header("Location: Panel_bibliotecario.php"); break;
            default:               header("Location: Panel_login.php"); break;
        }
        exit();
    }
}
```

`requerirRol` acepta un rol o un arreglo:

```php
requerirRol('estudiante');
requerirRol(['estudiante', 'bibliotecario', 'administrador']);
```

La redirección es relativa a `Paginas/`, que es donde viven todas las páginas protegidas. Por eso el login puede redirigir a `Panel_principal.php` sin prefijo.

#### Cierre de sesión

`logout.php` vacía `$_SESSION`, borra la cookie de sesión y destruye la sesión:

```php
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
header("Location: Paginas/Panel_login.php");
```

Como `logout.php` está en la raíz, su redirección sí incluye el prefijo `Paginas/`.

### 2.3 CSRF y mensajes flash

#### Por qué existe

CSRF (Cross-Site Request Forgery) es un ataque en el que otra web hace que el navegador del usuario envíe una petición autenticada sin que este lo sepa. La defensa es incluir un token impredecible que solo el servidor conoce y validar que llegue en cada POST.

#### Dos pares de funciones equivalentes

`autenticacion.php` tiene dos APIs que trabajan sobre la misma clave de sesión (`$_SESSION['csrf_token']`):

| API | Funciones | Uso actual |
|---|---|---|
| Corta | `tokenCsrf()` / `tokenCsrfValido($token)` | Cuenta de usuario y `actualizar_avatar.php`. |
| Larga | `csrfToken()` / `csrfCampo()` / `csrfValido($token)` | Catálogo (meta etiqueta), paneles y `acciones.php`. |

No hay diferencia de seguridad entre ambas; la duplicación es histórica. Un cambio futuro podría unificarlas sin tocar la lógica.

Generación y validación:

```php
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function tokenCsrfValido($token): bool
{
    return is_string($token)
        && $token !== ''
        && isset($_SESSION['csrf_token'])
        && hash_equals((string) $_SESSION['csrf_token'], $token);
}
```

Detalles que importan:

- `random_bytes(32)` genera 32 bytes criptográficamente seguros; `bin2hex` produce un token de 64 caracteres.
- `hash_equals()` compara en tiempo constante y evita ataques de temporización.
- El token se guarda en sesión, no en una cookie independiente.

#### Tres formas de transportar el token

| Escenario | Dónde se imprime | Dónde se valida |
|---|---|---|
| Formulario HTML | `<?= csrfCampo() ?>` | `csrfValido($_POST['csrf_token'])` en `acciones.php` |
| Petición `fetch` del catálogo | `<meta name="csrf-token">` y se lee con `document.querySelector(...)` | `acciones.php` |
| Selector de avatar | `data-csrf` en el modal y se envía con `FormData` | `actualizar_avatar.php` |

Ejemplo de campo oculto:

```php
function csrfCampo(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}
```

En el catálogo:

```html
<meta name="csrf-token" content="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>" />
```

En el modal de avatares:

```html
<div class="modal fade" id="modalAvatares" data-csrf="<?= htmlspecialchars($csrfAvatar, ENT_QUOTES, 'UTF-8') ?>">
```

#### Mensajes flash

Un flash es un mensaje de una sola lectura que sobrevive a una redirección:

```php
guardarFlash('exito', 'Solicitud registrada.');
// …redirect…
$flash = tomarFlash();   // lo devuelve y lo borra
mostrarFlash();          // imprime el <div class="alert ...">
```

`mostrarFlash()` genera `alert-success` para `exito` y `alert-danger` para cualquier otro tipo. Se llama al inicio del contenido de cada panel, del catálogo, de la cuenta y de reseñas.

Importante: los mensajes flash usan `$_SESSION['flash']`, mientras que los errores de acceso directo (`requerirRol`) usan `$_SESSION['error']`. El login lee y borra `$_SESSION['error']` manualmente.

### 2.4 El controlador `acciones.php`

`acciones.php` es el punto único para las acciones POST de préstamos y reseñas. Centralizarlas evita repetir validaciones de sesión, rol y CSRF en cada formulario.

#### Secuencia de control

```text
POST a acciones.php
   │
   ├─ ¿Método POST?             No → 405
   ├─ ¿Sesión con usuario_id?   No → 401 JSON o redirect al login
   ├─ Normalizar "volver"       → solo páginas de PAGINAS_RETORNO
   ├─ Validar CSRF              No → 419 JSON o redirect con flash
   ├─ Leer "accion"
   └─ switch:
        ├─ solicitar_prestamo
        ├─ cancelar_solicitud
        ├─ solicitar_devolucion
        ├─ validar_entrega
        ├─ validar_devolucion
        └─ guardar_resena
```

#### Detección de AJAX

```php
$esAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
    || str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
```

El catálogo envía ambas cabeceras desde `fetch`. Los formularios normales no las envían, así que reciben una redirección.

#### Lista blanca de retorno

```php
const PAGINAS_RETORNO = [
    'Panel_principal.php',
    'Panel_bibliotecario.php',
    'Panel_admin.php',
    'Catalogo.php',
    'Cuenta_usuarios.php',
    'Resenas.php',
];

$volver = basename((string) ($_POST['volver'] ?? ''));
if (!in_array($volver, PAGINAS_RETORNO, true)) {
    $volver = paginaPorDefecto($rol);
}
$destino = 'Paginas/' . $volver;
```

Esto evita redirecciones abiertas: un atacante no puede usar `volver=https://sitio-malicioso.com`. `basename()` elimina cualquier ruta que intente colarse.

#### Matriz de permisos

| Acción | Estudiante | Bibliotecario | Administrador |
|---|---:|---:|---:|
| `solicitar_prestamo` | ✅ | ❌ | ❌ |
| `cancelar_solicitud` | ✅ | ❌ | ❌ |
| `solicitar_devolucion` | ✅ | ❌ | ❌ |
| `validar_entrega` | ❌ | ✅ | ✅ |
| `validar_devolucion` | ❌ | ✅ | ✅ |
| `guardar_resena` | ✅ | ✅ | ✅ |

> `guardar_resena` no tiene una comprobación explícita de rol: cualquier sesión válida puede enviar una reseña. Si en el futuro las reseñas deben ser solo de estudiantes, hay que agregar el `if` correspondiente en `acciones.php`.

#### Validación de entrada

Los identificadores se validan con `filter_var`, no con conversiones simples:

```php
$idLibro = filter_var($_POST['id_libro'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($idLibro === false) {
    $resultado = resultado(false, 'El libro indicado no es válido.');
    break;
}
```

Esto rechaza valores como `0`, negativos, arreglos o texto. El código del ticket se limpia con `trim()` y se normaliza a mayúsculas dentro de las funciones de validación.

#### Respuesta dual

```php
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
```

Contrato de respuesta:

```json
{
  "ok": true,
  "mensaje": "Solicitud registrada. Tu ticket es BL-3FA9C1...",
  "codigo_ticket": "BL-3FA9C1"
}
```

En caso de error:

```json
{
  "ok": false,
  "mensaje": "Este libro no tiene copias disponibles."
}
```

Códigos HTTP usados:

| Código | Cuándo |
|---|---|
| `200` | Acción procesada (el campo `ok` indica éxito o fallo de negocio). |
| `401` | Petición AJAX sin sesión. |
| `405` | Método distinto de POST. |
| `419` | Petición AJAX con token CSRF inválido o vencido. |

### 2.5 El endpoint `actualizar_avatar.php`

Este archivo es un buen ejemplo de endpoint JSON pequeño y defensivo. Su orden de comprobaciones es deliberado:

```text
POST
 ├─ ¿Método POST?                No → 405
 ├─ ¿Sesión iniciada?            No → 401
 ├─ ¿CSRF válido?                No → 403
 ├─ ¿Avatar dentro de la lista?  No → 422
 ├─ UPDATE usuario SET foto = …
 ├─ $_SESSION['foto'] = ruta
 └─ 200 { ok, avatar, nombre, src }
```

Fragmento central:

```php
$idAvatar = trim((string) ($_POST['avatar'] ?? ''));
$avatares = avataresDisponibles();

if ($idAvatar === '' || !isset($avatares[$idAvatar])) {
    responderJson(422, [
        'ok' => false,
        'error' => 'El avatar seleccionado no es válido.',
    ]);
}

$rutaAvatar = 'Imagenes/Avatares/' . $idAvatar . '.png';

$stmt = $pdo->prepare(
    'UPDATE usuario SET foto = :foto WHERE id_usuario = :id_usuario LIMIT 1'
);
$stmt->execute([
    'foto'       => $rutaAvatar,
    'id_usuario' => (int) $_SESSION['usuario_id'],
]);

$_SESSION['foto'] = $rutaAvatar;
```

Por qué la lista blanca es importante: el valor recibido se usa para construir una ruta de archivo. Aceptar texto libre permitiría rutas arbitrarias o nombres inexistentes. Al validar contra `avataresDisponibles()`, solo se guardan los 10 avatares conocidos.

La respuesta incluye `src` con prefijo `../` porque quien consume el endpoint es una página dentro de `Paginas/`:

```json
{
  "ok": true,
  "avatar": "focused-tiger",
  "nombre": "Focused Tiger",
  "src": "../Imagenes/Avatares/focused-tiger.png"
}
```

La conexión se incluye **después** de las validaciones. Así, una petición inválida no abre conexión a la base ni ejecuta SQL.

### 2.6 Lógica de préstamos

#### Reglas centralizadas

```php
const DIAS_PRESTAMO = 15;
const MAX_PRESTAMOS_POR_ESTUDIANTE = 3;
```

Cambiar estos valores en `funciones_biblioteca.php` cambia el comportamiento en todo el sitio. No hay copias de esos números en otros archivos.

#### Estados de un préstamo

```text
                    solicitar_prestamo
                         │
                         ▼
                   ┌───────────┐
                   │ solicitado│
                   └─────┬─────┘
                         │ validar_entrega
                         ▼
                   ┌───────────┐      solicitar_devolucion      ┌──────────────────────┐
                   │  activo   │ ──────────────────────────────►│ devolucion_solicitada│
                   └─────┬─────┘                                └──────────┬───────────┘
                         │                                                 │
                         │ validar_devolucion                              │ validar_devolucion
                         ▼                                                 ▼
                   ┌───────────────────────────────────────────────────────────┐
                   │                        devuelto                          │
                   └───────────────────────────────────────────────────────────┘

 cancelar_solicitud: solicitado ──► cancelado
```

| Estado | Significado | ¿Descuenta copia? |
|---|---|---|
| `solicitado` | El estudiante generó el ticket; aún no reclama el libro. | No |
| `activo` | El bibliotecario entregó el libro. | Sí |
| `devolucion_solicitada` | El estudiante avisó que va a devolverlo. | Sí (sigue afuera) |
| `devuelto` | El bibliotecario recibió el libro. | No (copia repuesta) |
| `cancelado` | El estudiante canceló antes de la entrega. | No |

La copia se descuenta **al validar la entrega**, no al crear la solicitud. Es una decisión de diseño: el libro sigue disponible para otros hasta que físicamente sale de la biblioteca.

#### Funciones principales

| Función | Rol | Qué hace |
|---|---|---|
| `contarLibrosDisponibles()` | Todos | Suma `libro.disponibles`. |
| `contarPrestamosActivos()` | Todos | Cuenta `activo` y `devolucion_solicitada`, opcionalmente por usuario. |
| `contarRetrasos()` | Todos | Cuenta préstamos abiertos con `fecha_vencimiento < CURDATE()`. |
| `contarTicketsPorValidar()` | Bibliotecario/Admin | Cuenta `solicitado` y `devolucion_solicitada`. |
| `listarMisLibros()` | Estudiante | Préstamos abiertos del usuario con etiquetas de estado. |
| `listarTicketsPorValidar()` | Bibliotecario/Admin | Tickets pendientes con datos del estudiante. |
| `listarPrestamosActivos()` | Bibliotecario/Admin | Préstamos que están fuera, ordenados por vencimiento. |
| `estadosPrestamoPorLibro()` | Catálogo | Mapa `id_libro → estado` para pintar botones. |
| `solicitarPrestamo()` | Estudiante | Crea el ticket `solicitado`. |
| `cancelarSolicitud()` | Estudiante | Pasa de `solicitado` a `cancelado`. |
| `solicitarDevolucion()` | Estudiante | Pasa de `activo` a `devolucion_solicitada`. |
| `validarEntrega()` | Bibliotecario/Admin | Pasa a `activo`, descuenta copia y fija fechas. |
| `validarDevolucion()` | Bibliotecario/Admin | Pasa a `devuelto` y repone copia. |
| `guardarResena()` | Todos | Inserta una reseña de máximo 1000 caracteres. |

Todas las acciones devuelven el mismo formato:

```php
['ok' => true|false, 'mensaje' => 'Texto para el usuario']
```

Eso permite que `acciones.php` no sepa si la operación fue SQL, de negocio o de validación: solo mira `ok` y `mensaje`.

#### `solicitarPrestamo()` paso a paso

```php
// 1. El libro existe y tiene copias
$stmt = $pdo->prepare("SELECT disponibles FROM libro WHERE id_libro = ?");
…
if ((int) $libro['disponibles'] < 1) {
    return resultado(false, 'Este libro no tiene copias disponibles.');
}

// 2. No pedir dos veces el mismo libro
…

// 3. Límite de libros al mismo tiempo
if ((int) $stmt->fetchColumn() >= MAX_PRESTAMOS_POR_ESTUDIANTE) {
    return resultado(false, 'Llegaste al límite de ' . MAX_PRESTAMOS_POR_ESTUDIANTE . ' libros al mismo tiempo.');
}

// 4. Crear el ticket
$codigo = generarCodigoTicket(); // BL- + 6 caracteres hex
```

El código del ticket se genera con `random_bytes(3)` y se reintenta hasta 5 veces si choca con la restricción única `uq_prestamo_ticket`. Es una solución simple y suficiente para el volumen de una biblioteca escolar.

> **Característica a tener en cuenta:** `solicitarPrestamo()` no abre una transacción. Las comprobaciones de copias y de límite se hacen antes del `INSERT`, pero dos peticiones simultáneas podrían pasar ambas. El sistema vuelve a comprobar las copias al validar la entrega, así que el peor caso es un ticket que no se puede entregar, no una copia negativa. Si se quisiera eliminar esa ventana, habría que envolver las comprobaciones y el insert en una transacción con bloqueo.

#### `validarEntrega()`: transacción y bloqueo

Esta es la operación más delicada porque toca dos tablas (`prestamo` y `libro`). Por eso usa transacción y `FOR UPDATE`:

```php
$pdo->beginTransaction();

$stmt = $pdo->prepare(
    "SELECT id_prestamo, id_libro, estado FROM prestamo WHERE codigo_ticket = ? FOR UPDATE"
);
$stmt->execute([$codigoTicket]);
$prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prestamo) { $pdo->rollBack(); return resultado(false, 'No existe un ticket con ese código.'); }
if ($prestamo['estado'] !== 'solicitado') { $pdo->rollBack(); return resultado(false, 'Este ticket no está pendiente de entrega.'); }

$stmt = $pdo->prepare(
    "UPDATE libro SET disponibles = disponibles - 1 WHERE id_libro = ? AND disponibles > 0"
);
$stmt->execute([$prestamo['id_libro']]);

if ($stmt->rowCount() === 0) {
    $pdo->rollBack();
    return resultado(false, 'Ya no hay copias disponibles de este libro.');
}

$dias = (int) DIAS_PRESTAMO;
$stmt = $pdo->prepare(
    "UPDATE prestamo
     SET estado = 'activo',
         fecha_prestamo = CURDATE(),
         fecha_vencimiento = DATE_ADD(CURDATE(), INTERVAL $dias DAY)
     WHERE id_prestamo = ?"
);
$stmt->execute([$prestamo['id_prestamo']]);

$pdo->commit();
```

Qué se gana con esto:

- **`FOR UPDATE`** bloquea la fila del préstamo mientras se procesa. Dos bibliotecarios no pueden validar el mismo ticket a la vez.
- **`WHERE … AND disponibles > 0`** evita dejar `disponibles` en negativo aunque otra operación haya consumido la última copia.
- **`rowCount() === 0`** detecta que la condición no se cumplió; entonces se revierte todo.
- **`beginTransaction` / `commit` / `rollBack`** garantizan que no quede un préstamo activo sin haber descontado la copia (o al revés).
- **`(int) DIAS_PRESTAMO`** convierte la constante a entero antes de interpolarla en SQL. No es una entrada de usuario, pero mantener el casteo es una buena costumbre.

#### `validarDevolucion()`: reponer sin pasarse

La devolución acepta tanto `devolucion_solicitada` como `activo`, porque el estudiante puede entregar el libro directamente en el mostrador sin haber generado la solicitud previa.

El incremento de copias usa `LEAST`:

```php
$stmt = $pdo->prepare(
    "UPDATE libro SET disponibles = LEAST(copias, disponibles + 1) WHERE id_libro = ?"
);
```

Así, aunque se ejecute dos veces por un error, `disponibles` nunca supera el total de `copias`. El estado del préstamo se actualiza en la misma transacción.

#### Ayudas de presentación

Estas funciones no tocan la base; transforman datos para la vista:

| Función | Ejemplo |
|---|---|
| `etiquetaEstado($estado, $vencido)` | `activo` + vencido → `['Retraso', 'retraso']`. |
| `formatearFecha($fecha)` | `2026-05-09` → `09 / Mayo / 2026`. |
| `diasParaVencimiento($fecha)` | Devuelve días restantes o negativos. |
| `inicialesTitulo($titulo)` | `Cien años de soledad` → `CA`. |
| `agregarEtiquetas($filas)` | Añade `etiqueta` y `clase` a cada fila. |

Mantener estas transformaciones en PHP evita duplicar reglas de presentación en cada plantilla.

---

## 3. JavaScript

### Convenciones generales

- Todos los archivos usan **IIFE** (función autoejecutable) para no contaminar el ámbito global.
- `'use strict'` está activo en los tres scripts.
- Se usan **guard clauses** al inicio: si falta un elemento del DOM, el script termina sin errores.
- No hay módulos ES, imports ni bundler. Bootstrap se usa como global (`bootstrap.Modal`).
- Los scripts se cargan al final del `<body>`, después del HTML.
- Los nombres de variables y funciones están en español, igual que el resto del proyecto.

### 3.1 `Catalogo.js`

Se encarga de dos cosas: filtrar tarjetas y gestionar el modal de detalle con solicitud/cancelación de préstamos.

#### Estructura

```text
Catalogo.js
 ├─ Referencias al DOM y CSRF
 ├─ Filtros
 │   ├─ normalizar texto
 │   ├─ aplicarFiltros()
 │   └─ desplegable de categorías
 ├─ Modal de detalle
 │   ├─ leer data-* de la tarjeta
 │   ├─ pintar portada, título, autor, categoría, descripción
 │   ├─ calcular etiqueta de estado
 │   └─ configurar botón de acción
 └─ fetch a acciones.php
```

#### Filtros tolerantes a tildes y mayúsculas

```js
const normalizar = (texto) => String(texto || '')
  .toLocaleLowerCase('es')
  .normalize('NFD')
  .replace(/\p{Diacritic}/gu, '');
```

La combinación `normalize('NFD')` + eliminación de diacríticos hace que “cancion” encuentre “Canción” y que “JOSE” encuentre “José”. Se aplica al texto del buscador y a los `data-*` de cada tarjeta.

La búsqueda cubre tres campos:

```js
const coincideTexto = texto === ''
  || normalizar(tarjeta.dataset.titulo).includes(texto)
  || normalizar(tarjeta.dataset.autor).includes(texto)
  || normalizar(tarjeta.dataset.categoria).includes(texto);
```

La categoría se compara de forma exacta contra `data-categoria`, porque las opciones se generan desde la base de datos y coinciden carácter por carácter.

El contador se actualiza en el mismo recorrido y se anuncia con `aria-live`:

```js
contador.textContent = visibles === 1
  ? '1 libro encontrado'
  : `${visibles} libros encontrados`;
```

#### Desplegable de categorías accesible

El botón y la lista usan atributos ARIA:

```html
<button id="filtro-categoria"
  aria-haspopup="listbox"
  aria-expanded="false"
  aria-controls="filtro-categoria-lista">
```

```js
function abrirFiltro(abrir) {
  filtroLista.hidden = !abrir;
  filtroTrigger.setAttribute('aria-expanded', abrir ? 'true' : 'false');
}
```

Se cierra al hacer clic fuera y al pulsar `Escape`. La opción seleccionada actualiza `aria-selected`, el texto visible y un campo oculto (`data-filtro-categoria-campo`).

#### Contrato de datos de las tarjetas

Cada `.libro-card` publica la información que el modal necesita:

| Atributo | Uso |
|---|---|
| `data-libro-id` | Identificador para `acciones.php`. |
| `data-titulo` | Título en el modal. |
| `data-autor` | Autor en el modal. |
| `data-categoria` | Categoría en el modal y filtro. |
| `data-descripcion` | Sinopsis en el modal. |
| `data-disponibles` | Decide si se puede pedir prestado. |
| `data-copias` | Total de copias (informativo). |
| `data-mi-estado` | `solicitado`, `activo`, `devolucion_solicitada` o vacío. |
| `data-mi-prestamo-id` | Préstamo asociado, si existe. |
| `data-puede-solicitar` | `1` solo para estudiantes. |

La ventaja de este patrón es que el HTML ya viene renderizado por PHP: el JS no necesita pedir los datos del libro por red para abrir el modal.

#### Máquina de estados del botón

`configurarBotonAccion()` decide texto, clase y disponibilidad:

| Condición | Texto | ¿Habilitado? |
|---|---|---|
| `miEstado === 'solicitado'` | Cancelar solicitud | Sí |
| `miEstado === 'activo'` | Ya está en tu préstamo | No |
| `miEstado === 'devolucion_solicitada'` | Devolución en revisión | No |
| `puedeSolicitar !== '1'` | Solo estudiantes | No |
| `disponibles <= 0` | Sin copias disponibles | No |
| Caso normal | Pedir prestado | Sí |

Ese orden importa: primero se resuelven los estados del usuario, luego el rol y por último la disponibilidad. Así un estudiante con un préstamo activo ve su estado aunque queden copias.

#### Petición al servidor

```js
const cuerpo = new URLSearchParams({
  accion: accion === 'solicitar' ? 'solicitar_prestamo' : 'cancelar_solicitud',
  id_libro: libroActual.dataset.libroId,
  csrf_token: csrf,
  volver: 'Catalogo.php',
});

const respuesta = await fetch('../acciones.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
  body: cuerpo,
});

const datos = await respuesta.json();
```

Puntos clave:

- `URLSearchParams` produce el mismo formato que un formulario HTML (`application/x-www-form-urlencoded`).
- `X-Requested-With` y `Accept` hacen que `acciones.php` responda con JSON.
- El token se lee una sola vez desde `<meta name="csrf-token">`.
- El botón se deshabilita y muestra “Procesando…” antes del `fetch` para evitar dobles clics.
- Si la respuesta no es `ok`, se muestra el mensaje del servidor en el aviso del modal.
- Si la acción tiene éxito, se actualiza `data-mi-estado`, la tarjeta y el badge del modal **sin recargar la página**.
- El bloque `finally` vuelve a habilitar el botón y recalcula su estado, incluso si hubo error.

> La interfaz se actualiza sin recargar la página, pero solo después de que el servidor confirma la acción. Si el `fetch` falla, no se cambia el estado.

### 3.2 `Cuenta_usuarios.js`

Contiene dos IIFE independientes: uno para fechas/préstamos y otro para el selector de avatar.

#### Fechas sin desfases de zona horaria

El problema clásico: `new Date('2026-05-09')` se interpreta como UTC en algunos navegadores y puede mostrar el día anterior en zonas con offset negativo. El script lo evita parseando manualmente:

```js
const partes = /^(\d{4})-(\d{2})-(\d{2})$/.exec(valor.trim());
…
const fecha = new Date(anio, mes, dia);
```

Luego compara fechas de calendario en UTC:

```js
function fechaEnUTC(fecha) {
  return Date.UTC(fecha.getFullYear(), fecha.getMonth(), fecha.getDate());
}

function diasDeDiferencia(desde, hasta) {
  return Math.round((fechaEnUTC(hasta) - fechaEnUTC(desde)) / MILISEGUNDOS_POR_DIA);
}
```

Así, “hoy” y “vencimiento” se comparan como días completos, sin horas ni cambios de horario.

#### Etiquetas y clases

| Días restantes | Texto del badge | Clase |
|---|---|---|
| `> 2` | `N días` | Normal |
| `2` | `2 días` | `dias-restantes--urgente` |
| `1` | `1 día` | `dias-restantes--urgente` |
| `0` | `Vence hoy` | `dias-restantes--urgente` |
| `-1` | `Vencido hace 1 día` | `dias-restantes--vencido` |
| `< -1` | `Vencido hace N días` | `dias-restantes--vencido` |

El texto largo del detalle cambia a “Te quedan N días”, “Debes entregarlo hoy” o “Vencido hace N días”. Las fechas se formatean con `Intl.DateTimeFormat('es-CO')`.

#### Desplegable y estadísticas

Cada `.recommend-item` tiene un botón `.recommend-item__toggle` que alterna `aria-expanded` y `hidden` en `.recommend-detail`. Al final, el script cuenta préstamos y vencidos y actualiza:

```js
document.querySelector('[data-stat="prestamos-activos"]')
document.querySelector('[data-stat="libros-vencidos"]')
```

Si no hay préstamos con `data-inicio` y `data-devolucion`, el primer IIFE termina sin tocar nada; el segundo (avatar) sigue funcionando de forma independiente.

#### Selector de avatar

El segundo IIFE gestiona el modal `#modalAvatares`:

```js
let avatarActual = botonAvatar.dataset.avatarActual || '';
let seleccion = avatarActual;
let guardando = false;
```

Reglas de interacción:

- Al abrir el modal se marca el avatar actual.
- Elegir otro avatar actualiza `seleccion`, la clase `.is-selected` y el radio correspondiente.
- El botón “Guardar avatar” permanece deshabilitado si no hay selección o si la selección es la misma que la actual.
- Durante el guardado se bloquea el botón y se cambia el texto a “Guardando…”.
- Al terminar, se actualizan la imagen del sidebar y la del navbar con `resultado.src`, y se muestra un mensaje temporal de éxito.
- Si hay error, se muestra el mensaje del servidor tanto en el área de estado como en el feedback.

Envío:

```js
const datos = new FormData();
datos.append('avatar', seleccion);
datos.append('csrf', modalElement.dataset.csrf || '');

const respuesta = await fetch('../actualizar_avatar.php', {
  method: 'POST',
  body: datos,
  credentials: 'same-origin',
  headers: { 'X-Requested-With': 'XMLHttpRequest' },
});
```

`FormData` es la elección correcta aquí porque el endpoint espera campos de formulario (`avatar`, `csrf`) y no JSON. `credentials: 'same-origin'` garantiza que la cookie de sesión viaje con la petición.

### 3.3 `Olvide_mi_contrasena.js`

Este script es deliberadamente simple y **no envía nada al servidor**. Su propio comentario lo aclara:

> El envío real del correo lo debe hacer el servidor cuando exista el endpoint de recuperación.

Lo que hace:

1. Intercepta el `submit` con `preventDefault()`.
2. Valida que el correo no esté vacío y que `checkValidity()` sea verdadero.
3. Muestra un error con `role="alert"`, `aria-invalid="true"` y enfoca el campo.
4. Si es válido, oculta el formulario y muestra la sección de confirmación con el correo ingresado.
5. El botón “Usar otro correo” restaura el formulario y lo limpia.

Es un ejemplo de **validación de cliente**: mejora la experiencia, pero no sustituye la validación del servidor. Cuando exista el endpoint de recuperación, habrá que añadir la petición real y mantener estas comprobaciones como primera barrera.

### 3.4 Contratos de integración

| Origen | Destino | Datos | Respuesta |
|---|---|---|---|
| `Catalogo.php` | `acciones.php` | `accion`, `id_libro`, `csrf_token`, `volver` | JSON `{ ok, mensaje }` |
| `Panel_principal.php` | `acciones.php` | `accion`, `id_libro` o `id_prestamo`, `csrf_token`, `volver` | Redirect + flash |
| `Panel_bibliotecario.php` | `acciones.php` | `accion`, `codigo_ticket`, `csrf_token`, `volver` | Redirect + flash |
| `Cuenta_usuarios.php` | `actualizar_avatar.php` | `avatar`, `csrf` | JSON `{ ok, avatar, nombre, src }` |
| `Olvide_mi_contrasena.html` | — | Solo cliente | — |

Regla de oro: **todo POST que modifique datos debe llevar token CSRF** y toda respuesta debe poder manejar un fallo sin romper la interfaz.

---

## 4. HTML y CSS

### 4.1 Estructura HTML

#### Esqueleto común

Las páginas comparten esta base:

```html
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>…</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="…family=DM+Sans…&family=Playfair+Display…" rel="stylesheet" />
  <link rel="stylesheet" href="../Css/BiblioLend.css" />
</head>
<body class="pagina-catalogo">
  …
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../JavaScript/Catalogo.js"></script>
</body>
</html>
```

El `<body>` lleva una clase `pagina-*` que identifica la sección. Esa clase es la que permite que el CSS aplique estilos específicos sin afectar a las demás páginas.

Clases existentes:

```text
pagina-inicio
pagina-catalogo
pagina-contactanos
pagina-cuenta-usuarios
pagina-panel-principal
pagina-panel-admin
pagina-panel-bibliotecario
pagina-resenas
pagina-sobre-nosotros
pagina-panel-login
pagina-olvide-mi-contrasena
pagina-manual-de-bibliolend
```

#### Estructura semántica

Las páginas internas siguen un patrón consistente:

```html
<header>
  <nav class="navbar navbar-expand-lg navbar-dark navbar-sobre">…</nav>
</header>

<main class="catalogo-page">
  <?php mostrarFlash(); ?>
  <section aria-labelledby="titulo-catalogo">
    <h1 id="titulo-catalogo">Catálogo de Libros</h1>
    …
  </section>
</main>

<hr />
<footer class="site-footer">…</footer>
```

Uso de landmarks (`header`, `nav`, `main`, `footer`), encabezados jerárquicos y `aria-labelledby` para asociar secciones con su título. El enlace activo se marca con `aria-current="page"`.

#### Formularios y CSRF

Los formularios que envían a `acciones.php` incluyen el campo oculto y la página de retorno:

```html
<form action="../acciones.php" method="post" class="d-inline">
  <?= csrfCampo() ?>
  <input type="hidden" name="accion" value="cancelar_solicitud">
  <input type="hidden" name="id_libro" value="<?= (int) $prestamo['id_libro'] ?>">
  <input type="hidden" name="volver" value="Panel_principal.php">
  <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar solicitud</button>
</form>
```

El botón que define la acción puede ser un `submit` con `name="accion"` y `value="validar_entrega"`, como en el panel del bibliotecario. Así un mismo formulario sirve para varias acciones según el botón pulsado.

#### `data-*` como puente entre PHP y JS

El catálogo imprime los datos del libro en atributos:

```html
<article class="libro-card"
  data-libro-id="<?= htmlspecialchars($idLibro) ?>"
  data-titulo="<?= htmlspecialchars($tituloLibro) ?>"
  data-autor="<?= htmlspecialchars($autorLibro) ?>"
  data-categoria="<?= htmlspecialchars($categoriaLibro) ?>"
  data-descripcion="<?= htmlspecialchars($descripcionLibro) ?>"
  data-disponibles="<?= $disponiblesLibro ?>"
  data-mi-estado="<?= htmlspecialchars($estadoMi) ?>"
  data-puede-solicitar="<?= $puedeSolicitar ? '1' : '0' ?>">
```

Ventajas:

- El JS no necesita otra petición para abrir el modal.
- Los datos ya vienen escapados con `htmlspecialchars`.
- El estado del usuario se refleja desde el primer render, sin parpadeos.

#### Tablas responsive

Las tablas de los paneles usan `data-label` en cada celda para poder apilarse en móvil:

```html
<table class="table tabla-apilada">
  <thead>
    <tr><th>Libro</th><th>Solicitado</th><th>Estado</th><th>Acciones</th></tr>
  </thead>
  <tbody>
    <tr>
      <td data-label="Libro">…</td>
      <td data-label="Solicitado">…</td>
      <td data-label="Estado">…</td>
      <td data-label="Acciones">…</td>
    </tr>
  </tbody>
</table>
```

En pantallas pequeñas, el CSS oculta el `thead` y usa `content: attr(data-label)` en un pseudo-elemento `::before` para mostrar el nombre de la columna.

#### Modales

Los modales siguen la estructura de Bootstrap con etiquetas ARIA correctas:

```html
<div class="modal fade" id="modalDetalleLibro" tabindex="-1"
  aria-labelledby="detalleTituloModal" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content detalle-libro">
      <div class="modal-header">
        <h2 class="modal-title" id="detalleTituloModal">Detalle del libro</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar ventana"></button>
      </div>
      <div class="modal-body">…</div>
    </div>
  </div>
</div>
```

`modal-dialog-scrollable` es importante en móvil: mantiene el encabezado visible y permite desplazar el contenido.

### 4.2 Tokens y arquitectura CSS

`Css/BiblioLend.css` es una hoja única de unas 3700 líneas, organizada por secciones:

```text
01. Tokens de diseño
02. Base y fondo de página
03. Componentes compartidos
    03.1 Navbar
    03.2 Bloques, tarjetas y estados
    03.3 Botones
    03.4 Footer
    03.5 Tablas en móvil
04. Inicio (landing)
05. Catálogo
06. Contáctanos
07. Cuenta de usuario
08. Inicio de sesión y recuperación
09. Paneles administrativos
10. Panel principal
11. Reseñas y Sobre nosotros
12. Manual de usuario (recursos)
13. Manual interactivo (Manual_usuario)
14. Accesibilidad y movimiento reducido
```

#### Tokens

Todo el diseño se apoya en variables CSS definidas en `:root`:

| Grupo | Variables | Uso |
|---|---|---|
| Marca | `--bl-navy`, `--bl-indigo`, `--bl-accent` | Colores principales. |
| Superficies | `--bl-white`, `--bl-surface`, `--bl-surface-alt`, `--bl-border` | Fondos y bordes. |
| Texto | `--bl-text`, `--bl-text-heading`, `--bl-text-muted` | Jerarquía tipográfica. |
| Estados | `--bl-success`, `--bl-danger`, `--bl-warning` y sus fondos | Badges y alertas. |
| Tipografía | `--bl-font-body`, `--bl-font-heading` | DM Sans y Playfair Display. |
| Radios | `--bl-radius-sm` … `--bl-radius-pill` | Bordes redondeados. |
| Sombras | `--bl-shadow-xs` … `--bl-shadow-lg` | Profundidad. |
| Layout | `--bl-container`, `--bl-container-narrow` | Anchos máximos. |
| Movimiento | `--bl-transition-fast`, `--bl-transition` | Transiciones. |

Ejemplo:

```css
:root {
  --bl-navy: #070f2e;
  --bl-indigo: #3737a6;
  --bl-accent: #ffab00;
  --bl-font-body: 'DM Sans', sans-serif;
  --bl-font-heading: 'Playfair Display', serif;
  --bl-radius-md: 12px;
  --bl-transition: 0.3s ease;
}
```

Usar tokens en lugar de valores sueltos permite cambiar la identidad visual sin buscar y reemplazar decenas de colores.

#### Scoping por página

El patrón central es `body.pagina-*`:

```css
body:is(
    .pagina-catalogo,
    .pagina-contactanos,
    .pagina-cuenta-usuarios,
    .pagina-panel-principal,
    .pagina-resenas,
    .pagina-sobre-nosotros,
    .pagina-panel-admin,
    .pagina-panel-bibliotecario
  ) {
  min-height: 100vh;
  background: var(--bl-page-bg) fixed no-repeat;
  background-size: cover;
  font-family: var(--bl-font-body);
}
```

Esto cumple dos funciones:

1. **Compartir** base visual entre páginas internas.
2. **Aislar** estilos: una regla de `body.pagina-catalogo .filtros` no puede afectar a `pagina-inicio`.

La landing (`pagina-inicio`) y las páginas de login/recuperación tienen sus propios bloques porque su diseño es distinto.

#### Componentes

| Prefijo / clase | Sección |
|---|---|
| `.navbar`, `.nav-custom`, `.navbar-sobre` | Navegación superior. |
| `.site-footer` | Pie compartido. |
| `.libro-card`, `.libros-grid`, `.filtros` | Catálogo. |
| `.detalle-libro__*` | Modal de detalle. |
| `.avatar__*`, `.avatar-grid`, `.avatar-option` | Cuenta y selector de avatar. |
| `.recommend-item`, `.recommend-detail` | Préstamos en la cuenta. |
| `.bloque`, `.tabla-apilada`, `.estado` | Paneles y tablas. |
| `.btn-login`, `.field-input`, `.field-error` | Login y recuperación. |

La convención BEM simplificada (`bloque__elemento--modificador`) se usa sobre todo en componentes nuevos: `detalle-libro__portada`, `detalle-libro__aviso--error`, `avatar-option__input`.

### 4.3 Componentes y estados

#### Badges de estado

El PHP decide la clase y el CSS decide el color:

```html
<span class="estado disponible">Disponible</span>
<span class="estado prestado">Prestado</span>
<span class="estado pendiente">Solicitud enviada</span>
<span class="estado retraso">Retraso</span>
```

```css
.estado {
  display: inline-block;
  padding: 6px 12px;
  border-radius: var(--bl-radius-pill);
  font-size: 0.85rem;
  font-weight: 700;
}

.disponible { color: var(--bl-success); background-color: var(--bl-success-bg); }
.prestado  { color: var(--bl-danger);  background-color: var(--bl-danger-bg); }
.pendiente { color: #8a6000;           background-color: #fff3cd; }
.retraso   { color: #842029;           background-color: #f8d7da; }
.estado.activo { color: #084298; background-color: #cfe2ff; }
```

La etiqueta visible la calcula `etiquetaEstado()` en PHP. El JS replica la misma lógica en `etiquetaVisible()` para actualizar la tarjeta tras una acción AJAX. Si se agrega un estado nuevo, hay que tocar ambos lados.

#### Botones

Los botones de Bootstrap se redefinen con tokens:

```css
.btn-primary {
  --bs-btn-bg: var(--bl-indigo);
  --bs-btn-border-color: var(--bl-indigo);
  --bs-btn-hover-bg: var(--bl-indigo-dark);
}
```

Así se conserva el comportamiento de Bootstrap (focus, disabled, tamaños) sin heredar su paleta por defecto.

#### Navbar y footer

El navbar usa `navbar-sobre` en las páginas internas y `navbar-dark` donde corresponde. El footer se repite en las páginas internas con la misma estructura de tres columnas: marca, enlaces rápidos y contacto. Al ser HTML duplicado, cualquier cambio debe replicarse en todas las páginas que lo incluyen.

### 4.4 Responsive y accesibilidad

#### Breakpoints

El CSS usa los breakpoints de Bootstrap y algunos propios:

| Breakpoint | Uso típico |
|---|---|
| `991.98px` | Colapso del navbar y layouts de dos columnas. |
| `768px` | Tablas apiladas, ajustes de paneles y modales. |
| `575.98px` | Ajustes de tipografía, botones y espaciados. |
| `480px` | Detalles finos en catálogo y landing. |

No hay un sistema de grid propio: se combina el grid de Bootstrap (`row`, `col-*`) con flexbox y CSS Grid en los componentes.

#### Tabla apilada en móvil

El patrón ya descrito (`thead` oculto + `data-label` + `::before`) evita el scroll horizontal en pantallas pequeñas. Es especialmente importante en los paneles, donde las tablas tienen muchas columnas.

#### Accesibilidad

Prácticas que ya están implementadas:

- `lang="es"` en todas las páginas.
- Landmarks semánticos (`header`, `nav`, `main`, `footer`) y encabezados jerárquicos.
- `aria-label` en botones icónicos (cerrar modal, cambiar avatar, redes sociales).
- `aria-expanded` en desplegables y `aria-controls` para relacionarlos con su lista.
- `role="status"` y `aria-live="polite"` en el contador del catálogo.
- `role="alert"` en mensajes de error.
- `aria-invalid` en campos con error.
- `focus-visible` en enlaces y botones para teclado.
- `visually-hidden` para etiquetas que no deben verse.
- `loading="lazy"` en las portadas del catálogo.
- `alt` descriptivo en imágenes; `alt=""` en imágenes decorativas.

> **Brecha detectada:** el índice del CSS anuncia una sección “14. Accesibilidad y movimiento reducido”, pero el archivo no contiene actualmente reglas `@media (prefers-reduced-motion: reduce)`. Si se agregan animaciones nuevas, conviene incluir ese bloque para respetar la preferencia del sistema.

---

## 5. Flujos de extremo a extremo

### 5.1 Login

```text
Usuario                Panel_login.php              MySQL
  │                          │                        │
  ├─ correo + contraseña ───►│                        │
  │                          ├─ SELECT por correo ───►│
  │                          │◄── usuario + hash ─────┤
  │                          ├─ password_verify()     │
  │                          ├─ session_regenerate_id()
  │                          ├─ $_SESSION[...] = ...
  │◄── redirect al panel ────┤
```

### 5.2 Solicitud de préstamo desde el catálogo

```text
Estudiante          Catalogo.js            acciones.php        funciones_biblioteca.php        MySQL
   │                    │                       │                        │                       │
   ├─ clic "Pedir" ────►│                       │                        │                       │
   │                    ├─ POST + CSRF ────────►│                        │                       │
   │                    │                       ├─ validar rol/CSRF ─────┤                       │
   │                    │                       ├─ solicitarPrestamo() ─►│                       │
   │                    │                       │                        ├─ SELECT disponibles ►│
   │                    │                       │                        ├─ SELECT duplicados ─►│
   │                    │                       │                        ├─ SELECT límite ─────►│
   │                    │                       │                        ├─ INSERT ticket ────►│
   │                    │◄── JSON { ok, ... } ──┤◄── resultado ──────────┤                       │
   │◄─ actualizar DOM ──┤                       │                        │                       │
```

### 5.3 Entrega y devolución en mostrador

```text
Bibliotecario         Panel_bibliotecario.php       acciones.php         validarEntrega/Devolucion
     │                          │                       │                          │
     ├─ código del ticket ─────►│                       │                          │
     │                          ├─ POST + CSRF ────────►│                          │
     │                          │                       ├─ rol bibliotecario ─────►│
     │                          │                       │                          ├─ BEGIN
     │                          │                       │                          ├─ SELECT ... FOR UPDATE
     │                          │                       │                          ├─ UPDATE libro
     │                          │                       │                          ├─ UPDATE prestamo
     │                          │                       │                          ├─ COMMIT
     │◄── redirect + flash ─────┤◄──────────────────────┤                          │
```

La diferencia clave entre ambos flujos: el catálogo responde JSON porque el usuario no debe perder el contexto del modal; los paneles redirigen porque el bibliotecario necesita ver la tabla actualizada.

---

## 6. Recetario para extender el proyecto

### Agregar una acción nueva

1. Implementar la lógica en `funciones_biblioteca.php` y devolver `resultado(true|false, 'mensaje')`.
2. Agregar el `case` en `acciones.php`.
3. Validar el rol dentro del `case` (si no es para todos).
4. Validar los IDs con `filter_var`.
5. Agregar el `name="accion"` en el formulario o el `accion` en el `fetch`.
6. Incluir `csrfCampo()` o el token correspondiente.
7. Probar el flujo AJAX y el flujo con formulario.

### Agregar una página protegida

1. Crear el archivo en `Paginas/`.
2. Incluir `conexion.php`, `autenticacion.php` y, si se usan préstamos, `funciones_biblioteca.php`.
3. Llamar a `requerirRol(...)` antes de cualquier salida HTML.
4. Agregar la clase `pagina-*` al `<body>`.
5. Reutilizar navbar, footer y estilos existentes.
6. Si la página recibe formularios, usar `csrfCampo()`.
7. Llamar a `mostrarFlash()` al inicio del contenido.

### Agregar un avatar

1. Guardar la imagen en `Imagenes/Avatares/<id>.png`.
2. Añadir el identificador y nombre en `avataresDisponibles()` (`autenticacion.php`).
3. No hace falta tocar el JS: el modal se genera con un `foreach` sobre esa lista.
4. Verificar que el nombre del archivo coincida exactamente con la clave (minúsculas, guiones, sin espacios).

### Cambiar la duración o el límite de préstamos

Editar las constantes en `funciones_biblioteca.php`:

```php
const DIAS_PRESTAMO = 15;
const MAX_PRESTAMOS_POR_ESTUDIANTE = 3;
```

No hay otros lugares donde estén duplicados. Los textos que muestran el límite se construyen a partir de la constante.

### Agregar un componente CSS

1. Añadir los estilos en la sección temática correspondiente de `Css/BiblioLend.css`.
2. Usar tokens (`var(--bl-*)`) en lugar de colores o tamaños sueltos.
3. Delimitar con `body.pagina-*` si el componente es exclusivo de una página.
4. Incluir estados `:hover`, `:focus-visible` y `:disabled` cuando aplique.
5. Añadir el ajuste responsive dentro del bloque `@media` de la sección.

---

## 7. Observaciones y deuda técnica

Estas son características reales del código actual, no errores inventados. Se documentan para que quien mantenga el proyecto sepa dónde mirar.

| # | Observación | Impacto | Recomendación |
|---|---|---|---|
| 1 | Credenciales de base de datos escritas en `conexion.php`. | Medio | Moverlas a variables de entorno o a un archivo fuera del repositorio. |
| 2 | Dos pares de funciones CSRF equivalentes. | Bajo | Unificar en una sola API y actualizar las llamadas. |
| 3 | `solicitarPrestamo()` no usa transacción. | Bajo | Aceptable para el volumen actual; envolver si se necesita consistencia estricta. |
| 4 | `guardar_resena` no restringe el rol. | Bajo | Agregar comprobación si las reseñas deben ser solo de estudiantes. |
| 5 | `Olvide_mi_contrasena.js` no envía nada al servidor. | Medio | Implementar el endpoint de recuperación cuando exista el servicio de correo. |
| 6 | El índice del CSS anuncia movimiento reducido, pero no hay reglas `prefers-reduced-motion`. | Bajo | Agregar el bloque si se incorporan animaciones. |
| 7 | El footer y el navbar se repiten en cada página. | Bajo | Mantener la sincronización manual o extraer partials PHP si el proyecto crece. |
| 8 | `hash.php` es accesible por web para administradores. | Bajo | Preferir ejecución por CLI o eliminar el archivo después de migrar. |
| 9 | Los redirects de `autenticacion.php` son relativos a `Paginas/`. | Bajo | Documentar la restricción al crear páginas fuera de esa carpeta. |
| 10 | El login no valida CSRF. | Bajo | Considerar un token también en el formulario de login para endurecer la sesión. |

Ninguna de estas observaciones impide el funcionamiento actual. Son puntos de mejora, no fallos bloqueantes.

---

## 8. Glosario rápido

| Término | Definición |
|---|---|
| **PDO** | Capa de acceso a datos de PHP. Permite consultas preparadas y manejo de errores por excepción. |
| **DSN** | Cadena que indica motor, host, base de datos y charset. |
| **CSRF** | Ataque que fuerza peticiones autenticadas desde otro sitio; se mitiga con tokens. |
| **Flash** | Mensaje de una sola lectura que se muestra después de una redirección. |
| **Ticket** | Código único (`BL-XXXXXX`) que identifica un préstamo. |
| **Transacción** | Conjunto de operaciones SQL que se confirman o se revierten como una unidad. |
| **`FOR UPDATE`** | Bloqueo de fila dentro de una transacción para evitar modificaciones concurrentes. |
| **IIFE** | Función autoejecutable que encapsula el código JavaScript. |
| **Token de diseño** | Variable CSS que almacena un valor reutilizable de color, tipografía, radio, etc. |
| **Scoping** | Delimitar estilos a una página o componente para evitar efectos colaterales. |

---

*Guía técnica de BiblioLend. Los ejemplos reflejan el código actual del repositorio; si se modifica la lógica de préstamos o los contratos de `acciones.php`, conviene actualizar este documento en el mismo cambio.*
