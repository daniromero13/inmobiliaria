<?php
session_start();
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    header('Location: ../login.php');
    exit();
}
$nombreCompleto = isset($_SESSION['nombre_completo']) ? trim($_SESSION['nombre_completo']) : 'Administrador';
$inicial = strtoupper(mb_substr($nombreCompleto, 0, 1, 'UTF-8'));
include '../../config/db.php';

// Usuarios por rol
$roles = [
    1 => 'Administrador',
    2 => 'Propietario',
    3 => 'Arrendatario',
    4 => 'Agente'
];
$usuarios_por_rol = [];
$res = $conn->query("SELECT rol_id, COUNT(*) as total FROM usuarios GROUP BY rol_id");
while ($row = $res->fetch_assoc()) {
    $usuarios_por_rol[$row['rol_id']] = $row['total'];
}

// Total propiedades
$res = $conn->query("SELECT COUNT(*) as total FROM propiedades");
$total_propiedades = $res->fetch_assoc()['total'] ?? 0;

// Total contratos
$res = $conn->query("SELECT COUNT(*) as total FROM contratos");
$total_contratos = $res->fetch_assoc()['total'] ?? 0;

// Total pagos
$res = $conn->query("SELECT SUM(monto) as total FROM pagos");
$total_pagos = $res->fetch_assoc()['total'] ?? 0;

// Contratos por estado
$estados_labels = [];
$estados_data = [];
$res = $conn->query("SELECT estado, COUNT(*) as total FROM contratos GROUP BY estado");
while ($row = $res->fetch_assoc()) {
    $estados_labels[] = $row['estado'] ?: 'Sin estado';
    $estados_data[] = (int)$row['total'];
}

// Propiedades por ciudad (top 5)
$ciudades_labels = [];
$ciudades_data = [];
$res = $conn->query("SELECT ciudad, COUNT(*) as total FROM propiedades GROUP BY ciudad ORDER BY total DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    $ciudades_labels[] = $row['ciudad'] ?: 'Sin ciudad';
    $ciudades_data[] = (int)$row['total'];
}

// Contratos por tipo de inmueble
$tipos_labels = [];
$tipos_data = [];
$res = $conn->query("SELECT tipo_inmueble, COUNT(*) as total FROM propiedades GROUP BY tipo_inmueble");
while ($row = $res->fetch_assoc()) {
    $tipos_labels[] = $row['tipo_inmueble'] ?: 'Sin tipo';
    $tipos_data[] = (int)$row['total'];
}

// Pagos por mes (últimos 12 meses)
$res = $conn->query(
    "SELECT DATE_FORMAT(fecha_pago, '%Y-%m') as mes, SUM(monto) as total_mes
     FROM pagos
     GROUP BY mes
     ORDER BY mes DESC
     LIMIT 12"
);
$pagos_mes = [];
while ($row = $res->fetch_assoc()) {
    $pagos_mes[] = $row;
}

// Contratos cancelados y firmados
$res = $conn->query("SELECT COUNT(*) as total FROM contratos WHERE estado = 'Cancelado'");
$contratos_cancelados = $res->fetch_assoc()['total'] ?? 0;
$res = $conn->query("SELECT COUNT(*) as total FROM contratos WHERE estado = 'Firmado'");
$contratos_firmados = $res->fetch_assoc()['total'] ?? 0;

// Propiedad con más contratos
$res = $conn->query(
    "SELECT p.titulo, COUNT(c.id) as total FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     GROUP BY p.id
     ORDER BY total DESC
     LIMIT 1"
);
$row = $res->fetch_assoc();
$prop_mas_contratos = $row['titulo'] ?? 'N/A';
$prop_mas_contratos_total = $row['total'] ?? 0;

// Ciudad con más propiedades
$res = $conn->query(
    "SELECT ciudad, COUNT(*) as total FROM propiedades
     GROUP BY ciudad
     ORDER BY total DESC
     LIMIT 1"
);
$row = $res->fetch_assoc();
$ciudad_top = $row['ciudad'] ?? 'N/A';
$ciudad_top_total = $row['total'] ?? 0;

// Contratos activos (Firmado o Vigente)
$res = $conn->query("SELECT COUNT(*) as total FROM contratos WHERE estado IN ('Firmado','Vigente')");
$contratos_activos = $res->fetch_assoc()['total'] ?? 0;

// Contratos vencidos (Cancelado o Cancelación anticipada)
$res = $conn->query("SELECT COUNT(*) as total FROM contratos WHERE estado IN ('Cancelado','Cancelación anticipada')");
$contratos_vencidos = $res->fetch_assoc()['total'] ?? 0;

