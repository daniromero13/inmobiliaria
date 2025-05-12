<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Administrador';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
include '../../config/db.php';

// Obtener todas las propiedades con propietario y agente
$query = "SELECT p.*, 
    u.nombre_completo AS propietario_nombre, 
    a.nombre_completo AS agente_nombre 
    FROM propiedades p 
    LEFT JOIN usuarios u ON p.propietario_id = u.id 
    LEFT JOIN usuarios a ON p.agente_id = a.id
    ORDER BY p.id DESC";
$res = $conn->query($query);
$propiedades = [];
while ($row = $res->fetch_assoc()) {
    $propiedades[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propiedades - Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .header-admin {
            background: #2563eb;
            color: #fff;
            border-radius: 0 0 24px 24px;
            padding: 32px 0 24px 0;
            box-shadow: 0 4px 24px 0 rgba(37,99,235,0.08);
            margin-bottom: 0;
        }
        .avatar {
            width: 64px;
            height: 64px;
            background: #fff;
            color: #2563eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0 auto 12px auto;
            box-shadow: 0 2px 8px 0 rgba(37,99,235,0.10);
        }
        .header-admin h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .header-admin .nombre-usuario {
            font-size: 1.15rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }
        .nav-admin {
            background: #fff;
            border-radius: 0 0 18px 18px;
            box-shadow: 0 2px 12px 0 rgba(60,72,88,0.10);
            margin-top: 0;
            margin-bottom: 32px;
            padding: 0.5rem 1rem;
            display: flex;
            justify-content: center;
            gap: 24px;
        }
        .nav-admin a {
            color: #2563eb;
            font-weight: 500;
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 8px;
            transition: background 0.18s, color 0.18s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-admin a:hover, .nav-admin a.active {
            background: #e6f0fa;
            color: #174ea6;
        }
        .main-card {
            max-width: 1200px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
        }
        .property-card {
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(139,92,246,0.10);
            overflow: hidden;
            margin-bottom: 32px;
            background: #fff;
            transition: box-shadow 0.18s, transform 0.18s;
            border: 1.5px solid #ede9fe;
            position: relative;
        }
        .property-card:hover {
            box-shadow: 0 8px 32px 0 rgba(139,92,246,0.18);
            transform: translateY(-6px) scale(1.025);
            border-color: #a78bfa;
        }
        .property-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #e5e7eb;
            border-radius: 18px 18px 0 0;
        }
        .property-card .card-body {
            padding: 1.2rem 1.2rem 1rem 1.2rem;
        }
        .property-card .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }
        .property-card .card-text {
            color: #4b5563;
            margin-bottom: 1.1rem;
        }
        .property-card .mb-1 strong {
            color: #2563eb;
        }
        @media (max-width: 900px) {
            .main-card { padding: 1rem; }
        }
        @media (max-width: 600px) {
            .property-img { height: 120px; }
            .property-card .card-body { padding: 1rem; }
        }
    </style>
</head>
<body>
    <header class="header-admin text-center position-relative">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Administrador</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-admin mb-4">
        <a href="usuarios.php"><i class="bi bi-people"></i>Usuarios</a>
        <a href="propiedades.php" class="active"><i class="bi bi-house-door"></i>Propiedades</a>
        <a href="contratos.php"><i class="bi bi-file-earmark-text"></i>Contratos</a>
        <a href="pagos.php"><i class="bi bi-cash-stack"></i>Pagos</a>
        <a href="reportes_admin.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../vistas/admin.php" class="btn btn-secondary btn-back">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h2 class="mb-0 text-center flex-grow-1" style="font-size:1.7rem;">Todas las Propiedades</h2>
                <span style="width: 90px;"></span>
            </div>
            <div class="mb-3">
                <input type="text" id="buscadorPropiedades" class="form-control" placeholder="Buscar por título, ciudad, propietario, agente...">
            </div>
            <div class="row" id="contenedorPropiedades">
                <?php foreach ($propiedades as $propiedad): ?>
                <?php
                    $imagenes = array_filter(explode(',', $propiedad['imagen']));
                    $imgPrincipal = isset($imagenes[0]) && file_exists('../../uploads/' . $imagenes[0])
                        ? '../../uploads/' . $imagenes[0]
                        : 'https://via.placeholder.com/400x180?text=Sin+Imagen';
                    $descCorta = mb_strlen($propiedad['descripcion']) > 80
                        ? mb_substr($propiedad['descripcion'], 0, 80) . '...'
                        : $propiedad['descripcion'];
                    $estado_prop = isset($propiedad['estado']) ? $propiedad['estado'] : 'Disponible';
                ?>
                <div class="col-md-4 propiedad-tarjeta">
                    <div class="property-card card">
                        <img src="<?= $imgPrincipal ?>" class="property-img card-img-top" alt="Imagen propiedad">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($propiedad['titulo']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($descCorta) ?></p>
                            <p class="mb-1"><strong>Habitaciones:</strong> <?= htmlspecialchars($propiedad['habitaciones']) ?></p>
                            <p class="mb-1"><strong>Baños:</strong> <?= htmlspecialchars($propiedad['banos']) ?></p>
                            <p class="mb-1"><strong>Estrato:</strong> <?= htmlspecialchars($propiedad['estrato']) ?></p>
                            <p class="mb-1"><strong>Precio:</strong> $<?= number_format($propiedad['precio'], 2) ?></p>
                            <p class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($propiedad['ubicacion']) ?></p>
                            <p class="mb-1"><strong>Ciudad:</strong> <?= htmlspecialchars($propiedad['ciudad']) ?></p>
                            <p class="mb-1"><strong>Propietario:</strong> <?= htmlspecialchars($propiedad['propietario_nombre'] ?? 'No asignado') ?></p>
                            <p class="mb-1"><strong>Agente:</strong> <?= htmlspecialchars($propiedad['agente_nombre'] ?? 'No asignado') ?></p>
                            <p class="mb-1"><strong>Estado:</strong> <?= htmlspecialchars($estado_prop) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($propiedades)): ?>
                <div class="col-12">
                    <p class="text-center text-muted">No hay propiedades registradas.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('buscadorPropiedades').addEventListener('input', function() {
        const texto = this.value.toLowerCase();
        const tarjetas = document.querySelectorAll('.propiedad-tarjeta');
        tarjetas.forEach(function(card) {
            const contenido = card.textContent.toLowerCase();
            card.style.display = contenido.includes(texto) ? '' : 'none';
        });
    });
    </script>
</body>
</html>
