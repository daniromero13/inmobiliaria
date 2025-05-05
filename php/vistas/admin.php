<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../login.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow mb-4">
        <div class="card-body text-center">
            <h1 class="card-title">Bienvenido Administrador</h1>
            <p class="card-text">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>.</p>
            <a href="../../php/logout.php" class="btn btn-danger">Cerrar sesión</a>
        </div>
    </div>

    <!-- Gestión de Usuarios -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h2 class="card-title">Gestión de Usuarios</h2>
            <a href="../admin/usuarios.php" class="btn btn-primary">Administrar Usuarios</a>
        </div>
    </div>

    <!-- Gestión de Propiedades -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h2 class="card-title">Gestión de Propiedades</h2>
            <a href="../admin/propiedades.php" class="btn btn-primary">Administrar Propiedades</a>
        </div>
    </div>

    <!-- Asignar Propiedades a Agentes -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h2 class="card-title">Asignar Propiedades a Agentes</h2>
            <a href="../admin/asignar_propiedades.php" class="btn btn-primary">Asignar Propiedades</a>
        </div>
    </div>

    <!-- Historial de Contratos -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h2 class="card-title">Historial de Contratos</h2>
            <a href="../admin/contratos.php" class="btn btn-primary">Ver Historial de Contratos</a>
        </div>
    </div>
</div>

</body>
</html>
