<?php
session_start();
include '../config/db.php'; // Asegúrate de que esta ruta sea correcta

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ajusta los nombres de los campos según tu formulario y base de datos
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['clave']);

    $query = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $correo);

    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            // Ajusta el nombre del campo de contraseña según tu base de datos
            if (password_verify($contrasena, $usuario['contrasena']) || $usuario['contrasena'] === hash('sha256', $contrasena)) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol_id'] = $usuario['rol_id'];

                // Redireccionar según el rol (ajusta las rutas)
                switch ($usuario['rol_id']) {
                    case 1: // Administrador
                        header("Location: ./vistas/admin.php");
                        break;
                    case 2: // Propietario
                        header("Location: ./vistas/propietario.php");
                        break;
                    case 3: // Arrendatario
                        header("Location: ./vistas/arrendatario.php");
                        break;
                    case 4: // Agente inmobiliario
                        header("Location: ./vistas/agente.php");
                        break;
                    default:
                        header("Location: ../html/login.php?mensaje=Rol no válido&tipo=error");
                        break;
                }
                exit();
            } else {
                header("Location: ../html/login.php?mensaje=Contraseña incorrecta&tipo=error");
                exit();
            }
        } else {
            header("Location: ../html/login.php?mensaje=Usuario no encontrado&tipo=error");
            exit();
        }
    } else {
        header("Location: ../html/login.php?mensaje=Error al ejecutar la consulta&tipo=error");
        exit();
    }
}
?>
