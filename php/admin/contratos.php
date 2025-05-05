<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../login.php');
    exit();
}
include '../../config/db.php';

$query = "SELECT c.id, p.titulo AS propiedad, u.nombre_completo AS arrendatario, c.fecha_inicio, c.fecha_fin, c.monto 
          FROM contratos c
          JOIN propiedades p ON c.propiedad_id = p.id
          JOIN usuarios u ON c.arrendatario_id = u.id";
$resultado = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Contratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Historial de Contratos</h1>
    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Propiedad</th>
                    <th>Arrendatario</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($contrato = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($contrato['id']) ?></td>
                        <td><?= htmlspecialchars($contrato['propiedad']) ?></td>
                        <td><?= htmlspecialchars($contrato['arrendatario']) ?></td>
                        <td><?= htmlspecialchars($contrato['fecha_inicio']) ?></td>
                        <td><?= htmlspecialchars($contrato['fecha_fin']) ?></td>
                        <td>$<?= number_format($contrato['monto'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No hay contratos registrados.</p>
    <?php endif; ?>
    <a href="../vistas/admin.php" class="btn btn-secondary">Volver</a>
</div>
</body>
</html>
