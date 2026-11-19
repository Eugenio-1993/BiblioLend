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
  * $activos = contarPrestamosActivos($pdo, $_SESSION['usuario_id']);
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

// Usuarios registrados (solo estudiantes). Para contar a todos, quita el WHERE.
function contarUsuariosRegistrados(PDO $pdo): int
{
    return (int) $pdo->query("SELECT COUNT(*) FROM usuario WHERE rol = 'estudiante'")->fetchColumn();
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
            return ['texto' => 'Pendiente', 'clase' => 'pendiente'];
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

// CATÁLOGO: ids de los libros que este estudiante ya solicitó (para mostrar "Cancelar solicitud")
function librosConSolicitud(PDO $pdo, int $idUsuario): array
{
    $stmt = $pdo->prepare(
        "SELECT id_libro FROM prestamo WHERE id_usuario = ? AND estado = 'solicitado'"
    );
    $stmt->execute([$idUsuario]);

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
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
                return resultado(true, 'Solicitud registrada. Acércate a la biblioteca con tu ticket.', [
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

        $stmt = $pdo->prepare(
            "SELECT id_prestamo, id_libro, estado FROM prestamo WHERE codigo_ticket = ? FOR UPDATE"
        );
        $stmt->execute([trim($codigoTicket)]);
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

        $stmt = $pdo->prepare(
            "SELECT id_prestamo, id_libro, estado FROM prestamo WHERE codigo_ticket = ? FOR UPDATE"
        );
        $stmt->execute([trim($codigoTicket)]);
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
