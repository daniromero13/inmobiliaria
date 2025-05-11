<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Usuario';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
// Simulación de propiedades (reemplaza por consulta a BD si tienes)
$propiedades = [
    [
        'direccion' => 'Cra 10 #20-30, Bogotá',
        'tipo' => 'Apartamento',
        'habitaciones' => 3,
        'banos' => 2,
        'area' => '95 m²'
    ],
    [
        'direccion' => 'Cll 45 #12-15, Medellín',
        'tipo' => 'Casa',
        'habitaciones' => 4,
        'banos' => 3,
        'area' => '120 m²'
    ]
];
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
        <a href="perfil_propietario.php"><i class="bi bi-person"></i>Perfil</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>

    </nav>
    <div class="container">
        <div class="main-card">
            <a href="../vistas/propietario.php" class="btn btn-secondary btn-back mb-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-4">Mis Propiedades</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Dirección</th>
                            <th>Tipo</th>
                            <th>Habitaciones</th>
                            <th>Baños</th>
                            <th>Área</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($propiedades as $prop): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prop['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($prop['tipo']); ?></td>
                            <td><?php echo htmlspecialchars($prop['habitaciones']); ?></td>
                            <td><?php echo htmlspecialchars($prop['banos']); ?></td>
                            <td><?php echo htmlspecialchars($prop['area']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($propiedades)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No tienes propiedades registradas.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
