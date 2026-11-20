<?php
/**
 * funciones_biblioteca.php
 * Funciones de BiblioLend: contadores, listas y acciones de préstamos.
 *
 * DÓNDE VA: en la raíz del proyecto, junto a conexion.php y autenticacion.php.
 *
 * CÓMO SE USA (desde una página dentro de Paginas/):
 *   require_once __DIR__ . '/../conexion.php';
 *   require_once __DIR__ . '/../funciones_biblioteca.php';
 *   $activos = contarPrestamosActivos($pdo, $_SESSION['usuario_id']);
 *
 * Todas las funciones reciben $pdo (la conexión que crea conexion.php).
 */

// ------------------------------------------------------------
// Reglas del préstamo: se cambian aquí y valen para todo el sitio
// ------------------------------------------------------------
const DIAS_PRESTAMO = 15;                  // duración de un préstamo
const MAX_PRESTAMOS_POR_ESTUDIANTE = 3;    // solicitudes + préstamos abiertos al mismo tiempo

// ============================================================
// 1. CONTADORES (los números que se ven en los paneles)
// ============================================================

// Número de copias disponibles ahora (suma de la columna 'disponibles' de todos los libros)
function contarLibrosDisponibles(PDO $pdo): int
{
    return (int) $pdo->query("SELECT COALESCE(SUM(disponibles), 0) FROM libro")->fetchColumn();
}

