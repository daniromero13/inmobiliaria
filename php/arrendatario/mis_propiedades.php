<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 3) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Arrendatario';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));

include '../../config/db.php';
$id_usuario = $_SESSION['id_usuario'];

// Consulta propiedades arrendadas (actuales y pasadas)
$query = "SELECT p.titulo, p.descripcion, p.imagen, p.ubicacion, p.ciudad, p.precio, c.fecha_inicio, c.fecha_fin, c.estado
          FROM contratos c
          JOIN propiedades p ON c.propiedad_id = p.id
          WHERE c.arrendatario_id = ?
          ORDER BY c.fecha_inicio DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$actuales = [];
$pasados = [];
$hoy = date('Y-m-d');
while ($row = $result->fetch_assoc()) {
    // Buscar contrato vigente para esta propiedad
    $contratoVigente = false;
    $stmtContrato = $conn->prepare("SELECT c.id FROM contratos c WHERE c.propiedad_id = (SELECT id FROM propiedades WHERE titulo = ?) AND c.estado = 'Vigente' LIMIT 1");
    $stmtContrato->bind_param("s", $row['titulo']);
    $stmtContrato->execute();
    $resContrato = $stmtContrato->get_result();
    $contratoId = null;
    if ($rowC = $resContrato->fetch_assoc()) {
        $contratoId = $rowC['id'];
        $contratoVigente = true;
    }
    // Si hay contrato vigente, verificar si tiene pagos
    $ocupado = false;
    if ($contratoId) {
        $stmtPagos = $conn->prepare("SELECT COUNT(*) as total FROM pagos WHERE contrato_id = ?");
        $stmtPagos->bind_param("i", $contratoId);
        $stmtPagos->execute();
        $resPagos = $stmtPagos->get_result();
        if ($rowP = $resPagos->fetch_assoc()) {
            if ($rowP['total'] > 0) {
                $ocupado = true;
            }
        }
    }
    // Si está ocupado, siempre va en actuales
    if ($ocupado || $row['estado'] == 'Vigente') {
        $row['estado'] = $ocupado ? 'Ocupado' : $row['estado'];
        $actuales[] = $row;
    } else {
        $pasados[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propiedades Arrendadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .header-arrendatario {
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
        .header-arrendatario h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .header-arrendatario .nombre-usuario {
            font-size: 1.15rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }
        .nav-arrendatario {
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
        .nav-arrendatario a {
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
        .nav-arrendatario a:hover, .nav-arrendatario a.active {
            background: #e6f0fa;
            color: #174ea6;
        }
        .main-card {
            max-width: 1100px;
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
    <header class="header-arrendatario text-center">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Bienvenido Arrendatario</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-arrendatario mb-4">
        <a href="mis_propiedades.php" class="active"><i class="bi bi-house-door"></i>Propiedades Arrendadas</a>
        <a href="mis_contratos.php"><i class="bi bi-file-earmark-text"></i>Mis Contratos</a>
        <a href="mis_pagos.php"><i class="bi bi-clock-history"></i>Historial de Pagos</a>
        <a href="subir_comprobante.php"><i class="bi bi-upload"></i>Subir Comprobante</a>
        <a href="reportes_arrendatario.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../vistas/arrendatario.php" class="btn btn-secondary btn-back">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h2 class="mb-0 text-center flex-grow-1" style="font-size:1.7rem;">Propiedades Arrendadas</h2>
                <span style="width: 90px;"></span>
            </div>
            <!-- Arriendos actuales -->
            <h4 class="mb-3">Arriendos actuales</h4>
            <?php if (empty($actuales) && empty($pasados)): ?>
                <p class="text-center">No tienes ningún arriendo registrado.</p>
            <?php elseif (empty($actuales)): ?>
                <p class="text-center">No tienes arriendos actuales.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($actuales as $prop): ?>
                    <?php
                        $imagenes = array_filter(explode(',', $prop['imagen'] ?? ''));
                        $imgPrincipal = isset($imagenes[0]) && file_exists('../../uploads/' . $imagenes[0])
                            ? '../../uploads/' . $imagenes[0]
                            : 'https://via.placeholder.com/400x180?text=Sin+Imagen';
                        $descCorta = mb_strlen($prop['descripcion']) > 80
                            ? mb_substr($prop['descripcion'], 0, 80) . '...'
                            : $prop['descripcion'];
                    ?>
                    <div class="col-md-4">
                        <div class="property-card card">
                            <img src="<?= $imgPrincipal ?>" class="property-img card-img-top" alt="Imagen propiedad">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($prop['titulo']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($descCorta) ?></p>
                                <p class="mb-1"><strong>Ciudad:</strong> <?= htmlspecialchars($prop['ciudad']) ?></p>
                                <p class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($prop['ubicacion']) ?></p>
                                <p class="mb-1"><strong>Precio:</strong> $<?= number_format($prop['precio'], 2) ?></p>
                                <p class="mb-1"><strong>Fecha inicio:</strong> <?= htmlspecialchars($prop['fecha_inicio']) ?></p>
                                <p class="mb-1"><strong>Fecha fin:</strong> <?= htmlspecialchars($prop['fecha_fin']) ?></p>
                                <span class="badge <?= 
                                    $prop['estado'] == 'Ocupado' ? 'bg-warning text-dark' : 
                                    ($prop['estado'] == 'Firmado' ? 'bg-success' : 'bg-success') 
                                ?>" style="font-size:1rem;">
                                    <?= $prop['estado'] == 'Ocupado' ? 'Ocupado' : ($prop['estado'] == 'Firmado' ? 'Firmado' : 'Activo') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Arriendos pasados -->
            <h4 class="mt-4 mb-3">Arriendos pasados</h4>
            <?php if (empty($pasados)): ?>
                <p class="text-center">No hay ningún arriendo pasado.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($pasados as $prop): ?>
                    <?php
                        $imagenes = array_filter(explode(',', $prop['imagen'] ?? ''));
                        $imgPrincipal = isset($imagenes[0]) && file_exists('../../uploads/' . $imagenes[0])
                            ? '../../uploads/' . $imagenes[0]
                            : 'https://via.placeholder.com/400x180?text=Sin+Imagen';
                        $descCorta = mb_strlen($prop['descripcion']) > 80
                            ? mb_substr($prop['descripcion'], 0, 80) . '...'
                            : $prop['descripcion'];
                    ?>
                    <div class="col-md-4">
                        <div class="property-card card">
                            <img src="<?= $imgPrincipal ?>" class="property-img card-img-top" alt="Imagen propiedad">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($prop['titulo']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($descCorta) ?></p>
                                <p class="mb-1"><strong>Ciudad:</strong> <?= htmlspecialchars($prop['ciudad']) ?></p>
                                <p class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($prop['ubicacion']) ?></p>
                                <p class="mb-1"><strong>Precio:</strong> $<?= number_format($prop['precio'], 2) ?></p>
                                <p class="mb-1"><strong>Fecha inicio:</strong> <?= htmlspecialchars($prop['fecha_inicio']) ?></p>
                                <p class="mb-1"><strong>Fecha fin:</strong> <?= htmlspecialchars($prop['fecha_fin']) ?></p>
                                <span class="badge 
                                    <?= $prop['estado'] == 'Cancelado' ? 'bg-secondary' : 
                                        ($prop['estado'] == 'Cancelación anticipada' ? 'bg-warning text-dark' : 
                                            ($prop['estado'] == 'Firmado' ? 'bg-success' : 'bg-light text-dark')) ?>">
                                    <?= htmlspecialchars($prop['estado']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
