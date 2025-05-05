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
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propiedades Asignadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Propiedades Asignadas</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($propiedad = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($propiedad['id']) ?></td>
                    <td><?= htmlspecialchars($propiedad['titulo']) ?></td>
                    <td><?= htmlspecialchars($propiedad['descripcion']) ?></td>
                    <td>$<?= number_format($propiedad['precio'], 2) ?></td>
                    <td>
                        <a href="editar_propiedad.php?id=<?= $propiedad['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="../vistas/agente.php" class="btn btn-secondary">Volver</a>
</div>
</body>
</html>