// Pago promedio por contrato
$res = $conn->query("SELECT AVG(monto) as promedio FROM contratos");
$pago_promedio = $res->fetch_assoc()['promedio'] ?? 0;

// Top 3 agentes por cantidad de contratos gestionados
$top_agentes = [];
$res = $conn->query(
    "SELECT u.nombre_completo, COUNT(c.id) as total 
     FROM contratos c
     JOIN propiedades p ON c.propiedad_id = p.id
     JOIN usuarios u ON p.agente_id = u.id
     GROUP BY p.agente_id
     ORDER BY total DESC
     LIMIT 3"
);
while ($row = $res->fetch_assoc()) {
    $top_agentes[] = $row;
}

// Top 3 propietarios por cantidad de propiedades
$top_propietarios = [];
$res = $conn->query(
    "SELECT u.nombre_completo, COUNT(p.id) as total 
     FROM propiedades p
     JOIN usuarios u ON p.propietario_id = u.id
     GROUP BY p.propietario_id
     ORDER BY total DESC
     LIMIT 3"
);
while ($row = $res->fetch_assoc()) {
    $top_propietarios[] = $row;
}

// Pagos por estado de contrato
$pagos_estado_labels = [];
$pagos_estado_data = [];
$res = $conn->query(
    "SELECT c.estado, SUM(p.monto) as total 
     FROM pagos p
     JOIN contratos c ON p.contrato_id = c.id
     GROUP BY c.estado"
);
while ($row = $res->fetch_assoc()) {
    $pagos_estado_labels[] = $row['estado'] ?: 'Sin estado';
    $pagos_estado_data[] = (float)$row['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes - Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .report-card {
            border-radius: 14px;
            background: #f3f4f6;
            box-shadow: 0 2px 8px 0 rgba(60,72,88,0.08);
            padding: 1.5rem 1.2rem;
            margin-bottom: 24px;
            text-align: center;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: box-shadow 0.18s, transform 0.18s;
        }
        .report-card h4 {
            font-size: 1.15rem;
            color: #6366f1;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .report-card .report-value {
            font-size: 2.1rem;
            font-weight: 700;
            color: #2563eb;
        }
        .report-value.total-pagos {
            color: #ef4444 !important;
        }
        .row.mb-4 > .col-md-4, .row.mb-4 > .col-md-6 {
            display: flex;
        }
        .row.mb-4 > .col-md-4 > .report-card,
        .row.mb-4 > .col-md-6 > .report-card {
            width: 100%;
            min-height: 120px;
        }
        @media (max-width: 900px) {
            .report-card {
                min-height: 100px;
            }
        }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .nav-admin { flex-direction: column; gap: 8px; }
            .report-card { min-height: 90px; }
            .report-card canvas { max-width: 100%; }
        }
        .report-card canvas {
            max-width: 260px;
            max-height: 180px;
            margin: 0 auto !important;
            display: block !important;
            position: static !important;
            width: 100% !important;
            height: auto !important;
            image-rendering: auto !important;
        }
        .bg-white.rounded-4.shadow-sm.p-3.w-100 {
            min-height: unset !important;
            height: 220px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        @media (min-resolution: 2dppx), (min-device-pixel-ratio: 2) {
            .report-card canvas {
                image-rendering: -webkit-optimize-contrast !important;
                image-rendering: crisp-edges !important;
            }
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
        <a href="usuarios.php"><i class="bi bi-people"></i>Usuarios</a>
        <a href="propiedades.php"><i class="bi bi-house-door"></i>Propiedades</a>
        <a href="contratos.php"><i class="bi bi-file-earmark-text"></i>Contratos</a>
        <a href="pagos.php"><i class="bi bi-cash-stack"></i>Pagos</a>
        <a href="reportes_admin.php" class="active"><i class="bi bi-bar-chart"></i>Reportes</a>
        <a href="../php/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </nav>
    <div class="container">
        <div class="main-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../vistas/admin.php" class="btn btn-secondary btn-back">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h2 class="mb-0 text-center flex-grow-1" style="font-size:1.7rem;">Reportes Generales del Sistema</h2>
                <span style="width: 90px;"></span>
            </div>
                <!-- KPIs principales -->
            <div class="row mb-4">
                <div class="col-md-3 d-flex">
                    <div class="report-card w-100">
                        <h4>Total Usuarios</h4>
                        <div class="report-value" id="anim-usuarios"><?= array_sum($usuarios_por_rol) ?></div>
                    </div>
                </div>
                <div class="col-md-3 d-flex">
                    <div class="report-card w-100">
                        <h4>Total Propiedades</h4>
                        <div class="report-value" id="anim-propiedades"><?= $total_propiedades ?></div>
                    </div>
                </div>
                <div class="col-md-3 d-flex">
                    <div class="report-card w-100">
                        <h4>Total Contratos</h4>
                        <div class="report-value" id="anim-contratos"><?= $total_contratos ?></div>
                    </div>
                </div>
                <div class="col-md-3 d-flex">
                    <div class="report-card w-100">
                        <h4>Total Pagos</h4>
                        <div class="report-value total-pagos" id="anim-pagos">$<?= number_format($total_pagos, 2) ?></div>
                    </div>
                </div>
            </div>
            <!-- KPIs adicionales -->
            <div class="row mb-4">
                <div class="col-md-3 d-flex">
                    <div class="report-card w-100">
                        <h4>Contratos Activos</h4>
                        <div class="report-value"><?= $contratos_activos ?></div>
                    </div>
                </div>
                <div class="col-md-3 d-flex">
                    <div class="report-card w-100">
                        <h4>Contratos Vencidos</h4>
                        <div class="report-value"><?= $contratos_vencidos ?></div>
                    </div>
                </div>
                <div class="col-md-3 d-flex">
                    <div class="report-card w-100">
                        <h4>Pago Promedio Contrato</h4>
                        <div class="report-value">$<?= number_format($pago_promedio, 2) ?></div>
                    </div>
                </div>
                <div class="col-md-3 d-flex">
                    <div class="report-card w-100">
                        <h4>Contratos Firmados</h4>
                        <div class="report-value"><?= $contratos_firmados ?></div>
                    </div>
                </div>
            </div>
            <!-- Usuarios por rol -->
            <div class="row mb-4">
                <div class="col-md-12 d-flex">
                    <div class="report-card w-100">
                        <h4 class="mb-3 text-center">Usuarios por Rol</h4>
                        <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="height:220px;display:flex;align-items:center;justify-content:center;">
                            <canvas id="usuariosRolChart" style="max-width:260px;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Contratos por estado y propiedades por ciudad -->
            <div class="row mb-4">
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100">
                        <h4 class="mb-3 text-center">Contratos por Estado</h4>
                        <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="height:220px;display:flex;align-items:center;justify-content:center;">
                            <canvas id="estadosChart" style="max-width:260px;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100">
                        <h4 class="mb-3 text-center">Propiedades por Ciudad (Top 5)</h4>
                        <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="height:220px;display:flex;align-items:center;justify-content:center;">
                            <canvas id="ciudadesChart" style="max-width:260px;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Contratos por tipo de inmueble y pagos por mes -->
            <div class="row mb-4">
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100">
                        <h4 class="mb-3 text-center">Propiedades por Tipo de Inmueble</h4>
                        <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="height:220px;display:flex;align-items:center;justify-content:center;">
                            <canvas id="tiposChart" style="max-width:260px;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100">
                        <h4 class="mb-3 text-center">Pagos por Mes (últimos 12 meses)</h4>
                        <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="height:220px;display:flex;align-items:center;justify-content:center;">
                            <canvas id="pagosMesChart" style="max-width:260px;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Top agentes y propietarios -->
            <div class="row mb-4">
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100">
                        <h4>Top 3 Agentes por Contratos</h4>
                        <ul class="list-group">
                            <?php foreach ($top_agentes as $ag): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($ag['nombre_completo']) ?>
                                    <span class="badge bg-primary rounded-pill"><?= $ag['total'] ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if (empty($top_agentes)): ?>
                                <li class="list-group-item text-muted">Sin datos</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100">
                        <h4>Top 3 Propietarios por Propiedades</h4>
                        <ul class="list-group">
                            <?php foreach ($top_propietarios as $pr): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($pr['nombre_completo']) ?>
                                    <span class="badge bg-success rounded-pill"><?= $pr['total'] ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if (empty($top_propietarios)): ?>
                                <li class="list-group-item text-muted">Sin datos</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Pagos por estado de contrato -->
            <div class="row mb-4">
                <div class="col-md-12 d-flex">
                    <div class="report-card w-100">
                        <h4 class="mb-3 text-center">Pagos por Estado de Contrato</h4>
                        <div class="bg-white rounded-4 shadow-sm p-3 w-100" style="height:220px;display:flex;align-items:center;justify-content:center;">
                            <canvas id="pagosEstadoChart" style="max-width:260px;max-height:180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Otros indicadores -->
            <div class="row mb-4">
                <div class="col-md-4 d-flex">
                    <div class="report-card w-100">
                        <h4>Contratos Cancelados</h4>
                        <div class="report-value" id="anim-cancelados"><?= $contratos_cancelados ?></div>
                    </div>
                </div>
                <div class="col-md-4 d-flex">
                    <div class="report-card w-100">
                        <h4>Contratos Firmados</h4>
                        <div class="report-value" id="anim-firmados"><?= $contratos_firmados ?></div>
                    </div>
                </div>
                <div class="col-md-4 d-flex">
                    <div class="report-card w-100">
                        <h4>Propiedad con más contratos</h4>
                        <div class="report-value"><?= htmlspecialchars($prop_mas_contratos) ?> <span style="font-size:1rem;color:#6366f1;">(<?= $prop_mas_contratos_total ?>)</span></div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6 d-flex">
                    <div class="report-card w-100">
                        <h4>Ciudad con más propiedades</h4>
                        <div class="report-value"><?= htmlspecialchars($ciudad_top) ?> <span style="font-size:1rem;color:#6366f1;">(<?= $ciudad_top_total ?>)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Usuarios por rol
        new Chart(document.getElementById('usuariosRolChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_values($roles)) ?>,
                datasets: [{
                    data: [
                        <?= (int)($usuarios_por_rol[1] ?? 0) ?>,
                        <?= (int)($usuarios_por_rol[2] ?? 0) ?>,
                        <?= (int)($usuarios_por_rol[3] ?? 0) ?>,
                        <?= (int)($usuarios_por_rol[4] ?? 0) ?>
                    ],
                    backgroundColor: ['#6366f1', '#2563eb', '#fbbf24', '#10b981'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: true, position: 'bottom' }
                }
            }
        });

        // Contratos por estado
        new Chart(document.getElementById('estadosChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($estados_labels) ?>,
                datasets: [{
                    data: <?= json_encode($estados_data) ?>,
                    backgroundColor: [
                        '#10b981', '#fbbf24', '#6366f1', '#ef4444', '#2563eb'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: true, position: 'bottom' }
                }
            }
        });

        // Propiedades por ciudad
        new Chart(document.getElementById('ciudadesChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($ciudades_labels) ?>,
                datasets: [{
                    data: <?= json_encode($ciudades_data) ?>,
                    backgroundColor: [
                        '#6366f1', '#2563eb', '#fbbf24', '#10b981', '#ef4444'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: true, position: 'bottom' }
                }
            }
        });

        // Propiedades por tipo de inmueble
        new Chart(document.getElementById('tiposChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($tipos_labels) ?>,
                datasets: [{
                    data: <?= json_encode($tipos_data) ?>,
                    backgroundColor: [
                        '#fbbf24', '#10b981', '#6366f1', '#ef4444', '#2563eb'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: true, position: 'bottom' }
                }
            }
        });

        // Pagos por mes
        const pagosMes = <?= json_encode(array_reverse($pagos_mes)) ?>;
        let labelsMes = [];
        let dataPagos = [];
        pagosMes.forEach(function(row) {
            labelsMes.push(row.mes);
            let totalMes = parseFloat(row.total_mes);
            dataPagos.push(totalMes);
        });
        new Chart(document.getElementById('pagosMesChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labelsMes.map(m => {
                    let d = new Date(m + '-01');
                    return d.toLocaleString('es-ES', { month: 'long', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Pagos',
                    data: dataPagos,
                    backgroundColor: 'rgba(37,99,235,0.18)',
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 6,
                    pointHoverRadius: 7,
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                elements: {
                    line: { borderJoinStyle: 'round', borderCapStyle: 'round' }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#444',
                            font: { size: 14, family: "'Segoe UI', 'Roboto', Arial, sans-serif" }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e0e7ef', borderDash: [4, 4] },
                        ticks: {
                            color: '#444',
                            font: { size: 14, family: "'Segoe UI', 'Roboto', Arial, sans-serif" },
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Pagos por estado de contrato
        new Chart(document.getElementById('pagosEstadoChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($pagos_estado_labels) ?>,
                datasets: [{
                    data: <?= json_encode($pagos_estado_data) ?>,
                    backgroundColor: [
                        '#10b981', '#fbbf24', '#6366f1', '#ef4444', '#2563eb', '#a3e635'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: true, position: 'bottom' }
                }
            }
        });
    });
    </script>
</body>
</html>
