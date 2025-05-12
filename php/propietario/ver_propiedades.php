<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Usuario';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
include '../../config/db.php';

$propietario_id = $_SESSION['id_usuario'];

// Consultar propiedades asignadas a este propietario (sin la columna 'area')
$stmt = $conn->prepare(
    "SELECT p.*, u.nombre_completo AS propietario_nombre
     FROM propiedades p
     LEFT JOIN usuarios u ON p.propietario_id = u.id
     WHERE p.propietario_id = ?"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$result = $stmt->get_result();
$propiedades = [];
while ($row = $result->fetch_assoc()) {
    $propiedades[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Propiedades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .header-propietario {
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
        .header-propietario h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .header-propietario .nombre-usuario {
            font-size: 1.15rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }
        .nav-propietario {
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
        .nav-propietario a {
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
        .nav-propietario a:hover, .nav-propietario a.active {
            background: #e6f0fa;
            color: #174ea6;
        }
        .main-card {
            max-width: 900px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
        }
        .table thead {
            background: #2563eb;
            color: #fff;
        }
        .table tbody tr:hover {
            background: #e6f0fa;
        }
        .btn-back {
            margin-bottom: 18px;
        }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-propietario { flex-direction: column; gap: 8px; }
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
            width: 98%; /* Más ancha que el valor por defecto */
            max-width: 420px; /* Puedes ajustar este valor según tu preferencia */
            margin-left: auto;
            margin-right: auto;
        }
        .property-card:hover {
            box-shadow: 0 8px 32px 0 rgba(139,92,246,0.18);
            transform: translateY(-6px) scale(1.025);
            border-color: #a78bfa;
        }
        .property-img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            background: #e5e7eb;
            border-radius: 18px 18px 0 0;
        }
        .property-card .card-body {
            padding: 1.5rem 1.5rem 1.2rem 1.5rem;
        }
        .property-card .card-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #6d28d9;
            margin-bottom: 0.5rem;
        }
        .property-card .card-text {
            color: #4b5563;
            margin-bottom: 1.1rem;
        }
        .property-card .mb-1 strong {
            color: #7c3aed;
        }
        @media (max-width: 900px) {
            .main-card { padding: 1rem; }
        }
        @media (max-width: 600px) {
            .property-img { height: 140px; }
            .property-card .card-body { padding: 1rem; }
        }
    </style>
</head>
<body>
    <header class="header-propietario text-center">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Bienvenido Propietario</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-propietario mb-4">
        <a href="ver_propiedades.php" class="active"><i class="bi bi-house-door"></i>Propiedades</a>
        <a href="ver_contratos.php"><i class="bi bi-file-earmark-text"></i>Contratos</a>
        <a href="ver_pagos.php"><i class="bi bi-cash-stack"></i>Pagos</a>
        <a href="reportes_propietario.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <!-- <a href="perfil_propietario.php"><i class="bi bi-person"></i>Perfil</a> -->
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card">
            <a href="../vistas/propietario.php" class="btn btn-secondary btn-back mb-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-4">Mis Propiedades</h2>
            <div class="row">
                <?php foreach ($propiedades as $propiedad): ?>
                <?php
                    $imagenes = array_filter(explode(',', $propiedad['imagen']));
                    $imgPrincipal = isset($imagenes[0]) && file_exists('../../uploads/' . $imagenes[0])
                        ? '../../uploads/' . $imagenes[0]
                        : 'https://via.placeholder.com/400x180?text=Sin+Imagen';
                    $descCorta = mb_strlen($propiedad['descripcion']) > 80
                        ? mb_substr($propiedad['descripcion'], 0, 80) . '...'
                        : $propiedad['descripcion'];
                ?>
                <div class="col-md-4">
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
                            <p class="mb-1"><strong>Tipo:</strong> <?= htmlspecialchars($propiedad['tipo_inmueble']) ?></p>
                            <p class="mb-1"><strong>Propietario:</strong> <?= htmlspecialchars($propiedad['propietario_nombre'] ?? 'No asignado') ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($propiedades)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">No tienes propiedades registradas.</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
