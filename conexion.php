<?php
// credenciales y parametros
$host = '127.0.0.1'; 
$db = 'biblioteca';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$opciones = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $opciones);
} catch (PDOException $e) {
    // El detalle técnico queda en el log; las páginas muestran un mensaje amable.
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    throw new PDOException("No se pudo conectar con la base de datos.");
}

?>