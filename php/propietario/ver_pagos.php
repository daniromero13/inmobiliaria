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

// Consultar pagos de contratos de propiedades de este propietario (agregando el agente)
$stmt = $conn->prepare(
    "SELECT pagos.id, pagos.contrato_id, pagos.monto, pagos.fecha_pago, pagos.created_at, 
            p.titulo AS propiedad, u.nombre_completo AS arrendatario, a.nombre_completo AS agente
     FROM pagos
     JOIN contratos c ON pagos.contrato_id = c.id
     JOIN propiedades p ON c.propiedad_id = p.id
     JOIN usuarios u ON c.arrendatario_id = u.id
     JOIN usuarios a ON p.agente_id = a.id
     WHERE p.propietario_id = ?
     ORDER BY pagos.fecha_pago DESC"
);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$result = $stmt->get_result();
$pagos = [];
while ($row = $result->fetch_assoc()) {
    $pagos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos de mis propiedades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        html, body {
            /* Elimina height/min-height/overflow que bloquea el scroll vertical */
            /* height: 100%; */
            /* min-height: 100vh; */
            /* width: 100vw; */
            /* overflow-x: unset; */
            /* overflow-y: unset; */
        }
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            /* min-height: 100vh; */
            /* width: 100vw; */
        }
        .container {
            /* min-width: unset; */
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
            max-width: 1100px;
            margin: 0 auto 40px auto;
            border-radius: 18px;
            box-shadow: 0 6px 32px 0 rgba(60,72,88,0.12);
            background: #fff;
            border: none;
            padding: 2rem 2.5rem;
            /* overflow-x: unset; */
            /* overflow-y: unset; */
        }
        .table-responsive { 
            margin-top: 2rem; 
            overflow-x: auto;
        }
        .pagos-table-custom th, .pagos-table-custom td {
            white-space: nowrap !important;
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
        .pagos-table-custom th, .pagos-table-custom td {
            padding: 12px 8px !important;
            font-size: 0.97rem;
            border: 2px solid #e0e7ef !important;
            background: #fff;
        }
        .pagos-table-custom th {
            background: #e6f0fa !important;
            color: #174ea6 !important;
            font-weight: 700;
            text-align: center;
        }
        .pagos-table-custom td {
            background: #fff !important;
            color: #222;
            font-weight: 500;
            vertical-align: middle !important;
            word-break: break-word;
            white-space: normal;
            text-align: left;
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
        <a href="ver_propiedades.php"><i class="bi bi-house-door"></i>Propiedades</a>
        <a href="ver_contratos.php"><i class="bi bi-file-earmark-text"></i>Contratos</a>
        <a href="ver_pagos.php" class="active"><i class="bi bi-cash-stack"></i>Pagos</a>
        <a href="reportes_propietario.php"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card">
            <a href="../vistas/propietario.php" class="btn btn-secondary btn-back mb-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-4">Pagos de mis propiedades</h2>
            <!-- Filtros y buscador -->
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <input type="month" id="filtroMes" class="form-control" placeholder="Filtrar por mes pagado">
                </div>
                <div class="col-md-4 mb-2">
                    <input type="text" id="buscadorPagos" class="form-control" placeholder="Buscar...">
                </div>
            </div>
            <div class="table-responsive" style="max-width:1400px; overflow-x:auto;">
                <table id="tablaPagos" class="table table-bordered align-middle mb-0 pagos-table-custom table-striped" style="max-width:1350px; margin:auto; table-layout: auto; white-space:nowrap;">
                    <thead>
                        <tr>
                            <th class="text-center align-middle" style="width:7%;">ID Pago</th>
                            <th class="text-center align-middle" style="width:10%;">Contrato</th>
                            <th class="text-center align-middle" style="width:20%;">Propiedad</th>
                            <th class="text-center align-middle" style="width:15%;">Arrendatario</th>
                            <th class="text-center align-middle" style="width:15%;">Agente</th>
                            <th class="text-center align-middle" style="width:12%;">Monto</th>
                            <th class="text-center align-middle" style="width:12%;">Dinero al Agente</th>
                            <th class="text-center align-middle" style="width:11%;">Fecha del Mes Pagado</th>
                            <th class="text-center align-middle" style="width:11%;">Fecha de Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td class="text-center align-middle"><?= $pago['id'] ?></td>
                            <td class="text-center align-middle">#<?= $pago['contrato_id'] ?></td>
                            <td class="align-middle"><?= htmlspecialchars($pago['propiedad']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($pago['arrendatario']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($pago['agente']) ?></td>
                            <td class="text-end align-middle" style="color:#2563eb; font-weight:600;">
                                $<?= number_format($pago['monto'], 2, '.', ',') ?>
                            </td>
                            <td class="text-end align-middle" style="color:#ef4444; font-weight:600;">
                                $<?= number_format($pago['monto'] * 0.10, 2, '.', ',') ?>
                            </td>
                            <td class="text-center align-middle"><?= htmlspecialchars($pago['fecha_pago']) ?></td>
                            <td class="text-center align-middle"><?= htmlspecialchars($pago['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No hay pagos registrados.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <style>
                /* Mostrar todo en una sola línea y permitir scroll horizontal */
                .table-responsive {
                    overflow-x: auto;
                }
                .pagos-table-custom th, .pagos-table-custom td {
                    white-space: nowrap !important;
                }
                .main-card {
                    max-width: 100vw !important;
                }
            </style>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtroMes = document.getElementById('filtroMes');
        const buscador = document.getElementById('buscadorPagos');
        const tabla = document.getElementById('tablaPagos').getElementsByTagName('tbody')[0];

        function filtrarTabla() {
            const mes = filtroMes.value;
            const texto = buscador.value.toLowerCase();

            for (let row of tabla.rows) {
                let mostrar = true;

                // Filtro por mes pagado (columna 5)
                if (mes && !row.cells[5].textContent.startsWith(mes)) {
                    mostrar = false;
                }
                // Buscador general
                if (texto) {
                    let rowText = row.textContent.toLowerCase();
                    if (!rowText.includes(texto)) {
                        mostrar = false;
                    }
                }
                row.style.display = mostrar ? '' : 'none';
            }
        }

        filtroMes.addEventListener('input', filtrarTabla);
        buscador.addEventListener('input', filtrarTabla);
    });
    </script>
</body>
</html>
