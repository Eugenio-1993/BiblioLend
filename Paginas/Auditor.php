<?php
// Página de auditoría: solo administradores y conexión centralizada.
require_once __DIR__ . '/../autenticacion.php';
requerirRol('administrador');

$tablaSeleccionada = "";
$registros = [];
$error = "";

// Validamos el nombre de la tabla antes de consultarla.
if (isset($_POST['tabla']) && trim($_POST['tabla']) !== '') {
    $tablaSeleccionada = trim($_POST['tabla']);

    if (!preg_match('/^[A-Za-z0-9_]+$/', $tablaSeleccionada)) {
        $error = "El nombre de la tabla solo puede contener letras, números y guiones bajos.";
    } else {
        try {
            // La conexión solo se necesita al ejecutar una consulta.
            require_once __DIR__ . '/../conexion.php';

            $stmtExiste = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :tabla"
            );
            $stmtExiste->execute(['tabla' => $tablaSeleccionada]);

            if ((int) $stmtExiste->fetchColumn() === 0) {
                $error = "La tabla indicada no existe en la base de datos.";
            } else {
                // El nombre ya fue validado y existe: se puede interpolar con seguridad.
                $stmt = $pdo->query("SELECT * FROM `$tablaSeleccionada`");
                $registros = $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            error_log("Error en Auditor: " . $e->getMessage());
            $error = "No se pudo consultar la tabla indicada.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditor de Base de Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container my-5">
        <div class="card shadow-sm p-4 mb-4">
            <h2 class="mb-3 text-primary">Auditor de Conexión y Datos</h2>
            <p class="text-muted">Escribe el nombre de la tabla que quieres consultar en esta base de datos.</p>

            <!-- Formulario para ingresar el nombre de la tabla -->
            <form method="POST" class="row g-3">
                <div class="col-auto flex-grow-1">
                    <input type="text" class="form-control" name="tabla" placeholder="Ej: usuarios, productos, clientes..." value="<?php echo htmlspecialchars($tablaSeleccionada); ?>" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success">Consultar Tabla</button>
                </div>
            </form>
        </div>

        <!-- Error de consulta -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- Condición para mostrar registros -->
        <?php if (!empty($registros)): ?>
            <div class="card shadow-sm p-4">
                <h4 class="mb-3 text-success">Resultados para: <code><?php echo htmlspecialchars($tablaSeleccionada); ?></code> 
                    <span class="badge bg-secondary fs-6 float-end">Total registros: <?php echo count($registros); ?></span>
                </h4>
                <!-- Tabla para mostrar registros -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <?php foreach (array_keys($registros[0]) as $columna): ?>
                                    <th><?php echo htmlspecialchars($columna); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Recorremos cada fila de la base de datos -->
                            <?php foreach ($registros as $fila): ?>
                                <tr>
                                    <?php foreach ($fila as $valor): ?>
                                        <td>
                                            <?php 
                                                // condición para valores nullos
                                                echo ($valor === null) ? '<em class="text-muted">NULL</em>' : htmlspecialchars($valor); 
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif (isset($_POST['tabla']) && empty($error)): ?>
            <div class="alert alert-warning" role="alert">
                La consulta se ejecutó con éxito, pero la tabla <strong><?php echo htmlspecialchars($tablaSeleccionada); ?></strong> está vacía.
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
