<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Administrador';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrador</title>
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
            max-width: 600px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
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
        .btn-danger {
            border-radius: 8px;
        }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-admin { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>
    <header class="header-admin text-center position-relative">
        <div class="avatar"><?php echo $inicial; ?></div>
        <h1>Bienvenido Administrador</h1>
        <div class="nombre-usuario">Hola, <?php echo htmlspecialchars($nombreCompleto); ?></div>
    </header>
    <nav class="nav-admin mb-4">
        <a href="../admin/usuarios.php"><i class="bi bi-people"></i>Usuarios</a>
        <a href="../admin/propiedades.php"><i class="bi bi-house-door"></i>Propiedades</a>
        <a href="../admin/contratos.php"><i class="bi bi-file-earmark-text"></i>Contratos</a>
        <a href="../admin/pagos.php"><i class="bi bi-cash-stack"></i>Pagos</a>
        <a href="../admin/reportes_admin.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card shadow text-center">
            <div class="card-title">Panel de administrador</div>
            <div class="card-text mb-4">Accede a las opciones principales desde el menú superior.</div>
        </div>
    </div>
</body>
</html>
