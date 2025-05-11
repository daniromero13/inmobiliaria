<!-- filepath: c:\xampp\htdocs\inmobiliaria\php\vistas\propiedades.php -->
<?php
session_start();
include '../../config/db.php'; // Conexión a la base de datos

// Consulta para obtener todas las propiedades con propietario y agente
$query = "SELECT p.*, u.nombre_completo AS propietario_nombre, a.nombre_completo AS agente_nombre
          FROM propiedades p
          LEFT JOIN usuarios u ON p.propietario_id = u.id
          LEFT JOIN usuarios a ON p.agente_id = a.id";
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
        .property-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #e5e7eb;
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
                        <?php
                        $imgPath = (!empty($propiedad['imagen']) && file_exists(__DIR__ . '/../../uploads/' . $propiedad['imagen']))
                            ? '../../uploads/' . $propiedad['imagen']
                            : 'https://via.placeholder.com/400x180?text=Sin+Imagen';
                        ?>
                        <img src="<?= $imgPath ?>" class="card-img-top property-img" alt="Propiedad">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($propiedad['titulo']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($propiedad['descripcion']) ?></p>
                            <p class="card-text mb-1"><strong>Precio:</strong> $<?= number_format($propiedad['precio'], 2) ?></p>
                            <p class="card-text mb-1"><strong>Agente:</strong> <?= htmlspecialchars($propiedad['agente_nombre'] ?? 'No asignado') ?></p>
                            <button class="btn btn-primary" 
                                data-bs-toggle="modal"
                                data-bs-target="#modalDetallePropiedad"
                                onclick="mostrarDetalle(
                                    '<?= htmlspecialchars(addslashes($propiedad['titulo'])) ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['descripcion'])) ?>',
                                    '<?= $imgPath ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['agente_nombre'] ?? 'No asignado')) ?>',
                                    '<?= htmlspecialchars(addslashes($propiedad['propietario_nombre'] ?? 'No asignado')) ?>',
                                    '<?= number_format($propiedad['precio'], 2) ?>'
                                )"
                            >Ver Detalles</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal Detalle Propiedad -->
    <div class="modal fade" id="modalDetallePropiedad" tabindex="-1" aria-labelledby="modalDetallePropiedadLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalDetallePropiedadLabel">Detalle de la Propiedad</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <img id="detalle_imagen" src="" alt="Imagen propiedad" class="img-fluid rounded" style="max-height:260px;">
                </div>
                <div class="col-md-6">
                    <h4 id="detalle_titulo"></h4>
                    <p id="detalle_descripcion"></p>
                    <p><strong>Precio:</strong> $<span id="detalle_precio"></span></p>
                    <p><strong>Agente:</strong> <span id="detalle_agente"></span></p>
                    <p><strong>Propietario:</strong> <span id="detalle_propietario"></span></p>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="text-white text-center py-4 mt-5 bg-dark">
        <p>&copy; 2025 Sistema de Gestión de Propiedades Inmobiliarias. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function mostrarDetalle(titulo, descripcion, imagen, agente, propietario, precio) {
        document.getElementById('detalle_titulo').textContent = titulo;
        document.getElementById('detalle_descripcion').textContent = descripcion;
        document.getElementById('detalle_imagen').src = imagen;
        document.getElementById('detalle_agente').textContent = agente;
        document.getElementById('detalle_propietario').textContent = propietario;
        document.getElementById('detalle_precio').textContent = precio;
    }
    </script>
</body>
</html>