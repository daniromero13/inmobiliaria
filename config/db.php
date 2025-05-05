<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'inmobiliaria';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si la columna agente_id existe en la tabla propiedades
$checkColumnQuery = "SHOW COLUMNS FROM propiedades LIKE 'agente_id'";
$result = $conn->query($checkColumnQuery);

if ($result->num_rows === 0) {
    // Agregar la columna agente_id si no existe
    $alterTableQuery = "ALTER TABLE propiedades ADD COLUMN agente_id INT NULL AFTER id";
    if (!$conn->query($alterTableQuery)) {
        die("Error al agregar la columna agente_id: " . $conn->error);
    }
}
?>
