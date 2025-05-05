<!-- filepath: c:\xampp\htdocs\inmobiliaria\php\vistas\propiedades.php -->
<?php
session_start();
include '../../config/db.php'; // Conexión a la base de datos

// Consulta para obtener todas las propiedades
$query = "SELECT * FROM propiedades";
$resultado = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propiedades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: none;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link active" href="#">Propiedades</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contratos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Reportes</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Lista de Propiedades -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Propiedades Disponibles</h2>
        <div class="row">
            <?php while ($propiedad = $resultado->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="<?= htmlspecialchars($propiedad['imagen']) ?>" class="card-img-top" alt="Propiedad">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($propiedad['titulo']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($propiedad['descripcion']) ?></p>
                            <p class="card-text"><strong>Precio:</strong> $<?= number_format($propiedad['precio'], 2) ?></p>
                            <a href="detalle_propiedad.php?id=<?= $propiedad['id'] ?>" class="btn btn-primary">Ver Detalles</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <footer class="text-white text-center py-4 mt-5 bg-dark">
        <p>&copy; 2025 Sistema de Gestión de Propiedades Inmobiliarias. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>