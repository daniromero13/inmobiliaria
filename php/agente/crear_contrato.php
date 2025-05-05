<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 4) {
    header('Location: ../login.php');
    exit();
}
include '../../config/db.php';

$agente_id = $_SESSION['id_usuario'];
$query = "SELECT * FROM propiedades WHERE agente_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $agente_id);
$stmt->execute();
$propiedades = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propiedad_id = $_POST['propiedad_id'];
    $arrendatario_id = $_POST['arrendatario_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $monto = $_POST['monto'];

    $stmt = $conn->prepare("INSERT INTO contratos (propiedad_id, arrendatario_id, fecha_inicio, fecha_fin, monto) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissi", $propiedad_id, $arrendatario_id, $fecha_inicio, $fecha_fin, $monto);
    if ($stmt->execute()) {
        header("Location: historial_contratos.php");
        exit();
    } else {
        echo "Error al crear el contrato.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Crear Contrato</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="propiedad_id" class="form-label">Propiedad</label>
            <select class="form-select" id="propiedad_id" name="propiedad_id" required>
                <?php while ($propiedad = $propiedades->fetch_assoc()): ?>
                    <option value="<?= $propiedad['id'] ?>"><?= htmlspecialchars($propiedad['titulo']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="arrendatario_id" class="form-label">ID del Arrendatario</label>
            <input type="number" class="form-control" id="arrendatario_id" name="arrendatario_id" required>
        </div>
        <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
        </div>
        <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
        </div>
        <div class="mb-3">
            <label for="monto" class="form-label">Monto</label>
            <input type="number" class="form-control" id="monto" name="monto" required>
        </div>
        <button type="submit" class="btn btn-primary">Crear Contrato</button>
    </form>
    <a href="../vistas/agente.php" class="btn btn-secondary mt-3">Volver</a>
</div>
</body>
</html>
