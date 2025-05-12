<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 4) {
    header('Location: ../login.php');
    exit();
}
include '../../config/db.php';

// Obtener lista de propietarios
$propietarios = [];
$result = $conn->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 2");
while ($row = $result->fetch_assoc()) {
    $propietarios[] = $row;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $propietario_id = intval($_POST['propietario_id']);
    $agente_id = $_SESSION['id_usuario'];

    $stmt = $conn->prepare("INSERT INTO propiedades (titulo, descripcion, precio, propietario_id, agente_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdii", $titulo, $descripcion, $precio, $propietario_id, $agente_id);
    if ($stmt->execute()) {
        $mensaje = "Propiedad registrada exitosamente.";
    } else {
        $mensaje = "Error al registrar la propiedad.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Propiedad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <h2 class="mb-4">Registrar Nueva Propiedad</h2>
    <nav class="nav-agente mb-4">
        <a href="propiedades.php"><i class="bi bi-house-door"></i>Mis Propiedades</a>
        <a href="crear_contrato.php"><i class="bi bi-file-earmark-plus"></i>Crear Contrato</a>
        <a href="registrar_pago.php"><i class="bi bi-cash-stack"></i>Registrar Pago</a>
        <a href="historial_pagos.php"><i class="bi bi-receipt"></i>Historial de Pagos</a>
        <a href="historial_contratos.php"><i class="bi bi-clock-history"></i>Historial Contratos</a>
        <a href="reportes_agente.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <?php if ($mensaje): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    <form method="post" class="card p-4 shadow">
        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" name="titulo" id="titulo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="precio" class="form-label">Precio</label>
            <input type="number" name="precio" id="precio" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="propietario_id" class="form-label">Propietario</label>
            <select name="propietario_id" id="propietario_id" class="form-select" required>
                <option value="">Seleccione un propietario</option>
                <?php foreach ($propietarios as $prop): ?>
                    <option value="<?php echo $prop['id']; ?>"><?php echo htmlspecialchars($prop['nombre_completo']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Registrar Propiedad</button>
        <a href="propiedades_asignadas.php" class="btn btn-secondary ms-2">Volver</a>
    </form>
</div>
</body>
</html>
