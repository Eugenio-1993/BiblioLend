<?php
include 'conexion.php'; 

try {
    $stmt = $pdo->query("SELECT id_usuario, contrasena FROM usuario");
    $usuarios = $stmt->fetchAll();

    $stmtUpdate = $pdo->prepare("UPDATE usuario SET contrasena = ? WHERE id_usuario = ?");

    $actualizados = 0;

    foreach ($usuarios as $fila) {
        $id = $fila['id_usuario'];
        $passwdActual = $fila['contrasena'];

        $info = password_get_info($passwdActual);

        if ($info['algo'] === null || $info['algoName'] === 'unknown') {
            
            $nuevohash = password_hash($passwdActual, PASSWORD_DEFAULT);

            $stmtUpdate->execute([$nuevohash, $id]);
            $actualizados++;
            
            echo "Usuario ID $id actualizado con éxito.<br>";
        } else {
            echo "Usuario ID $id ya tenía la contraseña hasheada (omitido).<br>";
        }
    }

    echo "<br><strong>¡Proceso finalizado! Se actualizaron $actualizados contraseñas correctamente.</strong>";

} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
}
?>