// Libros que están prestados ahora. Si pasas $idUsuario, cuenta solo los de ese estudiante.
function contarPrestamosActivos(PDO $pdo, ?int $idUsuario = null): int
{
    $sql = "SELECT COUNT(*) FROM prestamo WHERE estado IN ('activo','devolucion_solicitada')";
    $params = [];

    if ($idUsuario !== null) {
        $sql .= " AND id_usuario = ?";
        $params[] = $idUsuario;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

// Préstamos que ya pasaron su fecha de vencimiento y no se han devuelto
function contarRetrasos(PDO $pdo, ?int $idUsuario = null): int
{
    $sql = "SELECT COUNT(*) FROM prestamo
            WHERE estado IN ('activo','devolucion_solicitada')
              AND fecha_vencimiento < CURDATE()";
    $params = [];

    if ($idUsuario !== null) {
        $sql .= " AND id_usuario = ?";
        $params[] = $idUsuario;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

// Total de préstamos ya devueltos
function contarDevoluciones(PDO $pdo): int
{
    return (int) $pdo->query("SELECT COUNT(*) FROM prestamo WHERE estado = 'devuelto'")->fetchColumn();
}

// Tickets esperando que el bibliotecario los valide (entregas y devoluciones)
function contarTicketsPorValidar(PDO $pdo): int
{
    return (int) $pdo->query(
        "SELECT COUNT(*) FROM prestamo WHERE estado IN ('solicitado','devolucion_solicitada')"
    )->fetchColumn();
}

// Total de usuarios registrados (estudiantes, bibliotecarios y administradores).
function contarUsuariosRegistrados(PDO $pdo): int
{
    return (int) $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
}

function contarResenas(PDO $pdo): int
{
    return (int) $pdo->query("SELECT COUNT(*) FROM resena")->fetchColumn();
}

// ============================================================
// 2. AYUDAS PARA MOSTRAR DATOS
// ============================================================

// Convierte el estado de la base en el texto y la clase CSS que se ven en pantalla
function etiquetaEstado(string $estado, bool $vencido): array
{
    if ($vencido && ($estado === 'activo' || $estado === 'devolucion_solicitada')) {
        return ['texto' => 'Retraso', 'clase' => 'retraso'];
    }

    switch ($estado) {
        case 'solicitado':
            return ['texto' => 'Solicitud enviada', 'clase' => 'pendiente'];
        case 'activo':
            return ['texto' => 'En préstamo', 'clase' => 'activo'];
        case 'devolucion_solicitada':
            return ['texto' => 'Devolución solicitada', 'clase' => 'pendiente'];
        case 'devuelto':
            return ['texto' => 'Devuelto', 'clase' => 'disponible'];
        case 'cancelado':
            return ['texto' => 'Cancelado', 'clase' => 'prestado'];
        default:
            return ['texto' => $estado, 'clase' => 'pendiente'];
    }
}

// "2026-05-09" -> "09 / Mayo / 2026" (el formato de tus tablas). Si no hay fecha: "—"
function formatearFecha(?string $fecha): string
{
    if ($fecha === null || $fecha === '') {
        return '—';
    }

    $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
              'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $marca = strtotime($fecha);

    if ($marca === false) {
        return '—';
    }

    return date('d', $marca) . ' / ' . $meses[(int) date('n', $marca)] . ' / ' . date('Y', $marca);
}

// A cada fila le agrega 'etiqueta' (texto) y 'clase' (para el <span class="estado ...">)
function agregarEtiquetas(array $filas): array
{
    foreach ($filas as &$fila) {
        $e = etiquetaEstado($fila['estado'], (bool) $fila['vencido']);
        $fila['etiqueta'] = $e['texto'];
        $fila['clase'] = $e['clase'];
    }
    unset($fila);

    return $filas;
}

// Días que faltan para una fecha (negativo si ya venció). Null si no hay fecha.
function diasParaVencimiento(?string $fecha): ?int
{
    if ($fecha === null || $fecha === '') {
        return null;
    }

    $marca = strtotime($fecha);
    if ($marca === false) {
        return null;
    }

    $hoy = strtotime(date('Y-m-d'));

    return (int) floor(($marca - $hoy) / 86400);
}

// "Cien años de soledad" -> "CA" (para portadas sin imagen)
function inicialesTitulo(string $titulo): string
{
    $palabras = preg_split('/\s+/u', trim($titulo), -1, PREG_SPLIT_NO_EMPTY);

    if (!$palabras) {
        return '?';
    }

    $iniciales = '';
    foreach (array_slice($palabras, 0, 2) as $palabra) {
        $iniciales .= mb_strtoupper(mb_substr($palabra, 0, 1));
    }

    return $iniciales;
}

// Ruta segura de la portada de un libro. Devuelve '' si no hay una válida.
function rutaPortadaLibro(?string $portada): string
{
    $portada = (string) $portada;

    if ($portada !== '' && preg_match('#^Imagenes/Libros/[A-Za-z0-9._-]+$#', $portada)) {
        return $portada;
    }

    return '';
}

// ============================================================
// 3. LISTAS (tablas de los paneles)
// ============================================================

// ESTUDIANTE: "Estado de mis libros"
function listarMisLibros(PDO $pdo, int $idUsuario): array
{
    $stmt = $pdo->prepare(
        "SELECT p.id_prestamo, p.codigo_ticket, p.estado,
                p.fecha_solicitud, p.fecha_prestamo, p.fecha_vencimiento,
                (p.estado IN ('activo','devolucion_solicitada')
                  AND p.fecha_vencimiento < CURDATE()) AS vencido,
                l.id_libro, l.titulo, l.categoria, l.portada
         FROM prestamo p
         JOIN libro l ON l.id_libro = p.id_libro
         WHERE p.id_usuario = ?
           AND p.estado IN ('solicitado','activo','devolucion_solicitada')
         ORDER BY p.fecha_solicitud DESC"
    );
    $stmt->execute([$idUsuario]);

    return agregarEtiquetas($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// BIBLIOTECARIO: tickets por validar (entregas y devoluciones), el más antiguo primero
function listarTicketsPorValidar(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT p.id_prestamo, p.codigo_ticket, p.estado,
                p.fecha_solicitud, p.fecha_prestamo, p.fecha_vencimiento,
                (p.estado IN ('activo','devolucion_solicitada')
                  AND p.fecha_vencimiento < CURDATE()) AS vencido,
                CONCAT_WS(' ', u.primer_nombre, u.segundo_nombre,
                               u.primer_apellido, u.segundo_apellido) AS estudiante,
                u.grado, u.grupo,
                l.titulo
         FROM prestamo p
         JOIN usuario u ON u.id_usuario = p.id_usuario
         JOIN libro l ON l.id_libro = p.id_libro
         WHERE p.estado IN ('solicitado','devolucion_solicitada')
         ORDER BY p.fecha_solicitud ASC"
    );

    return agregarEtiquetas($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// BIBLIOTECARIO y ADMIN: préstamos que están afuera ahora (con retraso o sin retraso)
function listarPrestamosActivos(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT p.id_prestamo, p.codigo_ticket, p.estado,
                p.fecha_solicitud, p.fecha_prestamo, p.fecha_vencimiento,
                (p.estado IN ('activo','devolucion_solicitada')
                  AND p.fecha_vencimiento < CURDATE()) AS vencido,
                CONCAT_WS(' ', u.primer_nombre, u.segundo_nombre,
                               u.primer_apellido, u.segundo_apellido) AS estudiante,
                u.grado, u.grupo,
                l.titulo
         FROM prestamo p
         JOIN usuario u ON u.id_usuario = p.id_usuario
         JOIN libro l ON l.id_libro = p.id_libro
         WHERE p.estado IN ('activo','devolucion_solicitada')
         ORDER BY p.fecha_vencimiento ASC"
    );

    return agregarEtiquetas($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// ADMIN: todas las reseñas, la más reciente primero
function listarResenas(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT r.id_resena, r.cuerpo, r.fecha, u.correo,
                CONCAT_WS(' ', u.primer_nombre, u.primer_apellido) AS estudiante
         FROM resena r
         JOIN usuario u ON u.id_usuario = r.id_usuario
         ORDER BY r.fecha DESC"
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// BIBLIOTECARIO: todos los libros con los datos que se pueden editar
function listarLibrosGestion(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT id_libro, titulo, autor, categoria, descripcion, portada, copias, disponibles
         FROM libro
         ORDER BY titulo"
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Categorías existentes, para sugerirlas en el formulario de libros
function listarCategoriasLibros(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT DISTINCT categoria
         FROM libro
         WHERE categoria IS NOT NULL AND TRIM(categoria) <> ''
         ORDER BY categoria"
    );

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// ADMIN: usuarios registrados junto con todo su historial de préstamos
function listarUsuariosGestion(PDO $pdo): array
{
    $usuarios = $pdo->query(
        "SELECT id_usuario, documento, primer_nombre, segundo_nombre,
                primer_apellido, segundo_apellido, correo, rol, grado, grupo, foto
         FROM usuario
         ORDER BY rol, primer_apellido, primer_nombre"
    )->fetchAll(PDO::FETCH_ASSOC);

    $prestamos = $pdo->query(
        "SELECT p.id_prestamo, p.id_usuario, p.codigo_ticket, p.estado,
                p.fecha_solicitud, p.fecha_prestamo, p.fecha_vencimiento,
                (p.estado IN ('activo','devolucion_solicitada')
                  AND p.fecha_vencimiento < CURDATE()) AS vencido,
                l.titulo, l.autor
         FROM prestamo p
         JOIN libro l ON l.id_libro = p.id_libro
         ORDER BY p.fecha_solicitud DESC, p.id_prestamo DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $prestamos = agregarEtiquetas($prestamos);
    $porUsuario = [];

    foreach ($prestamos as $prestamo) {
        $porUsuario[(int) $prestamo['id_usuario']][] = $prestamo;
    }

    foreach ($usuarios as &$usuario) {
        $lista = $porUsuario[(int) $usuario['id_usuario']] ?? [];
        $activos = 0;

        foreach ($lista as $prestamo) {
            if (in_array($prestamo['estado'], ['activo', 'devolucion_solicitada'], true)) {
                $activos++;
            }
        }

        $nombre = trim(implode(' ', array_filter([
            $usuario['primer_nombre'] ?? '',
            $usuario['segundo_nombre'] ?? '',
            $usuario['primer_apellido'] ?? '',
            $usuario['segundo_apellido'] ?? '',
        ])));

        $usuario['nombre'] = $nombre !== '' ? $nombre : (string) $usuario['correo'];
        $usuario['grado_grupo'] = trim(($usuario['grado'] ?? '') . ' ' . ($usuario['grupo'] ?? ''));
        $usuario['prestamos'] = $lista;
        $usuario['prestamos_total'] = count($lista);
        $usuario['prestamos_activos'] = $activos;
    }
    unset($usuario);

    return $usuarios;
}

// CATÁLOGO: ids de los libros que este estudiante ya solicitó (para mostrar "Cancelar solicitud")
function librosConSolicitud(PDO $pdo, int $idUsuario): array
{
    $stmt = $pdo->prepare(
        "SELECT id_libro FROM prestamo WHERE id_usuario = ? AND estado = 'solicitado'"
    );
    $stmt->execute([$idUsuario]);

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

// CATÁLOGO: estado actual de este estudiante con cada libro.
// Devuelve [id_libro => ['estado' => 'solicitado', 'id_prestamo' => 3], ...]
function estadosPrestamoPorLibro(PDO $pdo, int $idUsuario): array
{
    $stmt = $pdo->prepare(
        "SELECT id_libro, id_prestamo, estado
         FROM prestamo
         WHERE id_usuario = ?
           AND estado IN ('solicitado','activo','devolucion_solicitada')
         ORDER BY fecha_solicitud DESC, id_prestamo DESC"
    );
    $stmt->execute([$idUsuario]);

    $estados = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $idLibro = (int) $fila['id_libro'];

        // Si por datos antiguos hubiera más de una fila, gana la más reciente.
        if (!isset($estados[$idLibro])) {
            $estados[$idLibro] = [
                'estado'      => $fila['estado'],
                'id_prestamo' => (int) $fila['id_prestamo'],
            ];
        }
    }

    return $estados;
}

// ============================================================
// 4. ACCIONES (lo que hacen los botones)
// Todas devuelven: ['ok' => true/false, 'mensaje' => '...'] (+ datos extra)
// ============================================================

function resultado(bool $ok, string $mensaje, array $extra = []): array
{
    return array_merge(['ok' => $ok, 'mensaje' => $mensaje], $extra);
}

// Código del ticket, por ejemplo BL-3FA9C1
function generarCodigoTicket(): string
{
    return 'BL-' . strtoupper(bin2hex(random_bytes(3)));
}

// ESTUDIANTE pulsa "Pedir prestado": crea el ticket en estado 'solicitado'
function solicitarPrestamo(PDO $pdo, int $idUsuario, int $idLibro): array
{
    try {
        // 1. El libro existe y tiene copias
        $stmt = $pdo->prepare("SELECT disponibles FROM libro WHERE id_libro = ?");
        $stmt->execute([$idLibro]);
        $libro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$libro) {
            return resultado(false, 'El libro no existe.');
        }
        if ((int) $libro['disponibles'] < 1) {
            return resultado(false, 'Este libro no tiene copias disponibles.');
        }

        // 2. No pedir dos veces el mismo libro
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM prestamo
             WHERE id_usuario = ? AND id_libro = ?
               AND estado IN ('solicitado','activo','devolucion_solicitada')"
        );
        $stmt->execute([$idUsuario, $idLibro]);
        if ((int) $stmt->fetchColumn() > 0) {
            return resultado(false, 'Ya tienes una solicitud o un préstamo de este libro.');
        }

        // 3. Límite de libros al mismo tiempo
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM prestamo
             WHERE id_usuario = ?
               AND estado IN ('solicitado','activo','devolucion_solicitada')"
        );
        $stmt->execute([$idUsuario]);
        if ((int) $stmt->fetchColumn() >= MAX_PRESTAMOS_POR_ESTUDIANTE) {
            return resultado(false, 'Llegaste al límite de ' . MAX_PRESTAMOS_POR_ESTUDIANTE . ' libros al mismo tiempo.');
        }

        // 4. Crear el ticket (si el código ya existía, se prueba con otro)
        $insertar = $pdo->prepare(
            "INSERT INTO prestamo (codigo_ticket, id_usuario, id_libro) VALUES (?, ?, ?)"
        );

        for ($intento = 0; $intento < 5; $intento++) {
            $codigo = generarCodigoTicket();

            try {
                $insertar->execute([$codigo, $idUsuario, $idLibro]);
                return resultado(true, 'Solicitud registrada. Tu ticket es ' . $codigo
                    . '. Acércate a la biblioteca para reclamar el libro.', [
                    'codigo_ticket' => $codigo,
                ]);
            } catch (PDOException $e) {
                // Solo se reintenta si el error fue por código repetido
                if (strpos($e->getMessage(), 'uq_prestamo_ticket') === false) {
                    throw $e;
                }
            }
        }

        return resultado(false, 'No se pudo generar el ticket. Inténtalo de nuevo.');
    } catch (Throwable $e) {
        error_log('Error en solicitarPrestamo: ' . $e->getMessage());
        return resultado(false, 'No se pudo registrar la solicitud. Inténtalo de nuevo más tarde.');
    }
}

// ESTUDIANTE pulsa "Cancelar solicitud" (solo si el bibliotecario aún no la validó)
function cancelarSolicitud(PDO $pdo, int $idUsuario, int $idLibro): array
{
    try {
        $stmt = $pdo->prepare(
            "UPDATE prestamo SET estado = 'cancelado'
             WHERE id_usuario = ? AND id_libro = ? AND estado = 'solicitado'"
        );
        $stmt->execute([$idUsuario, $idLibro]);

        if ($stmt->rowCount() === 0) {
            return resultado(false, 'No hay una solicitud pendiente de este libro.');
        }

        return resultado(true, 'Solicitud cancelada.');
    } catch (Throwable $e) {
        error_log('Error en cancelarSolicitud: ' . $e->getMessage());
        return resultado(false, 'No se pudo cancelar la solicitud.');
    }
}

// ESTUDIANTE pulsa "Generar nueva devolución" sobre uno de sus préstamos activos
function solicitarDevolucion(PDO $pdo, int $idUsuario, int $idPrestamo): array
{
    try {
        $stmt = $pdo->prepare(
            "UPDATE prestamo SET estado = 'devolucion_solicitada'
             WHERE id_prestamo = ? AND id_usuario = ? AND estado = 'activo'"
        );
        $stmt->execute([$idPrestamo, $idUsuario]);

        if ($stmt->rowCount() === 0) {
            return resultado(false, 'Ese préstamo no se puede devolver (ya está en proceso o no es tuyo).');
        }

        return resultado(true, 'Devolución solicitada. Entrega el libro en la biblioteca.');
    } catch (Throwable $e) {
        error_log('Error en solicitarDevolucion: ' . $e->getMessage());
        return resultado(false, 'No se pudo solicitar la devolución.');
    }
}

// BIBLIOTECARIO valida el ticket al ENTREGAR el libro:
// pasa a 'activo', resta una copia y fija las fechas
function validarEntrega(PDO $pdo, string $codigoTicket): array
{
    try {
        $pdo->beginTransaction();

        $codigoTicket = strtoupper(trim($codigoTicket));

        $stmt = $pdo->prepare(
            "SELECT id_prestamo, id_libro, estado FROM prestamo WHERE codigo_ticket = ? FOR UPDATE"
        );
        $stmt->execute([$codigoTicket]);
        $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prestamo) {
            $pdo->rollBack();
            return resultado(false, 'No existe un ticket con ese código.');
        }
        if ($prestamo['estado'] !== 'solicitado') {
            $pdo->rollBack();
            return resultado(false, 'Este ticket no está pendiente de entrega.');
        }

        // Resta una copia solo si todavía queda alguna
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
        return resultado(true, 'Entrega registrada. El préstamo vence en ' . $dias . ' días.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error en validarEntrega: ' . $e->getMessage());
        return resultado(false, 'No se pudo registrar la entrega.');
    }
}

// BIBLIOTECARIO valida al RECIBIR el libro: pasa a 'devuelto' y suma una copia.
// Funciona con 'devolucion_solicitada' y también con 'activo' (devolución directa en el mostrador).
function validarDevolucion(PDO $pdo, string $codigoTicket): array
{
    try {
        $pdo->beginTransaction();

        $codigoTicket = strtoupper(trim($codigoTicket));

        $stmt = $pdo->prepare(
            "SELECT id_prestamo, id_libro, estado FROM prestamo WHERE codigo_ticket = ? FOR UPDATE"
        );
        $stmt->execute([$codigoTicket]);
        $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prestamo) {
            $pdo->rollBack();
            return resultado(false, 'No existe un ticket con ese código.');
        }
        if ($prestamo['estado'] !== 'activo' && $prestamo['estado'] !== 'devolucion_solicitada') {
            $pdo->rollBack();
            return resultado(false, 'Este ticket no tiene un libro pendiente de devolver.');
        }

        // Suma una copia sin pasar del total de copias
        $stmt = $pdo->prepare(
            "UPDATE libro SET disponibles = LEAST(copias, disponibles + 1) WHERE id_libro = ?"
        );
        $stmt->execute([$prestamo['id_libro']]);

        $stmt = $pdo->prepare("UPDATE prestamo SET estado = 'devuelto' WHERE id_prestamo = ?");
        $stmt->execute([$prestamo['id_prestamo']]);

        $pdo->commit();
        return resultado(true, 'Devolución registrada.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error en validarDevolucion: ' . $e->getMessage());
        return resultado(false, 'No se pudo registrar la devolución.');
    }
}

// ESTUDIANTE envía una reseña
function guardarResena(PDO $pdo, int $idUsuario, string $texto): array
{
    $texto = trim($texto);

    if ($texto === '') {
        return resultado(false, 'Escribe tu reseña antes de enviarla.');
    }
    if (mb_strlen($texto) > 1000) {
        return resultado(false, 'La reseña es muy larga (máximo 1000 caracteres).');
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO resena (id_usuario, cuerpo) VALUES (?, ?)");
        $stmt->execute([$idUsuario, $texto]);

        return resultado(true, '¡Gracias! Tu reseña fue enviada.');
    } catch (Throwable $e) {
        error_log('Error en guardarResena: ' . $e->getMessage());
        return resultado(false, 'No se pudo guardar la reseña. Inténtalo de nuevo.');
    }
}

// ------------------------------------------------------------
// 5. GESTIÓN DE LIBROS (bibliotecario)
// ------------------------------------------------------------

/**
 * Valida y guarda la portada subida. Si no se envía archivo, conserva la
 * portada actual. Devuelve ['ok', 'ruta', 'nueva'] o un resultado de error.
 */
function procesarPortadaLibro(?array $archivo, ?string $portadaActual): array
{
    if ($archivo === null || !isset($archivo['error'])) {
        return ['ok' => true, 'ruta' => $portadaActual, 'nueva' => false];
    }

    $codigoError = (int) $archivo['error'];

    if ($codigoError === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'ruta' => $portadaActual, 'nueva' => false];
    }

    if ($codigoError !== UPLOAD_ERR_OK) {
        return resultado(false, 'No se pudo subir la portada. Inténtalo de nuevo.');
    }

    $tamano = (int) ($archivo['size'] ?? 0);

    if ($tamano <= 0) {
        return resultado(false, 'El archivo de la portada está vacío.');
    }

    if ($tamano > 5 * 1024 * 1024) {
        return resultado(false, 'La portada no puede superar los 5 MB.');
    }

    $rutaTemporal = (string) ($archivo['tmp_name'] ?? '');

    if ($rutaTemporal === '' || !is_uploaded_file($rutaTemporal)) {
        return resultado(false, 'No se pudo leer la portada subida.');
    }

    $tipo = @getimagesize($rutaTemporal);

    if ($tipo === false) {
        return resultado(false, 'El archivo de la portada no es una imagen válida.');
    }

    $tiposPermitidos = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];

    if (!isset($tiposPermitidos[$tipo[2]])) {
        return resultado(false, 'La portada debe ser una imagen JPG, PNG o WEBP.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($rutaTemporal);
    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];

    if ($mime === false || !in_array($mime, $mimesPermitidos, true)) {
        return resultado(false, 'El contenido del archivo no corresponde a una imagen permitida.');
    }

    $directorio = __DIR__ . '/Imagenes/Libros';

    if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
        return resultado(false, 'No se pudo preparar la carpeta de portadas.');
    }

    $nombreArchivo = 'libro_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4))
        . '.' . $tiposPermitidos[$tipo[2]];
    $destino = $directorio . '/' . $nombreArchivo;

    if (!move_uploaded_file($rutaTemporal, $destino)) {
        return resultado(false, 'No se pudo guardar la portada. Inténtalo de nuevo.');
    }

    return [
        'ok'    => true,
        'ruta'  => 'Imagenes/Libros/' . $nombreArchivo,
        'nueva' => true,
    ];
}

// Borra una portada anterior solo si está dentro de Imagenes/Libros.
function eliminarPortadaLibro(?string $ruta): void
{
    if ($ruta === null || !preg_match('#^Imagenes/Libros/[A-Za-z0-9._-]+$#', $ruta)) {
        return;
    }

    $archivo = __DIR__ . '/' . $ruta;

    if (is_file($archivo)) {
        @unlink($archivo);
    }
}

/**
 * BIBLIOTECARIO: crea un libro ($idLibro null) o actualiza uno existente.
 * Al editar se conservan los ejemplares prestados: disponibles = copias - prestados.
 */
function guardarLibro(PDO $pdo, array $datos, ?array $archivoPortada = null, ?int $idLibro = null): array
{
    $titulo = trim((string) ($datos['titulo'] ?? ''));
    $autor = trim((string) ($datos['autor'] ?? ''));
    $categoria = trim((string) ($datos['categoria'] ?? ''));
    $descripcion = trim((string) ($datos['descripcion'] ?? ''));
    $copias = filter_var($datos['copias'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 9999],
    ]);

    if ($titulo === '') {
        return resultado(false, 'Escribe el título del libro.');
    }
    if (mb_strlen($titulo) > 255) {
        return resultado(false, 'El título es demasiado largo (máximo 255 caracteres).');
    }
    if ($autor === '') {
        return resultado(false, 'Escribe el autor del libro.');
    }
    if (mb_strlen($autor) > 150) {
        return resultado(false, 'El autor es demasiado largo (máximo 150 caracteres).');
    }

    if ($categoria === '') {
        $categoria = 'General';
    }
    if (mb_strlen($categoria) > 50) {
        return resultado(false, 'La categoría es demasiado larga (máximo 50 caracteres).');
    }
    if (mb_strlen($descripcion) > 3000) {
        return resultado(false, 'La sinopsis es demasiado larga (máximo 3000 caracteres).');
    }
    if ($copias === false) {
        return resultado(false, 'El número de copias debe ser un entero mayor que cero.');
    }

    $libroActual = null;
    $rutaPortada = null;
    $portadaNueva = false;

    try {
        if ($idLibro !== null) {
            $stmt = $pdo->prepare(
                "SELECT id_libro, portada, copias, disponibles
                 FROM libro WHERE id_libro = ? LIMIT 1"
            );
            $stmt->execute([$idLibro]);
            $libroActual = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$libroActual) {
                return resultado(false, 'El libro que intentas editar no existe.');
            }
        }

        $portada = procesarPortadaLibro($archivoPortada, $libroActual['portada'] ?? null);

        if (!$portada['ok']) {
            return $portada;
        }

        $rutaPortada = $portada['ruta'] ?? null;
        $portadaNueva = !empty($portada['nueva']);

        $pdo->beginTransaction();

        if ($idLibro === null) {
            $stmt = $pdo->prepare(
                "INSERT INTO libro (titulo, autor, categoria, descripcion, portada, copias, disponibles)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $titulo,
                $autor,
                $categoria,
                $descripcion !== '' ? $descripcion : null,
                $rutaPortada,
                $copias,
                $copias,
            ]);

            $nuevoId = (int) $pdo->lastInsertId();
            $pdo->commit();

            return resultado(true, 'Libro agregado correctamente.', ['id_libro' => $nuevoId]);
        }

        // Bloquea la fila para calcular las copias prestadas con datos actuales.
        $stmt = $pdo->prepare(
            "SELECT copias, disponibles FROM libro WHERE id_libro = ? FOR UPDATE"
        );
        $stmt->execute([$idLibro]);
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$actual) {
            $pdo->rollBack();
            if ($portadaNueva) {
                eliminarPortadaLibro($rutaPortada);
            }
            return resultado(false, 'El libro que intentas editar ya no existe.');
        }

        $prestados = max(0, (int) $actual['copias'] - (int) $actual['disponibles']);

        if ((int) $copias < $prestados) {
            $pdo->rollBack();
            if ($portadaNueva) {
                eliminarPortadaLibro($rutaPortada);
            }
            return resultado(
                false,
                'No puedes dejar el total de copias por debajo de los ' . $prestados
                . ' ejemplar(es) que están prestados.'
            );
        }

        $disponibles = (int) $copias - $prestados;

        $stmt = $pdo->prepare(
            "UPDATE libro
             SET titulo = ?, autor = ?, categoria = ?, descripcion = ?,
                 portada = ?, copias = ?, disponibles = ?
             WHERE id_libro = ?"
        );
        $stmt->execute([
            $titulo,
            $autor,
            $categoria,
            $descripcion !== '' ? $descripcion : null,
            $rutaPortada,
            $copias,
            $disponibles,
            $idLibro,
        ]);

        $pdo->commit();

        if ($portadaNueva && $libroActual !== null
            && !empty($libroActual['portada'])
            && $libroActual['portada'] !== $rutaPortada) {
            eliminarPortadaLibro($libroActual['portada']);
        }

        return resultado(true, 'Libro actualizado correctamente.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($portadaNueva) {
            eliminarPortadaLibro($rutaPortada);
        }

        error_log('Error en guardarLibro: ' . $e->getMessage());
        return resultado(false, 'No se pudo guardar el libro. Inténtalo de nuevo.');
    }
}
