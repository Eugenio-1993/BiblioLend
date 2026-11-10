<?php
// 1. Incluimos la conexión del proyecto actual de forma automática
require_once 'conexion.php'; 

$tablaSeleccionada = "";
$registros = [];
$error = "";

// validamos el envió el formulario con el nombre de la tabla
if (isset($_POST['tabla']) && !empty(trim($_POST['tabla']))) {
    // Limpiamos un poco el texto para evitar errores de estacios
    $tablaSeleccionada = trim($_POST['tabla']);

    try {
        // 3. Preparamos y ejecutamos la consulta dinámica
        $sql = "SELECT * FROM `$tablaSeleccionada`";
        $stmt = $pdo->query($sql);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $error = "No se pudo consultar la tabla '$tablaSeleccionada'. Revisa si el nombre es correcto. Error: " . $e->getMessage();
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

        <!-- eror de consulta -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!—condicion para mostrar registros -->
        <?php if (!empty($registros)): ?>
            <div class="card shadow-sm p-4">
                <h4 class="mb-3 text-success">Resultados para: <code><?php echo htmlspecialchars($tablaSeleccionada); ?></code> 
                    <span class="badge bg-secondary fs-6 float-end">Total registros: <?php echo count($registros); ?></span>
                </h4>
                <!—tabla para mostrar registros -->
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
