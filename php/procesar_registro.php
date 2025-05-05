<?php
session_start();
require_once '../config/db.php'; // Asegúrate de que el db.php esté bien conectado

$nombre_completo = $_POST['nombre_completo'];
$correo = $_POST['correo'];
$telefono = $_POST['telefono'];
$contrasena = $_POST['contrasena'];
$confirmar_contrasena = $_POST['confirmar_contrasena'];
$rol = $_POST['rol'];

// Validar contraseñas iguales
if ($contrasena !== $confirmar_contrasena) {
    $_SESSION['mensaje'] = "Las contraseñas no coinciden.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../html/registro.php");
    exit();
}

// Validar correo o teléfono duplicados
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? OR telefono = ?");
$stmt->bind_param("ss", $correo, $telefono);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['mensaje'] = "El correo o teléfono ya están registrados.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../html/registro.php");
    exit();
}

// Obtener rol_id
$stmt = $conn->prepare("SELECT id FROM roles WHERE nombre = ?");
$stmt->bind_param("s", $rol);
$stmt->execute();
$result = $stmt->get_result();
$rol_id = $result->fetch_assoc()['id'];

// Insertar nuevo usuario
$contrasena_hash = hash('sha256', $contrasena);
$stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, correo, telefono, contrasena, rol_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $nombre_completo, $correo, $telefono, $contrasena_hash, $rol_id);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Registro exitoso. Ahora puedes iniciar sesión.";
    $_SESSION['tipo_mensaje'] = "success";
    header("Location: ../html/registro.php");
    exit();
}
 else {
    $_SESSION['mensaje'] = "Error al registrar. Intenta de nuevo.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../html/registro.php");
    exit();
}
?>
