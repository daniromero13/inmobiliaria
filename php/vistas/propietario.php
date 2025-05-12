<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ../login.php');
    exit();
}
// Obtener el nombre completo del usuario
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Usuario';
// Puedes obtener la inicial para el avatar
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Propietario</title>
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
            /* Quitar el margen inferior para que pegue con el menú */
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
            /* Solo border-radius en la parte inferior */
            border-radius: 0 0 18px 18px;
            box-shadow: 0 2px 12px 0 rgba(60,72,88,0.10);
            /* Quitar el margen superior negativo */
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
            max-width: 480px;
            margin: 0 auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
        }
        .main-card .card-body {
            padding: 2rem 2.5rem 2rem 2.5rem;
        }
        .main-card .card-title {
            font-weight: 600;
            font-size: 1.4rem;
            color: #2d3748;
            margin-bottom: 1.2rem;
        }
        .main-card .card-text {
            color: #4a5568;
            margin-bottom: 1.5rem;
        }
        .list-group-item {
            border: none;
            border-radius: 10px;
            margin-bottom: 8px;
            font-size: 1.08rem;
            transition: background 0.18s, color 0.18s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .list-group-item i {
            font-size: 1.2rem;
            color: #4a90e2;
        }
        .list-group-item:hover, .list-group-item:focus {
            background: #e6f0fa;
            color: #2563eb;
        }
        .btn-danger {
            margin-top: 18px;
            border-radius: 8px;
        }
        @media (max-width: 600px) {
            .main-card .card-body { padding: 1rem; }
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
        <a href="../propietario/ver_propiedades.php"><i class="bi bi-house-door"></i>Propiedades</a>
        <a href="../propietario/ver_contratos.php"><i class="bi bi-file-earmark-text"></i>Contratos</a>
        <a href="../propietario/ver_pagos.php"><i class="bi bi-cash-stack"></i>Pagos</a>
        <a href="../propietario/reportes_propietario.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="card main-card shadow">
            <div class="card-body text-center">
                <div class="card-title">Panel de propietario</div>
                <div class="card-text">Accede a las opciones principales desde el menú superior.</div>
            </div>
        </div>
    </div>
</body>
</html>
