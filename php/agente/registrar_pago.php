<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 4) {
    header('Location: ../login.php');
    exit();
}
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrato_id = $_POST['contrato_id'];
    $monto = $_POST['monto'];
    $fecha_pago = $_POST['fecha_pago'];

    // Verificar que el contrato_id existe en la tabla contratos
    $checkContratoQuery = "SELECT id FROM contratos WHERE id = ?";
    $stmt = $conn->prepare($checkContratoQuery);
    $stmt->bind_param("i", $contrato_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<div class='alert alert-danger'>Error: El contrato especificado no existe.</div>";
    } else {
        // Insertar el pago en la tabla pagos
        $stmt = $conn->prepare("INSERT INTO pagos (contrato_id, monto, fecha_pago) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $contrato_id, $monto, $fecha_pago);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Pago registrado exitosamente.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error al registrar el pago: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Registrar Pago</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="contrato_id" class="form-label">ID del Contrato</label>
            <input type="number" class="form-control" id="contrato_id" name="contrato_id" required>
        </div>
        <div class="mb-3">
            <label for="monto" class="form-label">Monto</label>
            <input type="number" class="form-control" id="monto" name="monto" required>
        </div>
        <div class="mb-3">
            <label for="fecha_pago" class="form-label">Fecha de Pago</label>
            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrar</button>
    </form>
    <a href="../vistas/agente.php" class="btn btn-secondary mt-3">Volver</a>
</div>
</body>
</html>
