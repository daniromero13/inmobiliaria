<!-- filepath: c:\xampp\htdocs\inmobiliaria\php\vistas\detalle_propiedad.php -->
<?php
session_start();
include '../../config/db.php'; // Asegúrate de que esta ruta sea correcta

// Obtener el ID de la propiedad desde la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consulta para obtener los detalles de la propiedad
$query = "SELECT * FROM propiedades WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$propiedad = $resultado->fetch_assoc();

if (!$propiedad) {
    die("Propiedad no encontrada.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Propiedad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Inmobiliaria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../../html/index.html">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="propiedades.php">Propiedades</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Detalle de la propiedad -->
    <div class="container mt-5">
        <h2 class="text-center mb-4"><?= htmlspecialchars($propiedad['titulo']) ?></h2>
        <div class="row">
            <div class="col-md-6">
                <img src="<?= htmlspecialchars($propiedad['imagen']) ?>" class="img-fluid" alt="Propiedad">
            </div>
            <div class="col-md-6">
                <p><strong>Descripción:</strong> <?= htmlspecialchars($propiedad['descripcion']) ?></p>
                <p><strong>Precio:</strong> $<?= number_format($propiedad['precio'], 2) ?></p>
                <p><strong>Ubicación:</strong> <?= htmlspecialchars($propiedad['ubicacion']) ?></p>
                <a href="propiedades.php" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="text-white text-center py-4 mt-5 bg-dark">
        <p>&copy; 2025 Sistema de Gestión de Propiedades Inmobiliarias. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>