<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../login.php');
    exit();
}
include '../../config/db.php';

$query = "SELECT * FROM propiedades";
$resultado = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Propiedades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Gestión de Propiedades</h1>
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
                        <a href="../vistas/detalle_propiedad.php?id=<?= $propiedad['id'] ?>" class="btn btn-info btn-sm">Ver</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="../vistas/admin.php" class="btn btn-secondary">Volver</a>
</div>
</body>
</html>
