<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 4) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agente Inmobiliario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow mb-4">
        <div class="card-body text-center">
            <h1 class="card-title">Bienvenido Agente</h1>
            <p class="card-text">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>.</p>
            <a href="../../php/logout.php" class="btn btn-danger">Cerrar sesión</a>
        </div>
    </div>

    <!-- Gestión de Propiedades Asignadas -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h2 class="card-title">Propiedades Asignadas</h2>
            <a href="../agente/propiedades_asignadas.php" class="btn btn-primary">Ver Propiedades</a>
        </div>
    </div>

    <!-- Crear Contratos -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h2 class="card-title">Crear Contratos</h2>
            <a href="../agente/crear_contrato.php" class="btn btn-primary">Nuevo Contrato</a>
        </div>
    </div>

    <!-- Registrar Pagos -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h2 class="card-title">Registrar Pagos</h2>
            <a href="../../php/agente/registrar_pago.php" class="btn btn-primary">Registrar Pago</a>
        </div>
    </div>

    <!-- Historial de Contratos -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h2 class="card-title">Historial de Contratos</h2>
            <a href="../../php/agente/historial_contratos.php" class="btn btn-primary">Ver Historial</a>
        </div>
    </div>
</div>

</body>
</html>
